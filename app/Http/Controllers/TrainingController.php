<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TrainingController extends Controller
{
    public function showTraining()
    {
        if (!session()->has('pre_phase_done') || session()->has('training_done')) {
            return redirect()->route('show', ['folder' => 'inbox']);
        }

        return $this->generateTraining();
    }


    private function generateTraining()
    {
        // OpenAI API Key
        $apiKey = env('OPENAI_API_KEY');
        $model = 'o3-mini';
        $maxTokens = 500;  //TODO tweak

        // Get prompts based on the user's conditions
        $personalization_condition = Auth::user()->training_personalization;
        $length_condition = Auth::user()->length_condition;
        $prompts = $this->getSectionPrompts($personalization_condition, $length_condition);

        $context = []; // To store message history
        $generatedSections = [];

        // Sequential API calls
        foreach ($prompts as $prompt) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type'  => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => array_merge($context, [['role' => 'user', 'content' => $prompt]]),
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            $generatedText = $response->json()['choices'][0]['message']['content'] ?? '<p>Failed to generate content.</p>';

            // Append generated content to the list
            $generatedSections[] = $generatedText;

            // Maintain context by adding assistant's response
            $context[] = ['role' => 'user', 'content' => $prompt];
            $context[] = ['role' => 'assistant', 'content' => $generatedText];
        }

        // Combine all sections into a single HTML string
        $finalHtml = implode("\n", $generatedSections);

        // Pass to the view
        return view('training', ['generatedHtml' => $finalHtml]);
    }


    private function getSectionPrompts($personalization, $length)
    {
        $user = Auth::user();
        if ($length == "short") {
            $sections_times = ["intro" => 1, "scenario" => 2, "defense_strats" => 3, "exercises" => 2, "conclusions" => 1];
            $n_exercises = 2;
        } else {
            $sections_times = ["intro" => 2, "scenario" =>5, "defense_strats" => 6, "exercises" => 3, "conclusions" => 2];
            $n_exercises = 3;
        }
        $sections_words = array_map(function ($x) {return $x * 150;}, $sections_times);

        $prompts = [];
        foreach (["intro", "scenario", "defense_strats", "exercises", "conclusions"] as $section) {
            $s_time = $sections_times[$section];
            $s_words = $sections_words[$section];
            switch ($section) {
                case "intro":
                     $prompt = "GOAL
                        Generate the Introduction sub-module, which must be structured as follows.
                        Introduction to the Phishing Problem ($s_time min, approx. $s_words words):";
                    if ($personalization == "no"){
                       $prompt .= "
                        - Explain what the problem of phishing is and why it’s dangerous.
                        - Include a statement that presents some psychological vulnerabilities exploited by attackers.
                        - Give an overview of what the whole training module will cover. ";
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
                case "defense_strats":
                    $prompt = "GOAL
                        Generate the Defense Strategies sub-module, which must be structured as follows.
                        Defense Strategies ($s_time minutes, approx. $s_words words):
                        ";
                    if ($personalization == "no"){
                       $prompt .= "- Present defense strategies against common phishing techniques (e.g., “Double-check the sender’s domain carefully”, “Check the actual URL by hovering on a link”, etc.).
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
                        - Thank the user for attending the training and conclude the session. ";
                break;
            }
            if ($personalization == "primed"){
                $main_traits = $user->getUserMainTraits();
                $priming_guidelines = $this->getPersonalizationGuidelines($main_traits);
                $prompt .= "\n\nPERSONALIZATION REQUIREMENTS\n". $priming_guidelines;
            } else if ($personalization == "yes"){
                $personalization_prompt = $user->getUserProfilePrompt();
                $prompt .= "\n\nPERSONALIZATION REQUIREMENTS\n". $personalization_prompt;
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
            $mimicked_service = $services[array_rand($services, 1)[0]];
            $prompt .= "
                -- The other email must mimic a real organization/service, including mimicking its real domains, namely $mimicked_service.
            - Also provide a genuine email that resembles one from $mimicked_service, also including a genuine URL. The genuine email must appear in random order among the phishing ones.";
        } else if ($n_exercises > 2){
            // Generate two more exercises
            $mimicked_services = array_intersect_key($services, array_flip(array_rand($services, 2)));  // take two random elements from $services
            $prompt .= "
                -- The other emails must mimic a real organization/service, including mimicking its real domains, namely ". implode(separator: ' and ', array:$mimicked_services).".
            - Also provide a genuine email that resembles one from ". $mimicked_services[0] .", also including a genuine URL. The genuine email must appear in random order among the phishing ones.";
        }
        $prompt .= "
        - Include an interactive classification task where the user must decide if an email is “Phishing” or “Legitimate” using HTML form controls. Give immediate feedback for each exercise, explaining which cues indicated whether the email was a phishing attempt or not.";
        return $prompt;
    }


    private function getPersonalizationGuidelines($main_personality_traits, $max_guidelines = 3){
        $guidelines = config('guidelines'); // Include the guidelines array
        $counter = 1;
        // remove traits with no guidelines
        unset($main_personality_traits['Total Trait Emotional Intelligence']);
        unset($main_personality_traits['Lack of self-control']);
        foreach ($main_personality_traits as $trait) {
            if ($counter > $max_guidelines)
                break;
            dd($trait);
            // Take the trait name and polarity

            // Construct the prompt fragment with the Language, Training Content, and Scenario
            $counter++;
        }
    }
}
