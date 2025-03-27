<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEmailQuestionnaire;
use Illuminate\Support\Facades\Log;


class QuestionnairesController extends Controller
{
    public function expelUser()
    {
        session(['expelled' => true]);
        return view('expelled');
    }

    public function showQuestionnaire($step)
    {

        if (session()->has('expelled')) {
            return redirect(route('expelUser'));
        }

        $questionnaires = [
            0 => 'questionnaires.demographicQuestionnaire',
            1 => 'questionnaires.bfi2xs',
            2 => 'questionnaires.stp-ii-b',
            3 => 'questionnaires.tei-que-sf',
            4 => 'questionnaires.training_reaction_questionnaire'
        ];

        for ($i = 0; $i <= 3; $i++) {
            if (!session()->has("questionnaire_{$i}done")) {
                if ($i != $step) {
                    return redirect(route('questionnaire', ['step' => $i]));
                }
                return view($questionnaires[$i]);
            }
        }

        if (!session()->has('pre_phase_done') || !session()->has('post_phase_done') ) {
            return redirect(route('show', ['folder' => 'inbox']));
        }

        if (!session()->has("questionnaire_4done")) {
            if ($step != 4) {
                return redirect(route('questionnaire', ['step' => 4]));
            }
            return view($questionnaires[4]);
        } else {
            return redirect(route('save-final-data'));
        }

    }

    public function saveEmailClassification(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'emailId'    => 'required|integer|exists:emails,id',
                'confidence' => 'required|integer|min:1|max:10',
                'phishing'   => 'required|in:yes,no',
                'time_spent' => 'required|numeric|min:1'
            ]);

            // Check if the email has already been saved for the current user
            $existingEntry = UserEmailQuestionnaire::where('email_id', $validatedData['emailId'])
                ->where('user_id', Auth::id())
                ->first();

            if ($existingEntry) {
                // Optionally, add a flash message or similar to notify the user
                return redirect(route('show', ['folder' => 'inbox']));
            }

            $phase = session('pre_phase_done') ? 'post' : 'pre';

            $dataToInsert = [
                'email_id'   => $validatedData['emailId'],
                'confidence' => $validatedData['confidence'],
                'phishing'   => $validatedData['phishing'] === 'yes' ? 1 : 0,
                'response_time_seconds' => min(999.99, $validatedData['time_spent']),
                'user_id'    => Auth::id(),
                'title_email' => 'null',
                'phase'      => $phase,
            ];

            UserEmailQuestionnaire::create($dataToInsert);

            return redirect(route('show', ['folder' => 'inbox']));
        } catch (\Exception $e) {
            // Catch any exceptions and return a JSON response with error details
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function saveDemographicsData(Request $request)
    {
        // Demographic questionnaire
        $user = Auth::user();
        $user->name = $request->first_name;
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $user->save();
        session(['questionnaire_0done' => true]);
        return redirect(route('questionnaire', ['step' => 1]));
    }

    public function saveFinalData(Request $request)
    {
        $user = Auth::user();
        if (! $user->study_completed) {
            $user->study_completed = Carbon::now();
            $user->save();
        }
        return redirect(route('thank_you'));
    }

}
