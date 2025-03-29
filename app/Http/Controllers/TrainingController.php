<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Models\User;
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

        if (! $training->completed) {
            return view("training.status_not_ready");
        }

        $training = $this->addCSS($training);
        return view('training.training_show', ["training" => $training]);
    }


     public static function createTraining()
    {
        $user = Auth::user();
        if ($user->training == null) {
            if ($user->training_personalization !== "ono") {  // If training is personalized, generate it from scratch
                $training = Training::create([
                    'user_id' => $user->id,
                    'completed' => false
                ]);
                ProcessTraining::dispatch($training->id);
            } else {  // otherwise, take the non-personalized one
                $training = Training::create([
                    'user_id' => $user->id,
                    'completed' => true
                ]);
                $training->setToNonPersonalizedVersion();
            }
        } else {
            $training = $user->training;
        }
        return $training;
        //return redirect()->route('emails', ['folder' => 'inbox']);  // Start pre-train classification task
    }


    public function completeTraining(Request $request)
    {
        $user = Auth::user();
        $timeSpent = $request->query('time', 0); // Get time from URL

        // Save time spent in DB or perform necessary actions
        $user_training = $user->training;
        $user_training->update([
            'training_completed_at' => now(),
            'time_taken' => $timeSpent
        ]);
        $user->training_completed = now();
        $user->save();
        return redirect()->route('emails', ['folder' => 'inbox']);  // Start post-train classification task
    }


    private function addCSS($tranining) {
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
                $newClass = trim($existingClass . ' bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full');
                $button->setAttribute('class', $newClass);
            }
            // Add style to <li>
            foreach ($xpath->query('//li') as $li) {
                $existingStyle = $li->getAttribute('style');
                $newClass = trim($existingStyle . '; list-style: inside;');
                $li->setAttribute('class', $newClass);
            }

            $updatedHtml = preg_replace('/^<!DOCTYPE.+?>/', '', $dom->saveHTML());
            $tranining->$section = $updatedHtml;
        }
        return $tranining;
    }

}
