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
        $validatedData = $request->validate([
            'q1' => ['required', 'integer'],
            'q2' => ['required', 'integer'],
            'q3' => ['required', 'integer'],
            'q4' => ['required', 'integer'],
            'q5' => ['required', 'integer'],
            'q6' => ['required', 'integer'],
            'q7' => ['max:255', 'string'],
            'q8' => ['max:255', 'string'],
            'trivial_question' => ['required', 'boolean'],
            'fastClickCount' => ['required', 'integer'],
        ]);

        $alreadyAnswered = TrainingReaction::where([
            'user_id' => $user->id,
        ])->exists();

        if (!$alreadyAnswered) {
            $validatedData['user_id'] = $user->id;
            TrainingReaction::create($validatedData);
        }

        $user->training_reaction_completed = now();
        $user->save();
        return redirect()->route('goodbye');
    }
}


