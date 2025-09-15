<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionnairesController extends Controller
{
    public function showQuestionnaire($step)
    {
        $questionnaires = [
            0 => 'questionnaires.demographicQuestionnaire',
            1 => 'questionnaires.phishing-assessment',
            /* 1 => 'questionnaires.bfi2xs',
            2 => 'questionnaires.stp-ii-b',
            3 => 'questionnaires.tei-que-sf',*/
            4 => 'questionnaires.training_reaction_questionnaire'
        ];

        if ($step == 0) {
            return view($questionnaires[$step], [
                // 'recaptcha_sitekey' => env('NOCAPTCHA_SITEKEY')
            ]);
        }

        return view($questionnaires[$step]);
    }

    public function saveDemographicsData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'g-recaptcha-response' => 'required|captcha',
            'first_name' => 'between:2,50|required|string',
            'age' => 'between:18,100|required|integer',
            'gender' => 'required',
            'role' => 'required',
            'sector' => 'required',
            'experience_level' => 'required',
            'num_hours_day_internet' => 'between:0,24|required|integer',
            'prolific_id' => 'nullable|size:24'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        // escape simple HTML chars
        $user->name = str_replace(["&","<",">","\"","'"], ["&amp;","&lt;","&gt;","&quot;","&#39;"], (string)$request->first_name);
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $user->role = $request->role;
        $user->sector = $request->sector;
        $user->experience_level = $request->experience_level;
        $user->demographics_completed = now();
        if ($request->prolific_id) {
            $user->prolific_id = $request->prolific_id;
        }
        $user->save();


        return redirect(route('questionnaire', ['step' => 1]));
    }
}
