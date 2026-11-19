<?php

namespace App\Jobs;

use App\Models\Training;
use App\Services\Llm\LlmClient;
use App\Services\Training\InteractiveBlocks;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\Materials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Generates the 5 sections of a personalized training module with 5 sequential LLM calls,
 * each one seeing the previous prompts and answers (same flow as the paper).
 *
 * Robustness:
 * - Each LLM call has its own HTTP timeout (services.llm.timeout); transient API errors are
 *   retried inside the LlmClient (SDK retries for Anthropic). If a call still fails, the job
 *   fails and the queue retries it ($tries / $backoff).
 * - $timeout covers 5 calls plus a margin. The worker must allow it
 *   (`php artisan queue:work --timeout=<same value>`) and the queue's `retry_after`
 *   (DB_QUEUE_RETRY_AFTER) must be greater, otherwise a running job is started twice.
 * - Idempotency: every attempt regenerates all 5 sections and the training is saved only
 *   at the end, so a failed attempt never leaves a half-generated module (sections depend
 *   on the previous ones, so mixing attempts would break the narrative continuity).
 * - When all attempts fail, failed() marks the training as failed; the waiting page shows
 *   the error and lets the participant re-dispatch the job.
 *
 * Thesis condition "interactive" (prototipo/materiali/generation-prompts-diff.md): same flow with
 * modified prompts. The user's name never reaches the provider ({{first_name}} placeholder, replaced
 * at render time); the model returns structured data as JSON blocks that are validated, stripped from
 * the HTML and saved in trainings.interactive_data (an invalid block fails the attempt, retryable);
 * Module 4 holds only the generated introduction, its emails come from the bank emails-m4.json.
 */
