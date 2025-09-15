<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Models\Assessment;
use App\Models\TrainingQuizResult;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class TrainingController extends Controller
{
    public function showTraining()
    {
        $user = Auth::user();

        // Must have completed the assessment first
        $assessment = Assessment::where('user_id', $user->id)->latest()->first();
        if (! $assessment) {
            return Route::has('phishing-assessment')
                ? redirect()->route('phishing-assessment')
                : redirect()->back()->with('error', 'Please complete the phishing assessment first.');
        }

        // Only the latest training for this assessment
        $training = Training::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->latest('created_at')
            ->first();

        if ($training === null) {
            $training = $this->createTraining($assessment);
        }

        $training_length = $user->training_length;
        if (env('DISABLE_TIMERS') === 'true') {
            $wait_times = [
                'introduction'       => 0,
                'modules'            => 0,
                'mini_scenarios'     => 0,
                'quiz'               => 0,
                'consolidation'      => 0,
                'cta'                => 0,
            ];
        } else {
            $short = $user->training_length === 'short';
            $wait_times = [
                'introduction'       => $short ? 20 : 40,
                'modules'            => $short ? 90 : 180,
                'mini_scenarios'     => $short ? 40 : 80,
                'quiz'               => $short ? 45 : 90,
                'consolidation'      => $short ? 15 : 30,
                'cta'                => $short ? 10 : 20,
            ];
        }


        if (!empty($training->introduction) || $training->generated) {
            $training = $this->addCSS($training);
        }

        $disableTimers = filter_var(env('DISABLE_TIMERS', false), FILTER_VALIDATE_BOOLEAN);
        $timersEnabled = ! $disableTimers;

        return view('training.training_show', [
            'training'      => $training,
            'wait_times'    => $wait_times,
            'timersEnabled' => $timersEnabled,
            'risk_score' => optional($assessment)->risk_score,
        ]);
    }

    // polled by the view
    public function status()
    {
        $user = Auth::user();
        $assessment = Assessment::where('user_id', $user->id)->latest()->first();
        if (! $assessment) {
            return response()->json(['generated' => false, 'ready' => false, 'updated_at' => null]);
        }

        $training = Training::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->latest('created_at')
            ->first();

        if (! $training) {
            return response()->json(['generated' => false, 'ready' => false, 'updated_at' => null]);
        }

        return response()->json([
            'generated'  => (bool) $training->generated,
            'ready'      => (bool) !empty($training->introduction),
            'updated_at' => optional($training->updated_at)->toIso8601String(),
        ]);
    }

    // NEW: store final quiz score
    public function storeQuizScore(Request $request)
    {
        $user = Auth::user();

        $v = Validator::make($request->all(), [
            'training_id'     => 'required|exists:trainings,id',
            'total_questions' => 'required|integer|min:0',
            'correct_answers' => 'required|integer|min:0',
            'percent'         => 'required|integer|min:0|max:100',
            'details'         => 'nullable|array',
        ]);
        if ($v->fails()) {
            return response()->json(['ok'=>false,'errors'=>$v->errors()], 422);
        }

        $training = Training::where('id', $request->training_id)
            ->where('user_id', $user->id)->firstOrFail();

        $data = $v->validated();

        $row = TrainingQuizResult::updateOrCreate(
            ['user_id'=>$user->id, 'training_id'=>$training->id],
            [
                'total_questions' => $data['total_questions'],
                'correct_answers' => $data['correct_answers'],
                'percent'         => $data['percent'],
                'details'         => $data['details'] ?? null,
            ]
        );

        return response()->json(['ok'=>true,'id'=>$row->id]);
    }

    public static function createTraining(Assessment $assessment): Training
    {
        $user = Auth::user();

        $pending = Training::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('generated', false)
            ->latest('id')
            ->first();

        if ($pending) return $pending;

        $training = Training::create([
            'user_id'       => $user->id,
            'assessment_id' => $assessment->id,
            'generated'     => false,
        ]);

        ProcessTraining::dispatch($training->id, $user)
            ->afterCommit()
            ->onQueue('training');

        return $training;
    }

    public function completeTraining(Request $request)
    {
        $user = Auth::user();
        $timeSpent = (int) $request->query('time', 0);

        $latest = $user->trainings()->latest('created_at')->first();
        if ($latest) {
            $latest->update([
                'completed_at' => now(),
                'time_taken'   => $timeSpent,
            ]);
        }

        $user->training_completed = now();
        $user->save();

        session(['startStudy' => true]);

        return redirect()->route('emails', ['folder' => 'inbox']);
    }

    private function addCSS(Training $training): Training
    {
        foreach (['introduction', 'scenario', 'defense_strategies', 'conclusions'] as $section) {
            $html = $training->$section;
            if (! $html) continue;

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//h1 | //h2') as $heading) {
                $heading->setAttribute(
                    'class',
                    trim($heading->getAttribute('class') . ' text-xl mt-8 mb-6 font-bold')
                );
            }
            foreach ($xpath->query('//h3') as $heading) {
                $heading->setAttribute(
                    'class',
                    trim($heading->getAttribute('class') . ' text-l mt-8 mb-4 font-bold')
                );
            }
            foreach ($xpath->query('//p') as $p) {
                $p->setAttribute('class', trim($p->getAttribute('class') . ' mb-4'));
            }
            foreach ($xpath->query('//div') as $div) {
                $div->setAttribute('class', trim($div->getAttribute('class') . ' mb-10'));
            }
            foreach ($xpath->query('//button[not(ancestor::div[contains(@class,"mcq")])]') as $btn) {
                $btn->setAttribute(
                    'class',
                    trim($btn->getAttribute('class') . ' bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full mb-4')
                );
            }
            foreach ($xpath->query('//ul | //ol') as $list) {
                $list->setAttribute('class', trim($list->getAttribute('class') . ' mb-6'));
                $list->setAttribute(
                    'style',
                    trim($list->getAttribute('style') . '; list-style-position: inside; list-style-type: initial')
                );
            }

            $training->$section = preg_replace('/^<!DOCTYPE.+?>/i', '', $dom->saveHTML());
        }

        if ($training->exercises) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($training->exercises, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//h1 | //h2') as $heading) {
                $heading->setAttribute(
                    'class',
                    trim($heading->getAttribute('class') . ' text-xl mt-8 mb-6 font-bold')
                );
            }
            foreach ($xpath->query('//h3') as $h3) {
                $h3->setAttribute(
                    'class',
                    trim($h3->getAttribute('class') . ' text-l mt-8 mb-4 font-bold')
                );
            }
            foreach ($xpath->query('//button') as $btn) {
                $btn->setAttribute(
                    'class',
                    trim($btn->getAttribute('class') . ' bg-gray-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full')
                );
            }

            $training->exercises = preg_replace('/^<!DOCTYPE.+?>/i', '', $dom->saveHTML());
        }

        return $training;
    }
}
