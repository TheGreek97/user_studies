<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function download_info_sheet(Request $request)
    {
        //Storage::disk('local')->put('example.txt', 'Contents');
        $file_name = "information-sheet-for-anonymous-studies.pdf";
        //$file_public_path = asset('storage/'. $file_name);
        try {
            return response()->file("storage/$file_name");
        } catch (FileNotFoundException) {
            abort(404, 'File not found');
        }
    }

    public function redirectUser(Request $request) {
        $user = Auth::user();
        //First show the demographics + 3 questionnaires
        if (! $user->demographics_completed) {
            return redirect()->route('questionnaire', ['step' => 0]);
        } elseif (! $user->bfi_completed) {
            return redirect()->route('questionnaire', ['step' => 1]);
        } elseif (! $user->stp_completed) {
            return redirect()->route('questionnaire', ['step' => 2]);
        } elseif (! $user->teique_completed) {
            return redirect()->route('questionnaire', ['step' => 3]);
        } else {  // All questionnaires have been completed
            if (! session()->has("generating_training")) {
                // Start training generation after questionnaire 3 is completed
                return redirect()->route("training_create");
            }
        }
        if (! $user->pre_training_completed) {
            // Start pre-training email classification
            return redirect()->route("showEmails");
        }
        elseif (! $user->training_completed) {
            // Start Training
            return redirect()->route('training.show');
        } elseif (! $user->post_training_complete) {
            // Start post-training email classification
            return redirect()->route('emails', ['phase' => 'post']);
        } elseif (! $user->training_reaction_completed) {
            //Training Reaction Questionnaire
            return redirect()->route('questionnaire', ['step' => 4]);
        } else {
            // All questionnaires and phases completed
            return redirect()->route('save-final-data');
        }
   }
}
