<?php

namespace App\Http\Controllers;

use App\Models\FollowUpQuestionnaire;
use App\Models\User;
use App\Models\UserEmailQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Questionnaire extends Controller
{
    public function storeEmailQuestionnaire($id, Request $request)
    {
        $questionnaire = new UserEmailQuestionnaire();
        $questionnaire->title_email = $request->title_email;
        $questionnaire->how_many_hyperlinks = $request->how_many_hyperlinks;
        $questionnaire->sender_email = $request->sender_email;
        $questionnaire->email_id = $id;
        $questionnaire->user_id = Auth::id();
        $questionnaire->save();
        return redirect(route('show'));
    }

    public function storeFollowUp(Request $request)
    {
        $questionnaire = new FollowUpQuestionnaire();
        $questionnaire->parts_email_suspicious = $request->parts_email_suspicious;
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
        $questionnaire->nasa_frustration_level = $request->nasa_frustration_level;
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
        $user = Auth::user();
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $user->save();
        return redirect(route('thankyou'));
    }

    public function showFollowUp()
    {
        if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thankyou'));
        } elseif (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) < 10) {
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
        }
    }
}