class ProcessTraining implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const SECTIONS = ["introduction", "scenario", "defense_strategies", "exercises", "conclusions"];

    /** Attempts before failed() is called. */
    public $tries = 3;

    /** Seconds to wait before attempt 2 and 3. */
    public $backoff = [30, 120];

    /** Seconds before the worker kills an attempt; set in the constructor from the per-call timeout. */
    public $timeout;

    protected $trainingId, $training_length, $training_personalization, $user_name,
        $personalization_prompt, $priming_prompt_scenario;

    public function __construct($trainingId, $user)
    {
        /*
        Condition names used in the paper:
        - Baseline (training_personalization == "no")
        - Direct Profile (training_personalization == "yes")
        - Few-Shot (training_personalization == "few_shot")
        - Table-Based (training_personalization == "table")
        - Guideline-Based (training_personalization == "primed")
        Thesis condition:
        - Interactive (training_personalization == "interactive"): Direct Profile + structured data
        */
        $this->trainingId = $trainingId;
        $this->training_length = $user->training_length;
        $this->training_personalization = $user->training_personalization;
        $this->user_name = $user->name;
        $this->personalization_prompt = $user->getUserProfilePrompt();
        $this->priming_prompt_scenario = $user->getPrimingPromptScenario();  // used exclusively for the "primed" cond.

        $this->timeout = count(self::SECTIONS) * (int) config('services.llm.timeout', 180) + 60;
    }

    /**
     * @throws \App\Services\Llm\LlmException when a section cannot be generated (the queue retries the job)
     */
    public function handle(LlmClient $llm)
    {
        $training = Training::find($this->trainingId);
        if (!$training) {
            return;
        }

        // Provider and model come from config (TRAINING_LLM_PROVIDER). The default "openai" provider uses o3-mini:
        // IF CHANGING THE MODEL, THE NON-CUSTOMIZED TRAINING SHOULD BE UPDATED (setNonCustomizedVersion@app/Models/Training.php)
        $timeout = (int) config('services.llm.timeout', 180);

        // Get user conditions
        $developer_prompt = $this->getDeveloperPrompt();
        $section_prompts = $this->getSectionPrompts();

        // Context: the developer prompt is the system prompt; previous prompts and answers are the conversation.
        $context = [];
        $sections = [];
        $interactive_data = [];
        $usage = ['input_tokens' => 0, 'output_tokens' => 0, 'calls' => 0];
        $models = [];

        foreach (self::SECTIONS as $section) {
            $prompt = $section_prompts[$section];

            $response = $llm->complete(
                $developer_prompt,
                array_merge($context, [['role' => 'user', 'content' => $prompt]]),
                ['timeout' => $timeout]
            );

            $sections[$section] = $response->text;
            if ($this->isInteractive()) {
                // Checked right after each call so an invalid block does not pay for the remaining ones.
                // The context keeps the raw answer: the next sections refer to the data blocks.
                $parsed = InteractiveBlocks::extract($response->text, InteractiveBlocks::REQUIRED_BY_SECTION[$section] ?? []);
                $sections[$section] = $parsed['html'];
                foreach ($parsed['blocks'] as $role => $data) {
                    $interactive_data[str_replace('-', '_', $role)] = $data;
                }
            }
            $usage['input_tokens'] += (int) ($response->usage['input_tokens'] ?? 0);
            $usage['output_tokens'] += (int) ($response->usage['output_tokens'] ?? 0);
            $usage['calls']++;
            $models[$response->model] = true;

            // Maintain context
            $context[] = ['role' => 'user', 'content' => $prompt];
            $context[] = ['role' => 'assistant', 'content' => $response->text];
        }

        // Saved only once all sections exist (see class comment on idempotency).
        foreach ($sections as $section => $text) {
            $training->$section = $text;  // add the section text to the training model
        }
        // Several models only if a server-side fallback answered some of the calls.
        if ($this->isInteractive()) {
            $draw = self::moduleFourSequence();
            $interactive_data['m4'] = ['sequence' => $draw['sequence'], 'seed' => $draw['seed']];
            $training->interactive_data = $interactive_data;
        }
        $training->llm_model = implode(',', array_keys($models));
        $training->llm_usage = $usage;
        $training->generation_failed_at = null;
        $training->generation_error = null;
        $training->generated = true;
        $training->save();
    }

    /**
     * Called by the queue after the last failed attempt: marks the training as failed so the
     * waiting page can show it. Only the exception message is stored (no headers, no keys).
     */
    public function failed(Throwable $e): void
    {
        Log::error('Training generation failed', ['training_id' => $this->trainingId, 'error' => $e->getMessage()]);

        $training = Training::find($this->trainingId);
        if (!$training || $training->generated) {
            return;
        }
        $training->generation_failed_at = now();
        $training->generation_error = Str::limit(class_basename($e) . ': ' . $e->getMessage(), 495);
        $training->save();
    }

    private function isInteractive(): bool
    {
        return $this->training_personalization === "interactive";
    }

    /**
     * Order of the Module 4 emails, fixed per training: the bank's default sequence with the
     * legitimate email inserted at a random position between 2 and 4 (emails-m4.json, _meta).
     * Single implementation of the rule: DialogSession::drawSequence (seeded, reproducible).
     *
     * @return array{seed: int, sequence: list<string>, legitimate_position: int}
     */
    public static function moduleFourSequence(?Materials $materials = null): array
    {
        $meta = ($materials ?? new Materials())->loadEmailBank()['_meta'];
        return DialogSession::drawSequence($meta);
    }

    private function getDeveloperPrompt()
    {
        if ($this->isInteractive()) {
            // The name must not reach the provider (cap4 4.2.4): the model writes a placeholder.
            $name_requirement = '- The content should be clear, engaging, and educational, and the user must be addressed by their first name. Do not invent a name: write the placeholder {{first_name}} exactly as shown wherever the name is needed; the platform will replace it.';
            $output_format_extra = "\nSome submodules must also return machine-readable data. When a prompt asks for it, put the data inside the same <div> as <script type=\"application/json\" data-role=\"...\"> blocks, with exactly the fields requested. These blocks are never shown to the user and must contain valid JSON.";
        } else {
            $name_requirement = "- The content should be clear, engaging, and educational, and the user must be addressed by their first name, which is $this->user_name.";
            $output_format_extra = "";
        }
        $personalized_string = $this->training_personalization == "no" ? "" : "personalized";
        $minutes = $this->training_length == "short" ? "9" : "18";
        error_log("length condition: $this->training_length - $minutes minutes, personalization: $this->training_personalization");
        $prompt = "
CONTEXT
You are asked to generate $personalized_string educational material for an anti‑phishing training module. This training module will last about $minutes minutes and will be split into 5 submodules. The training module will have the following structure:
- Introduction to the Phishing Problem
- Phishing Scenario
- Defense Strategies
- Interactive Exercises
- Conclusions


OUTPUT FORMAT
Each submodule will be embedded in a webpage, so it must be valid HTML and wrapped in a <div> tag. Do NOT include a heading of the section (such as <h2>Introduction</h2>).
Any <a> tag that you generate in the scenario and in the exercises must have both a valid href attribute, but must prevent the default action of redirecting the user to another website, as the user must never leave the current webpage.$output_format_extra


CONTENT AND STYLE REQUIREMENTS
- The language must be accessible and simple to make the concepts understandable by users with no expertise in cybersecurity.
$name_requirement
- The content should provide the hard facts and clear guidance expected from an “expert” while also incorporating relatable narratives and examples that create a more personal connection.
- The content should flow logically, ensuring a smooth and engaging user experience.
- The submodules are shown by the user in the same session, one after the other. Therefore, generate each subsequent submodule as the continuation of the previous one(s).
- Do not greet the user at the start of every submodule, but just in the introduction.
";
        if ($this->training_personalization === "yes" || $this->isInteractive()) {
            $prompt .= "

TRAIT DESCRIPTIONS
The definitions below establish a common framework for interpreting user profiles along three domains: the Big Five personality factors (OCEAN), emotional intelligence factors, and a set of Persuasion susceptibility factors (linked to scam compliance).

### Big Five (OCEAN) Personality Traits

- Open-Mindedness:
  - High scorers: Curious, imaginative, creative, and receptive to new ideas, experiences, and unconventional viewpoints; they tend to be less judgmental.
  - Low scorers: More conventional and practical, showing resistance to change and a preference for familiar, traditional approaches.

- Conscientiousness:
  - High scorers: Highly self-disciplined, organized, responsible, and detail-oriented, with a strong commitment to goal-directed behavior.
  - Low scorers: Often impulsive, disorganized, and spontaneous, tending to prioritize immediate desires over long-term objectives.

- Extraversion:
  - High scorers: Outgoing, energetic, and assertive individuals who thrive on social stimulation and active engagement with others.
  - Low scorers: More reserved, quiet, and introspective, preferring solitary activities or smaller, more intimate social interactions.

- Agreeableness:
  - High scorers: Compassionate, cooperative, and empathetic, inclined to trust others and prioritize social harmony.
  - Low scorers: More competitive, skeptical, or even antagonistic; they may prioritize their own goals over group consensus.

- Negative Emotionality:
  - High scorers: Tend to experience frequent or intense negative emotions (e.g., anxiety, anger, or sadness) and are more reactive to stress, with pronounced mood fluctuations.
  - Low scorers: Generally emotionally stable, calm, and resilient, maintaining equilibrium even in challenging situations.

### Persuasion-Related Traits

- Positive Attitudes Towards Advertising:
  - High scorers: Possess a strong predisposition to respond favorably to advertising messages and are more likely to be influenced by marketing offers.
  - Low scorers: Typically indifferent or even resistant to advertising, showing less inclination to be swayed by promotional content.

- Social Influence:
  - High scorers: Exhibit a pronounced desire for social inclusion and peer approval, making them more responsive to messaging that emphasizes group belonging and social validation.
  - Low scorers: More independent in their decision-making and less driven by the need to conform to social expectations, showing reduced sensitivity to group influence.

- Need for Uniqueness and Avoidance of Similarity:
  - High scorers: Value distinctiveness and are particularly attracted to offers presented as unique or scarce, as these reinforce their self-perception of individuality.
  - Low scorers: Generally less concerned with standing out, tending to feel comfortable with conventional choices and less swayed by uniqueness appeals.

- Sensation Seeking:
  - High scorers: Actively seek novel, intense experiences and are willing to take risks in pursuit of such stimulation, which can increase their responsiveness to arousing offers.
  - Low scorers: Prefer predictable, familiar experiences and tend to be more risk-averse, making them less likely to be drawn to high-stimulation offers.

- Risk Preferences:
  - High scorers: Exhibit a lower aversion to risk and are more open to engaging in activities with uncertain outcomes if potential rewards seem attractive.
  - Low scorers: Tend to be cautious, preferring safe and predictable choices over opportunities that involve significant risk.

- Lack of Premeditation:
  - High scorers: Often make decisions quickly without carefully considering the consequences, leading to more impulsive actions.
  - Low scorers: Deliberate carefully on potential outcomes before acting, showing greater forethought and caution.

- Lack of Self-Control:
  - High scorers: Are less able to regulate their impulses and emotional responses, which may lead to rapid, unfiltered decision-making.
  - Low scorers: Demonstrate strong self-control and the ability to delay gratification, contributing to more measured and thoughtful responses.

- Need for Cognition:
  - High scorers: Enjoy and engage readily in cognitive processing and complex thinking, often finding satisfaction in analyzing information deeply.
  - Low scorers: Are less driven by a need for cognitive engagement, preferring simpler, more intuitive approaches to processing information.

- Need for Consistency:
  - High scorers: Place a high value on maintaining behavioral and perceptual consistency, making them more likely to comply with initial small commitments and follow through with larger actions in line with that pattern.
  - Low scorers: Are more flexible and less concerned with aligning actions with past behavior, allowing for greater variation in decisions.

### Emotional Intelligence Factors

- Well-Being:
  - High scorers: Possess strong self-esteem, maintain high levels of life satisfaction, and exhibit a hopeful, optimistic outlook.
  - Low scorers: Often struggle with self-confidence, may report lower satisfaction with life, and exhibit a more pessimistic or subdued outlook.

- Sociability:
  - High scorers: Are adept at social interactions, possessing strong social awareness and effective skills in managing others’ emotions; they are typically assertive in social contexts.
  - Low scorers: May find social interactions challenging, often displaying lower social awareness, less effective emotion management, and a more passive communication style.

- Emotionality:
  - High scorers: Excel in perceiving, expressing, and managing emotions (both their own and those of others); they are capable of maintaining healthy interpersonal relationships with considerable empathy.
  - Low scorers: Often have difficulty recognizing or conveying emotions, potentially experiencing challenges in interpersonal communication and relationship management.

- Self-Control:
  - High scorers: Are proficient at regulating their emotions, managing stress effectively, and controlling impulses, contributing to balanced decision-making.
  - Low scorers: May struggle with emotional regulation, experience higher stress levels, and exhibit more impulsive behavior.

PERSONALIZATION REQUIREMENTS
The training material must be tailored to the user’s profile, which is defined by the following characteristics:
$this->personalization_prompt
";
        }
        elseif ($this->training_personalization === "few_shot") {
            $prompt .="

PERSONALIZATION REQUIREMENTS
The training content must be personalized using the psychological profile of the digital twin, which includes:

- **Big Five Personality Traits** (BFI-2): Openness, Conscientiousness, Extraversion, Agreeableness, Negative Emotionality
- **Susceptibility to Persuasion** (STP-II): Authority, Liking, Scarcity, Social Influence, Reciprocity
- **Emotional Intelligence** (TEIQue-SF): Emotionality, Self-control, Sociability, Well-being

$this->personalization_prompt

Use these traits to guide how content is framed, the tone and examples used, and the emphasis placed on emotional or rational appeals. For instance:
- For users high in Agreeableness or Emotionality, include emotionally resonant stories or social impact.
- For users high in Openness, offer slightly more reflective or conceptually interesting content.
- For users more susceptible to social proof or authority, highlight how attackers might exploit those principles.
- Adapt feedback in exercises to resonate with emotional triggers or confidence levels.

Do not explicitly mention the psychological traits in the output; instead, implicitly adapt the message to suit such a profile.
";
        } elseif ($this->training_personalization === "table") {
            $prompt .="

PERSONALIZATION REQUIREMENTS
The training content must be personalized using the psychological profile of the digital twin, which includes:

- **Big Five Personality Traits** (BFI-2): Openness, Conscientiousness, Extraversion, Agreeableness, Negative Emotionality
- **Susceptibility to Persuasion** (STP-II): Authority, Liking, Scarcity, Social Influence, Reciprocity
- **Emotional Intelligence** (TEIQue-SF): Emotionality, Self-control, Sociability, Well-being

$this->personalization_prompt

Use these traits to guide how content is framed, the tone and examples used, and the emphasis placed on emotional or rational appeals. The goal is to improve user engagement and learning effectiveness by aligning the training with their psychological predispositions.

Use the table below for guidance:

| Trait / Factor                | High Value – Content Strategy                                          | Low Value – Content Strategy                                           |
|-------------------------------|------------------------------------------------------------------------|------------------------------------------------------------------------|
| **Openness**                  | Use metaphors, scenarios involving novelty, abstract reasoning         | Use concrete facts, step-by-step logic, avoid abstraction              |
| **Conscientiousness**         | Emphasize planning, orderliness, and consequences of negligence        | Keep content short and informal; highlight practical shortcuts         |
| **Extraversion**              | Use socially framed examples, dialogues, and group implications        | Use solitary scenarios and personal reflection                         |
| **Agreeableness**             | Highlight cooperation, trust, and social harm caused by phishing       | Focus on self-protection, autonomy, and individual consequences        |
| **Negative Emotionality**     | Use reassuring tone, clear structure, reduce anxiety-inducing wording  | Use straightforward, direct style without over-explaining              |
| **Authority Susceptibility**  | Emphasize how attackers mimic authority figures (e.g., CEO fraud)      | Stress verification over blind trust in authority                      |
| **Liking Susceptibility**     | Show how friendly language and familiar logos can deceive              | Highlight how even likable elements can be forged                      |
| **Scarcity Susceptibility**   | Warn about fake urgency or expiring offers                             | Less emphasis on urgency, more on reasoning                            |
| **Social Influence Susceptibility** | Warn about messages claiming \"everyone is doing it\"            | Focus on technical cues, not social conformity                         |
| **Reciprocity Susceptibility**| Highlight fake \"gifts\" or helpful gestures from attackers            | Emphasize critical thinking and skepticism                             |
| **Emotionality (EI)**         | Use empathy, emotional narratives, and moral appeals                   | Prefer logical reasoning and evidence-based arguments                  |
| **Self-control (EI)**         | Emphasize impulsive risks and how to pause before acting               | Focus on planning and internal motivation                              |
| **Well-being (EI)**           | Frame phishing as a threat to personal safety and digital well-being   | Frame phishing as a cognitive and technical problem                    |
| **Sociability (EI)**          | Emphasize interactions with others and public risks (e.g., group leaks)| Emphasize individual risks and responsibility                          |

If a trait is not provided or unclear, default to a neutral, educational tone. When multiple traits are combined, resolve possible tensions by prioritizing **clarity**, **usability**, and **trust-building**, and avoid manipulative framings at all times.
";
        } elseif ($this->training_personalization === "primed") {
            $prompt .="

PERSONALIZATION REQUIREMENTS
$this->personalization_prompt
            ";
        }
        // error_log($prompt);
        return $prompt;
    }

    private function getSectionPrompts(): array
    {
        if ($this->isInteractive()) {
            return $this->getInteractiveSectionPrompts();
        }
        if ($this->training_length == "short") {
            $sections_times = ["introduction" => 1, "scenario" => 2, "defense_strategies" => 3, "exercises" => 2, "conclusions" => 1];
            $n_exercises = 2;
        } else {  // long
            $sections_times = ["introduction" => 2, "scenario" => 5, "defense_strategies" => 6, "exercises" => 3, "conclusions" => 2];
            $n_exercises = 3;
        }
        $sections_words = array_map(function ($x) {return $x * 150;}, $sections_times);

        $prompts = [];
        foreach (["introduction", "scenario", "defense_strategies", "exercises", "conclusions"] as $section) {
            $s_time = $sections_times[$section];
            $s_words = $sections_words[$section];
            switch ($section) {
                case "introduction":
                     $prompt = "GOAL
Generate the Introduction sub-module, which must be structured as follows.
Introduction to the Phishing Problem ($s_time min, approx. $s_words words):";
                    if ($this->training_personalization == "no"){
                       $prompt .= "
- Briefly introduce the user to the training course.
- Explain what the problem of phishing is and why it’s dangerous.
- Include a statement that presents some psychological vulnerabilities exploited by attackers.
- Give an overview of what the whole training module will cover.";
                    } else {
                        $prompt .= "
- Briefly introduce the user to the training course.
- Explain what the problem of phishing is and why it’s dangerous.
- Include a statement that presents some possible user vulnerabilities to phishing techniques based on the user’s psychological profile.
- Give an overview of what the whole training module will cover.";
                    }
                    break;
                case "scenario":
                    $prompt = "GOAL
Generate the Phishing Scenario sub-module, which must be structured as follows.
Phishing Scenario ($s_time minutes, approx. $s_words words):";
                    if ($this->training_personalization == "no"){
                       $prompt .= "
- Scenario Introduction: Briefly introduce a scenario with a realistic narrative that users might relate to, in which an email is suddenly received.
- Interactive Phishing Email: Create a realistic, simulated phishing email in HTML containing common phishing techniques (e.g., deceptive URL, spoofed sender details, etc.).
The email parts that contain a phishing technique must be reactive on mouse click showing a description of the technique (see next bullet point). Be sure that the link(s) in the email is an <a> tag, as the user should be able to hover over it and preview the URL as usual. Clicking on any link must not ever make the user exit the current webpage.
- Description of Techniques: the mouse click on a suspicious element of the email triggers the appearance of a detailed description of the phishing technique used in that part of the email.
- Interactive decision point: Insert an interactive prompt that asks the user what they would do in that situation, with a close-ended question (using HTML form elements like radio buttons).
- Reflection & Learning: Provide immediate feedback based on the decision point, explaining why certain choices may lead to a compromise and reinforcing learning outcomes.";
                    } elseif ($this->training_personalization == "primed"){
                        $prompt .= "
- Scenario Introduction: Briefly introduce a scenario with a realistic narrative that users might relate to, in which an email is suddenly received.
- Interactive Phishing Email: Create a realistic, simulated phishing email in HTML containing common phishing techniques (e.g., deceptive URL, spoofed sender details, etc.).
In the phishing scenario, you must:
    $this->priming_prompt_scenario
The email parts that contain a phishing technique must be reactive on mouse click showing a description of the technique (see next bullet point). Be sure that the link(s) in the email is an <a> tag, as the user should be able to hover over it and preview the URL as usual. Clicking on any link must not ever make the user exit the current webpage.
- Description of Techniques: the mouse click on a suspicious element of the email triggers the appearance of a detailed description of the phishing technique used in that part of the email.
- Interactive decision point: Insert an interactive prompt that asks the user what they would do in that situation, with a close-ended question (using HTML form elements like radio buttons).
- Reflection & Learning: Provide immediate feedback based on the decision point, explaining why certain choices may lead to a compromise and reinforcing learning outcomes.";
                    } else {
                        $prompt .= "
- Scenario Introduction: Briefly introduce a scenario with a realistic narrative that users might relate to, in which an email is suddenly received.
- Interactive Phishing Email: Create a realistic, simulated phishing email in HTML containing common phishing techniques (e.g., deceptive URL, spoofed sender details, etc.), making sure to include phishing techniques the user is most susceptible to, according to the PERSONALIZATION REQUIREMENTS.
The email parts that contain a phishing technique must be reactive on mouse click showing a description of the technique (see next bullet point). Be sure that the link(s) in the email is an <a> tag, as the user should be able to hover over it and preview the URL as usual. Clicking on any link must not ever make the user exit the current webpage.
- Description of Techniques: the mouse click on a suspicious element of the email triggers the appearance of a detailed description of the phishing technique used in that part of the email.
- Interactive decision point: Insert an interactive prompt that asks the user what they would do in that situation, with a close-ended question (using HTML form elements like radio buttons).
- Reflection & Learning: Provide immediate feedback based on the decision point, explaining why certain choices may lead to a compromise and reinforcing learning outcomes.";
                    }
                    break;
                case "defense_strategies":
                    $prompt = "GOAL
Generate the Defense Strategies sub-module, which must be structured as follows.
Defense Strategies ($s_time minutes, approx. $s_words words):
                        ";
                    if ($this->training_personalization == "no"){
                       $prompt .= "
- Present defense strategies against common phishing techniques (e.g., “Double-check the sender’s domain carefully”, “Check the actual URL by hovering on a link”, etc.).
- Give clear actionable items that participants can easily follow to protect themselves.
- This list of items must include directions on how to recognize a genuine email (e.g., by checking the domain).";
                    } else {
                        $prompt .= "
- Present tailored defense strategies against common phishing techniques (e.g., “Double-check the sender’s domain carefully”, “Check the actual URL by hovering on a link”, etc.) covering the ones the user is most susceptible to, given their profile (e.g., “Since you often click links quickly, double-check the sender’s email and URL before clicking”, etc.).
- Give clear actionable items that participants can easily follow to protect themselves.
- This list of items must include directions on how to recognize a genuine email (e.g., by checking the domain).";
                    }
                    break;
                case "exercises":
                    $prompt = "GOAL
Generate the Interactive Exercises sub-module, which must be structured as follows.
Interactive Exercises ($s_time minutes):
                        ";
                    $prompt .= $this->getEmailExerciseSubPrompt($n_exercises);
                    break;
                case "conclusions":
                    $prompt = "GOAL
Generate the Conclusions sub-module, which must be structured as follows.
Conclusions ($s_time minutes, approx. $s_words words):
- Provide a quick recap of the importance of phishing awareness.
- Provide a quick recap of the practical actions that the user can do to defend themselves. Be sure to cover only those already explained in the previous sub-modules.
- Thank the user for attending the training and conclude the session.";
            break;
            }
            $prompts[$section] = $prompt;  // save the prompt
        }
        return $prompts;
    }


    /**
     * Section prompts of the "interactive" condition: the Direct Profile prompts with the REPLACE/APPEND
     * changes of prototipo/materiali/generation-prompts-diff.md (sections 1-5). Introduction, exercises and
     * conclusions have fixed lengths; scenario and defense strategies keep the length of the condition.
     */
    private function getInteractiveSectionPrompts(): array
    {
        $long = $this->training_length != "short";
        $scenario_time = $long ? 5 : 2;
        $defense_time = $long ? 6 : 3;
        $scenario_words = $scenario_time * 150;
        $defense_words = $defense_time * 150;

        $introduction = <<<'PROMPT'
GOAL
Generate the Introduction sub-module, which must be structured as follows.

Opening task (data only):
- First, decide the phishing email that you will develop in full in the Phishing Scenario sub-module.
- Return only its header and context as a JSON block with data-role="scenario-header" and these fields:
  "scenario_context": one or two sentences that set the scene in which the email arrives (who the reader is, what they are doing). Do not use the user's name or the {{first_name}} placeholder here.
  "sender_name", "sender_address", "subject": as they will appear in the email.
  "first_line": the first sentence of the email body.
- The platform shows this header to the user, followed by the question "What would make you suspicious about this message?". Do not write the question, do not comment on the header and do not reveal which of its elements are suspicious, anywhere in this sub-module.

Introduction to the Phishing Problem (1 min, approx. 100 words):
- Briefly introduce the user to the training course.
- Explain what the problem of phishing is and why it's dangerous.
- Include a statement that presents some possible user vulnerabilities to phishing techniques based on the user's psychological profile.
- Give an overview of what the whole training module will cover.
PROMPT;

        $scenario = "GOAL
Generate the Phishing Scenario sub-module, which must be structured as follows.
Phishing Scenario ($scenario_time minutes, approx. $scenario_words words):
- Scenario Introduction: Briefly introduce a scenario with a realistic narrative that users might relate to, in which an email is suddenly received.
" . <<<'PROMPT'
- Interactive Phishing Email: develop in full the email whose header you wrote in the Introduction sub-module. Keep sender name, sender address, subject and first line identical. The email must contain common phishing techniques (e.g., deceptive URL, spoofed sender details, etc.), making sure to include phishing techniques the user is most susceptible to, according to the PERSONALIZATION REQUIREMENTS.
  Return the email as a JSON block with data-role="scenario-email" (not as visible HTML), with these fields:
  "sender_name", "sender_address", "reply_to" (or null), "recipient_name" (use {{first_name}}), "subject", "date" (ISO 8601),
  "body_html": the body only, simple HTML without images, styles from external files or scripts; every link is an <a> tag with data-link-id and a real-looking href (never "#"),
  "links": [{"id", "text", "href_real"}],
  "attachments": [{"filename"}] (or []),
  "clues": one entry per suspicious element, each with
     "id" ("c1", "c2", ...),
     "nist_category": one of "Error", "Technical indicator", "Visual presentation indicator", "Language and content", "Common tactic",
     "target": {"field": one of "sender_name", "sender_address", "reply_to", "subject", "body", "link", "attachment", "text": the exact text of that field or an exact substring of the visible body text, "link_id" for links},
     "label": max 6 words,
     "explanation": 1-2 sentences describing the technique, written for a non-expert (this replaces the description shown on click),
     "order": position in this fixed sequence: first the sender, then the subject and the tone, then the link, finally the request for data.
- The platform renders the email, highlights one suspicious element at a time in the given order and shows its explanation when the user opens it. Do not write HTML for the email itself and do not write click handlers.
- Interactive decision point: inside a <div data-role="decision-point">, insert an interactive prompt that asks the user what they would do in that situation, with a close-ended question (using HTML form elements like radio buttons). The platform shows it only after the user has opened all the suspicious elements.
- Reflection & Learning: Provide immediate feedback based on the decision point, explaining why certain choices may lead to a compromise and reinforcing learning outcomes.
PROMPT;

        $defense_strategies = "GOAL
Generate the Defense Strategies sub-module, which must be structured as follows.
Defense Strategies ($defense_time minutes, approx. $defense_words words):
- Present tailored defense strategies against common phishing techniques (e.g., “Double-check the sender’s domain carefully”, “Check the actual URL by hovering on a link”, etc.) covering the ones the user is most susceptible to, given their profile (e.g., “Since you often click links quickly, double-check the sender’s email and URL before clicking”, etc.).
- Give clear actionable items that participants can easily follow to protect themselves.
- This list of items must include directions on how to recognize a genuine email (e.g., by checking the domain).
" . <<<'PROMPT'
- Also return the actionable items as a JSON block with data-role="defense-guidelines": an array of 4 to 6 objects, each with
  "id" ("g1", "g2", ...),
  "text": the item exactly as the user should remember it, max 20 words, imperative form,
  "email_part": the part of the Phishing Scenario email it refers to, chosen from this fixed list: "sender", "subject", "tone", "link", "attachment", "request", "greeting", "signature", "none".
  At least one item must explain how to recognize a genuine email.
- The platform renders these items as a diagram of the Phishing Scenario email, with each item connected to its part. Do not repeat the items as a list in the text: the text explains why the strategies work and how to apply them (approx. 350 words).
PROMPT;

        $exercises = <<<'PROMPT'
GOAL
Generate the Interactive Exercises sub-module, which must be structured as follows.
Interactive Exercises introduction (approx. 70 words):
- Introduce the exercises that follow. The platform will show some emails, one at a time; do not write any email, form, button or feedback.
- Explain the task: for each email the user chooses "Phishing" or "Legitimate" and writes, in their own words, why.
- Explain that a tutor will then ask a few questions about their reasoning instead of giving the answer, that the correct answer is shown at the end of each conversation, and that the tutor's help becomes lighter from one email to the next.
- Do not say how many emails there are, nor how many are phishing or legitimate.
- Remind the user not to write personal data in their answers.
PROMPT;

        $conclusions = <<<'PROMPT'
GOAL
Generate the Conclusions sub-module, which must be structured as follows.
Conclusions (approx. 100 words):
- Provide a quick recap of the importance of phishing awareness.
- Provide a quick recap of the practical actions that the user can do to defend themselves. Be sure to cover only those already explained in the previous sub-modules. Do not phrase them as personal rules in the form "When ..., I ...": after this recap the user will write their own rules.
- Separately, inside a <div data-role="thanks">, thank the user for attending the training and conclude the session. The platform shows this part only at the very end of the module.
PROMPT;

        return compact("introduction", "scenario", "defense_strategies", "exercises", "conclusions");
    }

    private function getEmailExerciseSubPrompt($n_exercises){
        $services = ["Google", "Microsoft", "PayPal", "Revolut", "Netflix", "Apple", "Amazon", "Booking.com", "AirBnB"];
        $prompt = "
- Provide $n_exercises simulated phishing emails in HTML format. The simulated phishing emails should include realistic elements such as sender information, subject lines, and email content.
  The emails must implement a technique for which the user has been trained in the previous sub-modules, to put their new knowledge at test. The phishing emails must include a simulated phishing URL:
  -- One of the emails must mimic an email coming from a work colleague from a real company.";
        if ($n_exercises == 2){
            // Generate one more exercise
            $mimicked_service = $services[array_rand($services, 1)];
            $prompt .= "
    -- The other email must mimic a real organization/service, including mimicking its real domains, namely $mimicked_service.
- Also provide a genuine email that resembles one from $mimicked_service, also including a genuine URL.";
        } else if ($n_exercises > 2){
            // Generate two more exercises
            $mimicked_services = array_intersect_key($services, array_flip(array_rand($services, 2)));  // take two random elements from $services
            $prompt .= "
    -- The other emails must mimic a real organization/service, including mimicking its real domains, namely ". implode(separator: ' and ', array:$mimicked_services).".
- Also provide a genuine email that resembles one from ". array_key_first($mimicked_services) .", also including a genuine URL.";
        }
        $prompt .= "
- Include an interactive classification task where the user must decide if an email is “Phishing” or “Legitimate” using HTML form controls.
Give immediate feedback for each exercise, explaining which cues indicated whether the email was a phishing attempt or not.
Be sure that any form submission is prevented to avoid refreshing the webpage.
- Be sure that all the links in the emails must be <a> tags with the href attribute containing the phishing/genuine URL (i.e., no \"#\"), as the user should be able to hover over each link and preview the URL as usual. However, prevent any redirect by clicking on the links in the emails.";
        return $prompt;
    }

}
