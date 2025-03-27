<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TrainingController extends Controller
{
    public function showTraining()
    {
        $user = Auth::user();
        if (!session()->has('pre_phase_done') || session()->has('training_done')) {
            return redirect()->route('show', ['folder' => 'inbox']);
        }
        $training = $user->training;
        if ($training == null) {
            return redirect()->route('training_create');
        } else if (! $training->completed) {
            return view("training.status_not_ready");
        }
        return view('training.training_show', ["training" => $training]);
    }

     public function createTraining()
    {
        $training = Training::create([
            'user_id' => Auth::id(),
            'completed' => false
        ]);

        ProcessTraining::dispatch($training->id);
        session(["training_generation_started" => true]);
        return redirect()->route('show', ['folder' => 'inbox']);
    }
}


