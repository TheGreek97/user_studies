<?php

namespace App\Http\Controllers;

use App\Http\Livewire\ScenarioClues;
use App\Http\Livewire\ScenarioQuestion;
use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Models\TrainingResponse;
use App\Models\TutorSession;
use App\Models\User;
use App\Services\Tutor\PersonalRules;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function showTraining()
    {
        $user = Auth::user();
        // session()->put('training_done', true);
        //if (!session()->has('pre_phase_done') || session()->has('training_done')) {
            //return redirect()->route('show', ['folder' => 'inbox']);
        //}
        $training = $user->training;
        if ($training == null) {  // this should not ever be reached (training is created beforehand
            $training = $this->createTraining();
        }

        if (! $training->generated) {
            return view("training.status_not_ready", ["training" => $training]);
        }
        $training_length = $user->training_length;
        if (env("DISABLE_TIMERS") == "true") {
            $wait_times = [
              "introduction" => 0,
              "scenario" => 0,
              "defense_strategies" => 0,
              "exercises" => 0,
              "conclusions" => 0
            ];
        } else {
            $wait_times = [
              "introduction" => $training_length == "short" ? 20 : 40,
              "scenario" => $training_length == "short" ? 40 : 80,
              "defense_strategies" => $training_length == "short" ? 90 : 180,
              "exercises" => $training_length == "short" ? 45 : 90,
              "conclusions" => $training_length == "short" ? 15 : 30
            ];
        }

        $training = $this->addCSS($training);
        return view('training.training_show', [
            "training" => $training,
            "wait_times" => $wait_times,
            "initial_step" => $this->initialStep($training),
        ]);
    }


    /**
     * Step the training page opens at. Paper conditions always start at step 0. Interactive condition: a reload
     * reopens the first gated module not completed yet, as far as the stored state proves the user got:
     * - Module 1 answer saved -> Module 2 (step 2); Module 2 completed (decision saved) -> Module 3 (step 3), the
     *   ungated module the user necessarily reached, not beyond;
     * - Module 4 tutor chat started and not finished -> Module 4 (step 4). The tutor session row is created (with an
     *   empty dialog state) when the chat mounts, on every page view, so "started" means at least one turn in the
     *   dialog history or an email already closed (position > 1);
     * - Module 4 completed -> the conclusions (step 5: personal rules to write, revise, or thanks).
     * Otherwise the description (step 0).
     */
    private function initialStep(Training $training): int
    {
        if (! $training->isInteractive()) {
            return 0;
        }
        $session = TutorSession::query()
            ->where('user_id', $training->user_id)
            ->where('training_id', $training->id)
            ->first();
        $started = $session !== null && ($session->position > 1 || ! empty($session->state['history'] ?? []));
        if ($started) {
            return $session->isCompleted() ? 5 : 4;
        }
        if (ScenarioClues::completedFor($training)) {
            return 3;
        }
        $m1Answered = TrainingResponse::query()
            ->where('user_id', $training->user_id)
            ->where('module', ScenarioQuestion::MODULE)
            ->where('item_key', ScenarioQuestion::ITEM)
            ->exists();

        return $m1Answered ? 2 : 0;
    }


     public static function createTraining()
    {
        $user = Auth::user();
        if ($user->training == null) {
            // Personalized conditions, including the thesis condition "interactive", are generated from scratch
            if ($user->training_personalization !== "no") {
                $training = Training::create([
                    'user_id' => $user->id,
                    'generated' => false
                ]);
                ProcessTraining::dispatch($training->id, $user);
            } else {  // otherwise, take the pre-defined non-personalized one
                $training = Training::create([
                    'user_id' => $user->id,
                    'generated' => true
                ]);
                $training->setNonCustomizedVersion();
            }
        } else {
            $training = $user->training;
        }
        return $training;
        //return redirect()->route('emails', ['folder' => 'inbox']);  // Start pre-train classification task
    }


    /**
     * JSON status of the current user's training generation, polled by the waiting page.
     */
    public function generationStatus()
    {
        $training = Auth::user()->training;
        if ($training === null) {
            return response()->json(['generated' => false, 'failed' => false], 404);
        }

        return response()->json([
            'generated' => (bool) $training->generated,
            'failed' => $training->generation_failed_at !== null,
        ]);
    }


    /**
     * Re-dispatches the generation of the current user's training after a failure.
     * Only the authenticated user's own training is considered, and only if it failed.
     */
    public function retryGeneration(Request $request)
    {
        $user = Auth::user();
        $training = $user->training;
        if ($training === null || $training->generated || $training->generation_failed_at === null) {
            abort(409, 'Training generation is not in a failed state.');
        }

        // Atomic compare-and-set: two concurrent retries must not dispatch two paid generation jobs.
        $claimed = Training::whereKey($training->id)
            ->where('generated', false)
            ->whereNotNull('generation_failed_at')
            ->update(['generation_failed_at' => null, 'generation_error' => null]);
        if ($claimed !== 1) {
            abort(409, 'Training generation is not in a failed state.');
        }
        ProcessTraining::dispatch($training->id, $user);

        if ($request->expectsJson()) {
            return response()->json(['generated' => false, 'failed' => false], 202);
        }
        return redirect()->route('training.show');
    }


    public function completeTraining(Request $request)
    {
        $user = Auth::user();
        // Seconds spent, from the URL; integer column, so anything non-numeric becomes 0 instead of a SQL error.
        $timeSpent = max(0, (int) $request->query('time', 0));

        $user_training = $user->training;
        if ($user_training === null) {
            return redirect()->route('training.show');
        }
        // Interactive condition: Module 4 is complete only when the tutor says so. The "Next" gate in
        // training_show is client-side (Alpine `tutorDone`), so it is enforced here too.
        if ($user_training->isInteractive() && ! $this->tutorCompleted($user_training)) {
            return redirect()->route('training.show')
                ->with('error', 'Please complete the Module 4 exercises with the tutor before finishing the training.');
        }
        // Interactive condition, Module 5: the revised personal rules must be saved (rules_v2). The "Proceed" gate
        // in training_show is client-side (Alpine `rulesDone`), so it is enforced here too.
        if ($user_training->isInteractive() && ! PersonalRules::completed($user_training)) {
            return redirect()->route('training.show')
                ->with('error', 'Please write and save your personal rules in the Conclusion before finishing the training.');
        }

        // Save time spent in DB or perform necessary actions
        // The completion time is users.training_completed (trainings.completed_at was dropped upstream in
        // 2025_03_28_133204); the training row keeps only the time spent.
        $user_training->update(['time_taken' => $timeSpent]);

        $user->training_completed = now();
        $user->save();

        session(['startStudy' => true]);  // Needed to show instructions in the post-train phase

        return redirect()->route('emails', ['folder' => 'inbox']);  // Start post-train classification task
    }


    /** Whether the user's Module 4 tutor conversation for this training is finished (TutorConversation). */
    private function tutorCompleted(Training $training): bool
    {
        return TutorSession::query()
            ->where('user_id', $training->user_id)
            ->where('training_id', $training->id)
            ->whereNotNull('completed_at')
            ->exists();
    }


    // Public static: the interactive Module 2 component (ScenarioClues) styles the scenario HTML the same way.
    public static function addCSS($tranining) {
        foreach (["introduction", "scenario", "defense_strategies", "conclusions"] as $section) {
            $html = $tranining->$section;
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            // Add classes to <h1> and <h2>
            foreach ($xpath->query('//h1 | //h2') as $heading) {
                $existingClass = $heading->getAttribute('class');
                $newClass = trim($existingClass . ' text-xl mt-8 mb-6 font-bold');
                $heading->setAttribute('class', $newClass);
            }
            // Add classes to <h3>
            foreach ($xpath->query('//h3') as $heading) {
                $existingClass = $heading->getAttribute('class');
                $newClass = trim($existingClass . ' text-l mt-8 mb-4 font-bold');
                $heading->setAttribute('class', $newClass);
            }
            // Add classes to <p>
            foreach ($xpath->query('//p') as $paragraph) {
                $existingClass = $paragraph->getAttribute('class');
                $newClass = trim($existingClass . ' mb-4');
                $paragraph->setAttribute('class', $newClass);
            }
            // Add classes to <div>
            foreach ($xpath->query('//div') as $div) {
                $existingClass = $div->getAttribute('class');
                $newClass = trim($existingClass . ' mb-10');
                $div->setAttribute('class', $newClass);
            }
            // Add classes to <button>
            foreach ($xpath->query('//button') as $button) {
                $existingClass = $button->getAttribute('class');
                $newClass = trim($existingClass . ' bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full mb-4');
                $button->setAttribute('class', $newClass);
            }
            // Add style to <ul>, <ol>
            foreach ($xpath->query('//ul | //ol') as $li) {
                $existingStyle = $li->getAttribute('style');
                $existingClass = $li->getAttribute('class');
                $newStyle = trim($existingStyle . '; list-style-position: inside; list-style-type: initial');
                $newClass = trim($existingClass . ' mb-6');
                $li->setAttribute('class', $newClass);
                $li->setAttribute('style', $newStyle);
            }

            $updatedHtml = preg_replace('/^<!DOCTYPE.+?>/', '', $dom->saveHTML());
            $tranining->$section = $updatedHtml;
        }
        foreach (["exercises"] as $section) {
            $html = $tranining->$section;
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            foreach ($xpath->query('//h1 | //h2') as $heading) {
                $existingClass = $heading->getAttribute('class');
                $newClass = trim($existingClass . ' text-xl mt-8 mb-6 font-bold');
                $heading->setAttribute('class', $newClass);
            }
            // Add classes to <h3>
            foreach ($xpath->query('//h3') as $heading) {
                $existingClass = $heading->getAttribute('class');
                $newClass = trim($existingClass . ' text-l mt-8 mb-4 font-bold');
                $heading->setAttribute('class', $newClass);
            }
            // Add classes to <button>
            foreach ($xpath->query('//button') as $btn) {
                $existingClass = $btn->getAttribute('class');
                $newClass = trim($existingClass . ' bg-gray-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full');
                $btn->setAttribute('class', $newClass);
            }
            $updatedHtml = preg_replace('/^<!DOCTYPE.+?>/', '', $dom->saveHTML());
            $tranining->$section = $updatedHtml;
        }
        return $tranining;
    }

}
