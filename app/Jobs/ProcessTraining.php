<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Training;

class ProcessTraining implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trainingId;

    public function __construct($trainingId)
    {
        $this->trainingId = $trainingId;
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
        $personalization_condition = $training->user->training_personalization;
        $length_condition = $training->user->length_condition;
        $user_name = $training->user->name;
        $developer_prompt = $this->getDeveloperPrompt($personalization_condition, $length_condition, $user_name);
        $section_prompts = $this->getSectionPrompts($personalization_condition, $length_condition);

        $context = [['role' => 'developer', 'content' => $developer_prompt]];
        //error_log($apiKey);
        foreach (["introduction", "scenario", "defense_strategies", "exercises", "conclusions"]  as $section) {
            $prompt = $section_prompts[$section];

           $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
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

            /* DEBUG values:
            sleep(2);
            $training->$section = $section;*/
        }

        $training->completed = true;
        $training->save();
    }

    private function getDeveloperPrompt($personalization_condition, $length_condition, $user_name)
    {
        $personalized_string = $personalization_condition == "no" ? "" : "personalized";
        $minutes = $length_condition == "short" ? "9" : "18";

        $prompt = "
CONTEXT
You are asked to generate $personalized_string educational material for an anti‑phishing training module. This training module will last about $minutes minutes and will be split into 5 submodules. The training module will have the following structure:
- Introduction to the Phishing Problem
- Realistic Phishing Scenario Presentation
- Defense Strategies
- Interactive Exercises
- Conclusions


OUTPUT FORMAT
Each submodule will be embedded in a webpage, so it must be valid HTML and wrapped in a <div> tag.


CONTENT AND STYLE REQUIREMENTS
- The language must be accessible and simple to make the concepts understandable by users with no expertise in cybersecurity.
- The content should be clear, engaging, and educational, and the user must be addressed by their first name, which is $user_name.
- The content should provide the hard facts and clear guidance expected from an “expert” while also incorporating relatable narratives and examples that create a more personal connection.
- The content should flow logically, ensuring a smooth and engaging user experience.
";
        if ($personalization_condition !== "no"){
            $personalization_prompt = Auth::user()->getUserProfilePrompt();
            $prompt .= "
            PERSONALIZATION REQUIREMENTS
            $personalization_prompt
            ";
            /*if ($personalization_condition == "primed"){
                $main_traits = $user->getUserMainTraits();
                $priming_guidelines = $this->getPersonalizationGuidelines($main_traits);
                $prompt .= "\n\nPERSONALIZATION REQUIREMENTS\n". $priming_guidelines;
            } else if ($personalization == "yes"){*/
        }
        return $prompt;
    }

    private function getSectionPrompts($personalization, $length)
    {
        if ($length == "short") {
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
                    if ($personalization == "no"){
                       $prompt .= "
- Explain what the problem of phishing is and why it’s dangerous.
- Include a statement that presents some psychological vulnerabilities exploited by attackers.
- Give an overview of what the whole training module will cover.";
                    } else {
                        $prompt .= "
- Explain what the problem of phishing is and why it’s dangerous.
- Include a statement that presents some possible user vulnerabilities to phishing techniques based on the user’s psychological profile.
- Give an overview of what the whole training module will cover.";
                    }
                    break;
                case "scenario":
                    $prompt = "GOAL
                        Generate the Phishing Scenario sub-module, which must be structured as follows.
                        Realistic Phishing Scenario Presentation ($s_time minutes, approx. $s_words words):";
                    if ($personalization == "no"){
                       $prompt .= "
- Scenario Introduction: Briefly introduce a scenario with a realistic narrative that users might relate to, in which an email is suddenly received.
- Interactive Phishing Email: Create a realistic, simulated phishing email in HTML containing common phishing techniques (e.g., deceptive URL, spoofed sender details, etc.). The email parts that contain a phishing technique must be reactive on mouse click showing a description of the technique (see next bullet point).
- Description of Techniques: the mouse click on a suspicious element of the email triggers the appearance of a detailed description of the phishing technique used in that part of the email.
- Interactive decision point: Insert an interactive prompt that asks the user what they would do in that situation, with a close-ended question (using HTML form elements like radio buttons).
- Reflection & Learning: Provide immediate feedback based on the decision point, explaining why certain choices may lead to a compromise and reinforcing learning outcomes.";
                    } else {
                        $prompt .= "
- Explain what the problem of phishing is and why it’s dangerous.
- Include a statement that presents some possible user vulnerabilities to phishing techniques based on the user’s psychological profile.
- Give an overview of what the whole training module will cover.";
                    }
                    break;
                case "defense_strategies":
                    $prompt = "GOAL
Generate the Defense Strategies sub-module, which must be structured as follows.
Defense Strategies ($s_time minutes, approx. $s_words words):
                        ";
                    if ($personalization == "no"){
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
            // TODO: priming condition
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
Be sure that any form submission is prevented to avoid refreshing the webpage.";
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
