<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;


class QuestionnairesController extends Controller
{


    public function showQuestionnaire($step)
    {
        $questionnaires = [
            0 => 'questionnaires.demographicQuestionnaire',
            1 => 'questionnaires.bfi2xs',
            2 => 'questionnaires.stp-ii-b',
            3 => 'questionnaires.tei-que-sf',
            4 => 'questionnaires.training_reaction_questionnaire'
        ];

        return view($questionnaires[$step]);
    }

    public function saveDemographicsData(Request $request)
    {
        // Save demographic questionnaire
        $user = Auth::user();
        $user->name = str_replace(  // escape HTML characters in user input to avoid injections
            ["&", "<", ">", '"', "'"],
            ["&amp;", "&lt;", "&gt;", "&quot;", "&#39;"],
            (string) $request->first_name
        );
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $user->demographics_completed = now();
        if ($request->prolific_id) {
            $user->prolific_id = $request->prolific_id;
        }
        $user->save();
        return redirect(route('questionnaire', ['step' => 1]));  // show BFI questionnaire after demographics
    }

}
