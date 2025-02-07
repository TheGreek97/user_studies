<?php

namespace App\Http\Controllers;

use App\Models\FollowUpQuestionnaire;
use App\Models\User;
use App\Models\UserEmailQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Questionnaire extends Controller
{
    public function storeEmailQuestionnaire($id, Request $request)
    {
        $questionnaire = new UserEmailQuestionnaire();
        $questionnaire->title_email = "null";//$request->title_email;
        $questionnaire->how_many_hyperlinks = $request->how_many_hyperlinks;
        $questionnaire->sender_email = $request->sender_email;
        $questionnaire->email_id = $id;
        $questionnaire->user_id = Auth::id();
        $questionnaire->save();
        return redirect(route('show'));
    }

    public function showFollowUp()
    {
        $user = Auth::user();
        $warning_image = $this->getWarningToShow($user);
        return view('followupquestionnaire')
            ->with("user_ignored_warning", Auth::user()->ignored_warning)
            ->with("url_image_warning", $warning_image);
        if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thank_you'));
        } elseif (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) < MailController::MAILS_NUMBER) {
            return redirect(route('show', ['folder' => 'inbox']));
        } else {
            return view('followupquestionnaire')
                ->with("user_ignored_warning", Auth::user()->warning_ignored)
                ->with("url_image_warning", $warning_image);
        }
    }

    public function storeFollowUp(Request $request)
    {
        $questionnaire = new FollowUpQuestionnaire();
        // Warning questionnaire
        $questionnaire->read_warning = $request->read_warning;
        $questionnaire->reaction = $request->reaction;
        $questionnaire->understood_warning = $request->understood_warning;
        $questionnaire->familiar_warning = $request->familiar_warning;
        $questionnaire->interested_warning = $request->interested_warning;
        $questionnaire->confusing_words = $request->confusing_words;
        $questionnaire->felt_risk = $request->felt_risk;
        $questionnaire->actions_warning = $request->actions_warning;
        $questionnaire->meaning_warning = $request->meaning_warning;
        $questionnaire->understood_warning_reverse = $request->not_understood_warning;  // attention check
        $questionnaire->trust_warning = $request->trust_warning;
        if ($request->warning_ignored_motivation) {
            $questionnaire->warning_ignored_motivation = $request->warning_ignored_motivation;
        }
        $questionnaire->first_word = $request->first_word;  // attention check

        // NASA-TLX questionnaire
        $questionnaire->nasa_mental_demand = $request->nasa_mental_demand;
        $questionnaire->nasa_physical_demand = $request->nasa_physical_demand;
        $questionnaire->nasa_temporal_demand = $request->nasa_temporal_demand;
        $questionnaire->nasa_performance = $request->nasa_performance;
        $questionnaire->nasa_effort = $request->nasa_effort;
        $questionnaire->nasa_frustration_level = $request->nasa_frustration_level;
        $questionnaire->nasa_mental_demand_reverse = $request->nasa_mental_demand_reverse;  // attention check (reverse-coding question)

        // Need for Cognition questionnaire
        $questionnaire->n4c_1 = $request->n4c_1;
        $questionnaire->n4c_2 = $request->n4c_2;
        $questionnaire->n4c_3 = $request->n4c_3;
        $questionnaire->n4c_4 = $request->n4c_4;
        $questionnaire->n4c_5 = $request->n4c_5;
        $questionnaire->n4c_6 = $request->n4c_6;
        $questionnaire->n4c_attention = $request->n4c_attention;

        // Cyber-security knowledge
        $questionnaire->cyber_1 = $request->cyber_1;
        $questionnaire->cyber_2 = $request->cyber_2;
        $questionnaire->cyber_3 = $request->cyber_3;
        $questionnaire->cyber_4 = $request->cyber_4;
        $questionnaire->cyber_5 = $request->cyber_5;
        $questionnaire->cyber_6 = $request->cyber_6;
        $questionnaire->cyber_7 = $request->cyber_7;
        $questionnaire->cyber_8 = $request->cyber_8;
        $questionnaire->cyber_9 = $request->cyber_9;
        $questionnaire->cyber_10 = $request->cyber_10;
        $questionnaire->cyber_control = $request->cyber_11 == 1;   // attention check
        $questionnaire->user_id = Auth::id();
        $questionnaire->save();

        // Demographic questionnaire
        $user = Auth::user();
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $user->prolific_id = $request->prolific_id;
        $answers = [];
        for ($i=1; $i<=10; $i++){
            $question = "cyber_".$i;
            $answers[$question] = $request->$question;
        }
        $user->expertise_score = $this->computeExpertiseScore($answers);
        $user->study_completed = Carbon::now();
        $user->save();
        return redirect(route('thank_you'));
    }

    private function computeExpertiseScore($q){
        $score = 0;
        if ($q["cyber_1"] == 1){
            $score +=1;
        }
        if ($q["cyber_2"] == 4){
            $score +=1;
        }
        if ($q["cyber_3"] == 1){
            $score +=1;
        }
        if ($q["cyber_4"] == 0){
            $score +=1;
        }
        if ($q["cyber_5"] == 2){
            $score +=1;
        }
        if ($q["cyber_6"] == 2){
            $score +=1;
        }
        if ($q["cyber_7"] == 0){
            $score +=1;
        }
        if ($q["cyber_8"] == 1){
            $score +=1;
        }
        if ($q["cyber_9"] == 1){
            $score +=1;
        }
        if ($q["cyber_10"] == 1){
            $score +=1;
        }
        return $score;
    }

    private function getWarningToShow ($user) : string {
        // Based on what the user saw during the experiment, show a different image
        if ($user->show_explanation) {
            // if the exp condition involved showing a warning
            // $user->warning_shown contains a decimal representing a binary value 000:
            // 0: no warning was shown, 1: instagram warning only was shown, 2: amazon warning only was shown
            // 3: instagram and amazon warnings only were shown (1+2),
            // 4: the facebook (false positive) warning only was shown,
            // 5: the facebook and the instagram warnings only were shown (4+1),
            // 6: the facebook and the amazon warnings only were shown (4+2)
            // 7: all the 3 warnings were shown (1+2+4)

            $warning_name = match ($user->shown_warning) {
                1, 5 => "ip_addr",
                2, 6 => "tld_misp",
                0, 3, 4, 7 => (rand(0, 1) === 0) ? "ip_addr" : "tld_misp" // take one at random between the two true positive warnings
            };
        }
        else {
            $warning_name = "no_exp";
            if ($user->warning_type == "tooltip") {
                $warning_name = $warning_name . "_" . match ($user->shown_warning) {
                        0, 7 => (rand(0, 2) === 0) ? "ig" : ((rand(0, 1) === 0) ? "amazon" : "fb"),
                        1 => "ig",
                        2 => "amazon",
                        3 => (rand(0, 1) === 0) ? "ig" : "amazon", // random between ig and amazon
                        4 => "fb",
                        5 => (rand(0, 1) === 0) ? "ig" : "fb", // random between ig and facebook
                        6 => (rand(0, 1) === 0) ? "fb" : "amazon", // random between facebook and amazon
                    };
            }
        }
        $llm = $user->llm;
        return asset("/assets/img/warnings/$llm/".$user->explanation_type."_$warning_name.png");
    }
}
