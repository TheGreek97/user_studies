<?php

namespace App\Http\Controllers;

use App\Models\TrainingReaction;
use App\Models\UserQuestionnaireScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BFI2XS;
use App\Models\Questionnaire;
use App\Models\UserQuestionnaireAnswer;
use App\Models\QuestionnaireCampaign;
use App\Models\UserHfThreat;
use App\Models\HumanFactor;
use App\Models\Threat;

class TrainingReactionController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'q1' => ['required', 'integer'],
            'q2' => ['required', 'integer'],
            'q3' => ['required', 'integer'],
            'q4' => ['required', 'integer'],
            'q5' => ['required', 'integer'],
            'q6' => ['required', 'integer'],
            'q7' => ['required', 'string'],
            'trivial_question' => ['required', 'boolean'],
            'fastClickCount' => ['required', 'integer'],
        ]);

        $alreadyAnswered = TrainingReaction::where([
            'user_id' => Auth::id(),
        ])->exists();

        if (!$alreadyAnswered) {
            $validatedData['user_id'] = Auth::id();
            TrainingReaction::create($validatedData);
        } 
        session(['questionnaire_4done' => true]);
        return redirect()->route('questionnaire', ['step' => 5])->with('success', 'Questionnaire 4 completed successfully!');
    }
}


