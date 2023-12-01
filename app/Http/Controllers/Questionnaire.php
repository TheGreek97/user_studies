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

        return view('followupquestionnaire')->with("user_ignored_warning", Auth::user()->warning_ignored);
        if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thankyou'));
        } elseif (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) < MailController::MAILS_NUMBER) {
            return redirect(route('show', ['folder' => 'inbox']));
        } else {
            return view('followupquestionnaire')->with("user_ignored_warning", Auth::user()->warning_ignored);
        }
    }

    public function storeFollowUp(Request $request)
    {
        $questionnaire = new FollowUpQuestionnaire();
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

        $questionnaire->nasa_mental_demand = $request->nasa_mental_demand;
        $questionnaire->nasa_physical_demand = $request->nasa_physical_demand;
        $questionnaire->nasa_temporal_demand = $request->nasa_temporal_demand;
        $questionnaire->nasa_performance = $request->nasa_performance;
        $questionnaire->nasa_effort = $request->nasa_effort;
        $questionnaire->nasa_frustration_level = $request->nasa_frustration_level;
        $questionnaire->nasa_mental_demand_reverse = $request->nasa_mental_demand_reverse;  // attention check (reverse-coding question)


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
        return redirect(route('thankyou'));
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
}
