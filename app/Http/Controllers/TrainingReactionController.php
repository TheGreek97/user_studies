<?php

namespace App\Http\Controllers;

use App\Models\TrainingReaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;


class TrainingReactionController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();

        $alreadyAnswered = TrainingReaction::where([
            'user_id' => $user->id,
        ])->exists();

        /*
            $questionnaire = new TrainingReaction();
            $questionnaire->user_id = $user->id;
            $questionnaire->q1 = $request->input('q1');
            $questionnaire->q2 = $request->input('q2');
            $questionnaire->q3 = $request->input('q3');
            $questionnaire->q4 = $request->input('q4');
            $questionnaire->q5 = $request->input('q5');
            $questionnaire->q6 = $request->input('q6');
            $questionnaire->q7 = $request->input('q7');
            $questionnaire->q8 = $request->input('q8');
            $questionnaire->trivial_question = $request->input('trivial_question');
            $questionnaire->fastClickCount = $request->input('fastClickCount');
            $questionnaire->save();
        }
        */
        $validatedData = $request->validate([
            'q1' => ['required', 'integer'],
            'q2' => ['required', 'integer'],
            'q3' => ['required', 'integer'],
            'q4' => ['required', 'integer'],
            'q5' => ['required', 'integer'],
            'q6' => ['required', 'integer'],
            'q7' => ['max:255', 'nullable', 'string'],
            'q8' => ['max:255', 'nullable', 'string'],
            'trivial_question' => ['required', 'boolean'],
            'fastClickCount' => ['required', 'integer'],
        ]);

        if (!$alreadyAnswered) {
            $validatedData['user_id'] = $user->id;
            TrainingReaction::create($validatedData);
        }

        $user->training_reaction_completed = now();
        $user->save();
        return redirect()->route('goodbye');
    }
}


