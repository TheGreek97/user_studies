<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

use App\Models\Training;

class ProcessTraining implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trainingId, $training_length, $training_personalization, $user_name,
        $personalization_prompt, $priming_prompt_scenario;

    public function __construct($trainingId, $user)
    {
        $this->trainingId = $trainingId;
        $this->training_length = $user->training_length;
        $this->training_personalization = $user->training_personalization;
        $this->user_name = $user->name;
        $this->personalization_prompt = $user->getUserProfilePrompt();
        $this->priming_prompt_scenario = $user->getPrimingPromptScenario();
    }

    /**
     * @throws RequestException
     */
    public function handle()
    {
        $training = Training::find($this->trainingId);
        if (!$training) {
            return;
        }

        // OpenAI API Config
        $apiKey = env('OPENAI_API_KEY');
        $model = 'o3-mini';
        $reasoning_effort = 'medium';
        $temperature = 0.00001;

        // Get user conditions
        $developer_prompt = $this->getDeveloperPrompt();
        $section_prompts = $this->getSectionPrompts();

        $context = [['role' => 'developer', 'content' => $developer_prompt]];
        //error_log($apiKey);
        foreach (["introduction", "scenario", "defense_strategies", "exercises", "conclusions"]  as $section) {
            $prompt = $section_prompts[$section];

            // 2 min timeout
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->timeout(600)->retry(3,200000)->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'reasoning_effort' => $reasoning_effort,
                'messages' => array_merge($context, [['role' => 'user', 'content' => $prompt]]),
                //'temperature' => $temperature,
            ]);
            if ($response->successful()) {
                $generatedText = $response['choices'][0]['message']['content'] ?? '<p>Failed to generate content.</p>';
                $training->$section = $generatedText; // add the section text to the training model
                // Maintain context
                $context[] = ['role' => 'user', 'content' => $prompt];
            } else {
               $generatedText = $response->body(); //'<p>Failed to generate content.</p>';
               $response->throw();
            }
            $context[] = ['role' => 'assistant', 'content' => $generatedText];
        }

        $training->generated = true;
        $training->save();
    }

    private function getDeveloperPrompt()
    {
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
Any <a> tag that you generate in the scenario and in the exercises must have both a valid href attribute, but must prevent the default action of redirecting the user to another website, as the user must never leave the current webpage.


CONTENT AND STYLE REQUIREMENTS
- The language must be accessible and simple to make the concepts understandable by users with no expertise in cybersecurity.
- The content should be clear, engaging, and educational, and the user must be addressed by their first name, which is $this->user_name.
- The content should provide the hard facts and clear guidance expected from an “expert” while also incorporating relatable narratives and examples that create a more personal connection.
- The content should flow logically, ensuring a smooth and engaging user experience.
- The submodules are shown by the user in the same session, one after the other. Therefore, generate each subsequent submodule as the continuation of the previous one(s).
- Do not greet the user at the start of every submodule, but just in the introduction.
";
        if ($this->training_personalization === "yes") {
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
";
            $prompt .= "

PERSONALIZATION REQUIREMENTS
$this->personalization_prompt
";
            /*if ($personalization_condition == "primed"){
                $main_traits = $user->getUserMainTraits();
                $priming_guidelines = $this->getPersonalizationGuidelines($main_traits);
                $prompt .= "\n\nPERSONALIZATION REQUIREMENTS\n". $priming_guidelines;
            } else if ($personalization == "yes"){*/
        }
        elseif ($this->training_personalization === "yes_no_trait_desc") {
            $prompt .="

PERSONALIZATION REQUIREMENTS
$this->personalization_prompt

Use these traits to guide how content is framed, the tone and examples used, and the emphasis placed on emotional or rational appeals. For instance:
- For users high in Agreeableness or Emotionality, include emotionally resonant stories or social impact.
- For users high in Openness, offer slightly more reflective or conceptually interesting content.
- For users more susceptible to social proof or authority, highlight how attackers might exploit those principles.
- Adapt feedback in exercises to resonate with emotional triggers or confidence levels.

Do not explicitly mention the psychological traits in the output; instead, implicitly adapt the message to suit such a profile.
";
        }
        return $prompt;
    }

    private function getSectionPrompts(): array
    {
        if ($this->training_length == "short") {
            $sections_times = ["introduction" => 1, "scenario" => 2, "defense_strategies" => 3, "exercises" => 2, "conclusions" => 1];
            $n_exercises = 2;
        } else {
            $sections_times = ["introduction" => 2, "scenario" =>5, "defense_strategies" => 6, "exercises" => 3, "conclusions" => 2];
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


    private function getPersonalizationGuidelines($main_personality_traits, $max_guidelines = 3){
        $guidelines = config('guidelines'); // Include the guidelines array
        $counter = 1;
        $prompt= "";
        // remove traits with no guidelines
        unset($main_personality_traits['Total Trait Emotional Intelligence']);
        unset($main_personality_traits['Lack of self-control']);
        foreach ($main_personality_traits as $trait) {
            if ($counter > $max_guidelines)
                break;
            // Take the trait name and polarity

            // Construct the prompt fragment with the Language, Training Content, and Scenario
            $counter++;
        }
        return $prompt;
    }
}
