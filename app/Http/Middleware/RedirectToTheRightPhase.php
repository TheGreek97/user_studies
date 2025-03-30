<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TrainingController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RedirectToTheRightPhase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $currentRoute = Route::currentRouteName(); // Get the current route name
        error_log($currentRoute);
        /* Redirect if the user has completed the study
        if ($user && $user->study_completed && $currentRoute !== "goodbye") {
            //dd($user->study_completed, $currentRoute);
            return redirect()->route('goodbye');
        }*/

        // Redirect if the user was marked as banned from the study
        if ($user && $user->expelled
            && $currentRoute !== "expelUser") {
            return redirect()->route('expelUser');
        } elseif ($user && $user->study_completed) {
            // Redirect the user to the goodbye page if they have completed the study already
            if ($currentRoute !== 'goodbye') {
                return redirect()->route('goodbye');
            }
        } elseif (! $user->given_consent) {
            // Redirect the user to the welcome page + consent agreement
            if ($currentRoute !== 'welcome') {
                return redirect()->route('welcome')->with(['PROLIFIC_PID' => $request->input('PROLIFIC_PID')]);
            }
        } elseif (!$user->demographics_completed){
            if ($currentRoute !== 'questionnaire') {
                return redirect()->route('questionnaire', ['step' => 0]);
            }
        } elseif (!$user->bfi_completed) {
            if ($currentRoute !== 'questionnaire') {
                return redirect()->route('questionnaire', ['step' => 1]);
            }
        } elseif (!$user->stp_completed){
            if ($currentRoute !== 'questionnaire') {
                return redirect()->route('questionnaire', ['step' => 2]);
            }
        } elseif (!$user->teique_completed){
            if ($currentRoute !== 'questionnaire') {
                return redirect()->route('questionnaire', ['step' => 3]);
            }
        } elseif (!$user->pre_training_completed) {
            if ($currentRoute !== 'emails') {
                error_log("Redirecting to emails (pre-train) from ". $currentRoute);
                return redirect()->route("emails", ['folder' => 'inbox']);
            }
        }
        elseif (!$user->training_completed) {
            if ($currentRoute !== 'training.show') {
                error_log("Redirecting to training.show from ". $currentRoute);
                return redirect()->route('training.show');
            }
        } elseif (!$user->post_training_completed){
            if ($currentRoute !== 'emails') {
                error_log("Redirecting to emails (post-train) from ". $currentRoute);
                return redirect()->route('emails', ['folder' => 'inbox']);
            }
        } elseif (!$user->training_reaction_completed){
            if ($currentRoute !== 'questionnaire') {
                error_log("Redirecting to training react (post-train) from ". $currentRoute);
                return redirect()->route('questionnaire', ['step' => 4]);
            }
        }

        return $next($request);
    }
}
