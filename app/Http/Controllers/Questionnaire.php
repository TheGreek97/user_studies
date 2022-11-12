<?php

namespace App\Http\Controllers;

use App\Models\FollowUpQuestionnaire;
use App\Models\User;
use App\Models\UserQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Questionnaire extends Controller
{
    public function showQuestionnaire($id=0) {
        $id = (int) $id;
        return match ($id) {
            0 => view('questionnaire_basic'),
            1 => view('questionnaire_medium'),
            2 => view('questionnaire_expert'),
            default => view('questionnaire_basic')
        };
    }

    public function levelQuestionnaire($id, Request $request)
    {
        $questionnaire = new UserQuestionnaire();
        #TODO riempi campi
        $questionnaire->user_id = Auth::id();
        $questionnaire->save();
        return redirect(route('show'));
    }

    public function showFollowUp()
    {
        return view('skillsquestionnaire');
        /*
        if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thankyou'));
        } elseif (count(DB::table('userquestionnaire')->where('user_id', Auth::id())->get()) < 10) {
            return redirect(route('show', ['folder' => 'inbox']));
        } else {
            switch (Auth::user()->warning_type) {
                case 'popup_email':
                    $vendor_1 = "Bartolini";
                    $vendor_2 = "Amazon";
                    break;

                case 'popup_link':
                    $vendor_1 = "Poste Italiane";
                    $vendor_2 = "TikTok";
                    break;

                case 'tooltip':
                    $vendor_1 = "Trenitalia";
                    $vendor_2 = "Facebook";
                    break;

                case 'browser_native':
                    $vendor_1 = "Netflix";
                    $vendor_2 = "Apple";
                    break;

                default:
                    $vendor_1 = "";
                    $vendor_2 = "";
            }
            return view('followupquestionnaire', ['vendor_1' => $vendor_1, 'vendor_2' => $vendor_2]);
        }*/
    }

    public function storeFollowUp(Request $request)
    {
        $questionnaire = new FollowUpQuestionnaire();
        /*$questionnaire->parts_email_suspicious = $request->parts_email_suspicious;
        $questionnaire->know_email_1 = $request->know_email_1;
        $questionnaire->know_email_2 = $request->know_email_2;
        $questionnaire->explanation_feedback = $request->explanation_feedback;
        $questionnaire->have_read_warning = $request->have_read_warning == "1";
        $questionnaire->how_warning_useful_identifying_link = $request->how_warning_useful_identifying_link;
        $questionnaire->how_annoying_warning_was = $request->how_annoying_warning_was;
        $questionnaire->how_warning_perception_link = $request->how_warning_perception_link;
        $questionnaire->how_evident_was_warning = $request->how_evident_was_warning;
        $questionnaire->nasa_mental_demand = $request->nasa_mental_demand;
        $questionnaire->nasa_physical_demand = $request->nasa_physical_demand;
        $questionnaire->nasa_temporal_demand = $request->nasa_temporal_demand;
        $questionnaire->nasa_performance = $request->nasa_performance;
        $questionnaire->nasa_effort = $request->nasa_effort;
        $questionnaire->nasa_frustration_level = $request->nasa_frustration_level;*/
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
        return match ($expertise) {
            'basic' => redirect(route('next_step', ['id' => 0])),
            'medium' => redirect(route('next_step', ['id' => 1])),
            'expert' => redirect(route('next_step', ['id' => 2])),
            default => redirect(route('next_step', ['id' => 0])),
        };
        /*
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        */
#       return redirect(route('thankyou'));
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
        if ($score > 9) { // [10-13]
            $level = "expert";
        } else if ($score > 4) { // [5-9]
            $level = "medium";
        } else { // [0-4]
            $level = "basic";
        }
        return $level;
    }
}
