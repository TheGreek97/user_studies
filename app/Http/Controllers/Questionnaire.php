<?php

namespace App\Http\Controllers;

use App\Models\AdvancedQuestionnaire;
use App\Models\SkillsQuestionnaire;
use App\Models\User;
use App\Models\UserQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Questionnaire extends Controller
{

    public function store_skills_questionnaire(Request $request)
    {
        $questionnaire = new SkillsQuestionnaire();
        $questionnaire->level_informatics = $request->level_informatics;
        $questionnaire->level_cybersecurity = $request->level_cybersecurity;
        $questionnaire->level_iot = $request->level_iot;
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
        $questionnaire->user_id = Auth::id();
        $questionnaire->save();

        $expertise = $this->computeExpertiseLevel($questionnaire->getAttributes());
        $user = Auth::user();
        $user->expertise = $expertise;
        $user->save();

        return redirect(route('questionnaire'));
    }

    public function store_questionnaire(Request $request)
    {
        $questionnaire = new UserQuestionnaire();
        $questionnaire->answer_1 = $request->question_1;
        $questionnaire->answer_1_rationale = $request->question_1_rationale;
        $questionnaire->answer_1_alt = $request->question_1_alt ?? "";

        $questionnaire->answer_2 = $request->question_2;
        $questionnaire->answer_2_rationale = $request->question_2_rationale;
        $questionnaire->answer_2_alt = $request->question_2_alt ?? "";

        $questionnaire->answer_3 = $request->question_3;
        $questionnaire->answer_3_rationale = $request->question_3_rationale;
        $questionnaire->answer_3_alt = $request->question_3_alt ?? "";

        $questionnaire->answer_4 = $request->question_4;
        $questionnaire->answer_4_rationale = $request->question_4_rationale;
        $questionnaire->answer_4_alt = $request->question_4_alt ?? "";

        $questionnaire->answer_5 = $request->question_5;
        $questionnaire->answer_5_rationale = $request->question_5_rationale;
        $questionnaire->answer_5_alt = $request->question_5_alt ?? "";

        $questionnaire->answer_6 = $request->question_6;
        $questionnaire->answer_6_rationale = $request->question_6_rationale;
        $questionnaire->answer_6_alt = $request->question_6_alt ?? "";

        $questionnaire->user_id = Auth::id();
        $questionnaire->save();

        return redirect(route('next_step'));
    }

    public function showFollowUp()
    {
        if ( Auth::user()->expertise === "expert") {
            return redirect(route('advanced'));
        } else {
            return redirect(route("post_test"));
        }
    }

    public function storeAdvanced(Request $request)
    {
        $questionnaire = new AdvancedQuestionnaire();
        $questionnaire->answer_1 = $request->question_1;
        $questionnaire->answer_1_rationale = $request->question_1_rationale;
        $questionnaire->answer_1_alt = $request->question_1_alt ?? "";

        $questionnaire->answer_2 = $request->question_2;
        $questionnaire->answer_2_rationale = $request->question_2_rationale;
        $questionnaire->answer_2_alt = $request->question_2_alt ?? "";

        $questionnaire->answer_3 = $request->question_3;
        $questionnaire->answer_3_rationale = $request->question_3_rationale;
        $questionnaire->answer_3_alt = $request->question_3_alt ?? "";

        $questionnaire->user_id = Auth::id();
        $questionnaire->save();

        return redirect(route('post_test'));
    }

    public function storeFinalComments(Request $request)
    {
        $questionnaire = Auth::user()->questionnaire;
        $questionnaire->other_events = $request->other_events ?? "";
        $questionnaire->other_actions = $request->other_actions ?? "";
        //$questionnaire->preferred_level = $request->preference;
        $questionnaire->alternatives = $request->alternatives ?? "";
        $questionnaire->save();
        return redirect(route('thankyou'));
    }

    private function computeExpertiseLevel($q){
        $score = 0;
        if ($q["cyber_1"] == "That information entered into the site is encrypted"){
            $score +=1;
        }
        if ($q["cyber_2"] == "All of the above"){
            $score +=1;
        }
        if ($q["cyber_3"] == "Botnet"){
            $score +=1;
        }
        if ($q["cyber_4"] == "0"){
            $score +=1;
        }
        if ($q["cyber_5"] == "WTh!5Z"){
            $score +=1;
        }
        if ($q["cyber_6"] == "Ransomware"){
            $score +=1;
        }
        if ($q["cyber_7"] == "Yes"){
            $score +=1;
        }
        if ($q["cyber_8"] == "False"){
            $score +=1;
        }
        if ($q["cyber_9"] == "No, it is not safe"){
            $score +=1;
        }
        if ($q["cyber_10"] == "Use of insecure Wi-Fi networks"){
            $score +=1;
        }
        if ($q["level_informatics"] > 3) {
            $score+=1;
        }
        if ($q["level_cybersecurity"] > 3) {
            $score+=1;
        }if ($q["level_iot"] > 3) {
            $score+=1;
        }
        if ($score > 7) { // [8-13]
            $level = "expert";
        } else { // [0-7]
            $level = "basic";
        }
        return $level;
    }
}
