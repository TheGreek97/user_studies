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
            1 => 'questionnaires.bfi2xs',
            2 => 'questionnaires.stp-ii-b',
            3 => 'questionnaires.tei-que-sf',
            4 => 'questionnaires.training_reaction_questionnaire', 
            5 => 'questionnaires.demographicQuestionnaire', 
        ];

        for ($i = 1; $i <= 3; $i++) {
            if (!session()->has("questionnaire_{$i}done")) {
                if ($i != $step) {
                    return redirect(route('questionnaire', ['step' => $i]));
                }
                return view($questionnaires[$i]);
            }
        }

        if (!session()->has('pre_phase_done') or !session()->has('post_phase_done') ) {
            return redirect(route('show', ['folder' => 'inbox']));
        }

        if (!session()->has("questionnaire_4done")) {
            if ($step != 4) {
                return redirect(route('questionnaire', ['step' => 4]));
            }
            return view($questionnaires[4]);
        }

        if (!session()->has("questionnaire_5done")) {
            if ($step != 5) {
                return redirect(route('questionnaire', ['step' => 5]));
            }
            return view($questionnaires[5]);
        } else {
            return redirect(route('thank_you'));
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

    public function saveFinalData(Request $request)
    {
        // Demographic questionnaire
        $user = Auth::user();
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->num_hours_day_internet = $request->num_hours_day_internet;
        $answers = [];
        for ($i=1; $i<=10; $i++){
            $question = "cyber_".$i;
            $answers[$question] = $request->$question;
        }
        $user->expertise_score = null;
        $user->study_completed = Carbon::now();
        $user->save();
        session(['questionnaire_5done' => true]);
        return redirect(route('thank_you'));
        
    }


}