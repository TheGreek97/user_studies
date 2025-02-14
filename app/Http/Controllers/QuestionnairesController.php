<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEmailQuestionnaire;
use Illuminate\Support\Facades\Log;


class QuestionnairesController extends Controller
{
    /**
     * Display a listing of the questionnaires.
     */
    public function index()
    {
        return view('questionnaires_screen');
    }

    public function showQuestionnaire1()
    {
        session(['questionnaire1_view' => true]);
        return view('questionnaires.bfi2xs'); 
    }

    public function showQuestionnaire2()
    {
        session(['questionnaire2_view' => true]);
        return view('questionnaires.stp-ii-b'); 
    }

    public function showQuestionnaire3()
    {
        session(['questionnaire3_view' => true]);
        return view('questionnaires.tei-que-sf'); 
    }

    public function showQuestionnaire4()
    {
        session(['questionnaire3_view' => true]);
        return view('questionnaires.training_reaction_questionnaire');
    }

    public function finalData()
    {
        session(['questionnaire_4done' => true]);
        return view('questionnaires.demographicQuestionnaire');
    }

    public function saveEmailClassification(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'emailId'    => 'required|integer|exists:emails,id', 
                'confidence' => 'required|integer|min:1|max:10', 
                'phishing'   => 'required|in:yes,no', 
            ]);

            $dataToInsert = [
                'email_id'   => $validatedData['emailId'],
                'confidence' => $validatedData['confidence'],
                'phishing'   => $validatedData['phishing'] === 'yes' ? 1 : 0,
                'user_id'    => Auth::id(),
                'title_email' => 'null' 
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
        $user->prolific_id = $request->prolific_id;
        $answers = [];
        for ($i=1; $i<=10; $i++){
            $question = "cyber_".$i;
            $answers[$question] = $request->$question;
        }
        $user->expertise_score = null;
        $user->study_completed = Carbon::now();
        $user->save();
        session(['questionnaire_5done' => true]);
        return redirect(route('thankyou'));
        
    }


}