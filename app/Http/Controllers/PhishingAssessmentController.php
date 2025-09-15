<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Training;
use App\Jobs\ProcessTraining;
use Carbon\Carbon;
use Throwable;

class PhishingAssessmentController extends Controller
{
    /**
     * Mostra il form del questionario.
     */
    public function create()
    {
        $sections = trans('phishing_assessment.sections');
        $scale    = (array) trans('phishing_assessment.scale');

        $startedAt = now();                           // server time
        session(['phishing_started_at' => $startedAt]);
        $startedToken = encrypt($startedAt->toIso8601String());

        return view('questionnaires.phishing-assessment', compact(
            'sections',
            'scale',
            'startedToken'
        ));
    }

    /**
     * Riceve il POST, valida e salva i dati.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login'); // o abort(401)
        }

        $validatedData = $request->validate([
            'Q1' => ['required', 'integer'],
            'Q2' => ['required', 'integer'],
            'Q3' => ['required', 'integer'],
            'Q4'  => ['required','in:1-overclaiming,1,2,3,4,5'],
            'Q5' => ['required', 'integer'],
            'Q6' => ['required', 'integer'],
            'Q7' => ['required', 'integer'],
            'Q8' => ['required', 'integer'],
            'Q9' => ['required', 'integer'],
            'Q10' => ['required', 'integer'],
            'Q11' => ['required', 'integer'],
            'AC1' => ['required', 'integer'],
            'Q12' => ['required', 'integer'],
            'Q13' => ['required', 'integer'],
            'Q14' => ['required', 'integer'],
            'Q15' => ['required', 'integer'],
            'Q16' => ['required', 'integer'],
            'Q17' => ['required', 'integer'],
            'Q18' => ['required', 'integer'],
            'Q19' => ['required','in:1-overclaiming,1,2,3,4,5'],
            'AC2' => ['required', 'integer'],
            'Q20_SCENARIO' => ['required', 'integer'],
            'Q21_SCENARIO' => ['required', 'integer'],
            'Q22_SCENARIO' => ['required', 'integer'],
            'Q23_SCENARIO' => ['required', 'integer'],
            'Q24_SCENARIO' => ['required', 'integer'],
            'Q25_SCENARIO' => ['required', 'integer'],
            /*
            'control_question_0' => ['required', 'boolean'],
            'control_question_1' => ['required', 'boolean'],
            'control_question_2' => ['required', 'boolean'],
            'control_question_3' => ['required', 'boolean'],
            'control_question_4' => ['required', 'boolean'],
            */
            'fastClickCount' => ['required', 'integer'],
        ]);

        $started = session('phishing_started_at');
        if ($request->filled('started_token')) {
            try { $started = Carbon::parse(decrypt($request->started_token)); } catch (Throwable $e) {}
        }

        $alreadyAnswered = Assessment::where([
            'user_id' => $user->id,
        ])->exists();

        if ($alreadyAnswered) {
            return redirect()->route("emails", ['folder' => 'inbox'])->with('error', trans('phishing_assessment.already_answered'));
        }

        $sections = trans('phishing_assessment.sections');
        $scale    = (array) trans('phishing_assessment.scale');
        $maxScore = count($scale);
        $attentionFlag = false;
        $contAC = 0;
        $raw    = [];

        //Salvataggio Punteggi ed Attention Checks
        foreach ($sections as $sect) {
            foreach ($sect['items'] as $item) {
                $id = $item['id'] ?? null;
                if ($id === null || !array_key_exists($id, $validatedData)) {
                    continue;
                }
                if ($id === 'AC1' || $id === 'AC2') {
                    $expectedKey = $id === 'AC1' ? 'answer_ac1' : 'answer_ac2';
                    $expected    = isset($item[$expectedKey]) ? (int)$item[$expectedKey] : null;
                    $given       = (int)$validatedData[$id];

                    if ($expected === null || $given !== $expected) {
                        $contAC++;
                        if ($contAC === 1) {
                            $attentionFlag= true;
                        }
                        elseif ($contAC === 2)
                            return redirect()->route('expelUser');
                    }
                    continue;
                }
                $val = $validatedData[$id];
                $raw[$id] = ($val === '1-overclaiming') ? 1 : (int)$val;
            }
        }

        $techM = round((($raw['Q1'] ?? 0) + ($raw['Q2'] ?? 0) + ($raw['Q3'] ?? 0) + ($raw['Q5'] ?? 0)) / 4, 2);

        //OverClaiming Bias
        $controlItems = (array) trans('phishing_assessment.scoring.control_items');
        $BiasControl = collect($controlItems)
            ->contains(fn($qid) => (int)($raw[$qid] ?? 0) >= 4);

        $overclaiming = $BiasControl && ($techM <= 2.5);

        //Reverse scoring
        $reverseItems = (array) trans('phishing_assessment.scoring.reverse_items');

        $processed = [];
        foreach ($raw as $qid => $val) {
            $v = (int) $val;
            $processed[$qid] = in_array($qid, $reverseItems, true)
                ? ($maxScore + 1 - $v)
                : $v;
        }

        //Dimension Scores
        $exclude = (array) trans('phishing_assessment.scoring.control_items');
        $dims    = (array) trans('phishing_assessment.scoring.dimensions');

        $dimensionScores = [];
        foreach ($dims as $dim => $ids) {
            $ids = array_values(array_diff($ids, $exclude));
            $dimensionScores[$dim] = array_sum(
                array_map(fn($qid) => (int)($processed[$qid] ?? 0), $ids)
            );
        }

        /*Penalità over-claiming su due dimensioni
        if ($overclaiming) {
            foreach (['Technical_Competence','Metacognitive_Awareness'] as $dim) {
                if (isset($dimensionScores[$dim])) {
                    $dimensionScores[$dim] = round($dimensionScores[$dim] * 0.85, 2);
                }
            }
        }*/

        $TECH_SCORE = (int)($processed['Q1'] ?? 0) + (int)($processed['Q2'] ?? 0) + (int)($processed['Q3'] ?? 0) + (int)($processed['Q5'] ?? 0);

        $BEH_SCORE  = array_sum(array_map(fn($q)=> (int)($processed[$q] ?? 0), ['Q6','Q7','Q8','Q9','Q10','Q11']));

        $PSY_RAW_MEAN = array_sum(array_map(fn($q)=> (int)($raw[$q] ?? 0), ['Q12','Q13','Q14','Q15','Q16'])) / 5.0;

        $META_SCORE = array_sum(array_map(fn($q)=> (int)($processed[$q] ?? 0), ['Q17','Q18']));

        $Q17_original = (int)($raw['Q17'] ?? 0);

        $scenarioMap   = trans('phishing_assessment.scenario_types');
        $priorityList  = trans('phishing_assessment.scenario_priority');
        if (!is_array($priorityList)) $priorityList = [];
        if (!is_array($scenarioMap)) $scenarioMap = [];
        $prioIdx = array_flip($priorityList);

        $atRisk = [];
        foreach ($scenarioMap as $qid => $type) {
            $v = (int)($raw[$qid] ?? 0);
            if ($v <= 3) $atRisk[$type] = true;
        }

        $SCENARIO_TYPES_RISK = array_keys($atRisk);
        usort($SCENARIO_TYPES_RISK, fn($a,$b) => ($prioIdx[$a] ?? PHP_INT_MAX) <=> ($prioIdx[$b] ?? PHP_INT_MAX));

        $SCENARIO_RISK_COUNT = count($SCENARIO_TYPES_RISK);

        //Risk score e level
        $totalPossible = (array_sum(trans('phishing_assessment.total_possible'))) - (count($exclude)*$maxScore);
        $TOTAL_SCORE      = array_sum($dimensionScores);
        $RISK_PERCENT     = max(0, 100 - (($TOTAL_SCORE  / $totalPossible) * 100));
        $RISK_PERCENT     = round($RISK_PERCENT, 1);
        $riskLevel = $this->getRiskLevel($RISK_PERCENT);

        /*
        //Training modules
        $mapping = (array) trans('phishing_assessment.training_mapping');
        $trainingModules = [];
        foreach ($weaknesses as $dim) {
            $trainingModules = array_merge($trainingModules, $mapping[$dim] ?? []);
        }
        */

        //Debolezze
        $weaknesses = [];
        if ($TECH_SCORE <= 15)              $weaknesses[] = 'Technical_Competence';
        if ($BEH_SCORE  <= 18)              $weaknesses[] = 'Security_Behaviors';
        if ($PSY_RAW_MEAN >= 3.5)           $weaknesses[] = 'Psychological_Vulnerabilities';
        if ($overclaiming)                  $weaknesses[] = 'Metacognitive_Awareness';
        if ($SCENARIO_RISK_COUNT >= 2)      $weaknesses[] = 'Behavioral_Intentions';

        $derived = compact(
            'TECH_SCORE','BEH_SCORE','PSY_RAW_MEAN','META_SCORE','Q17_original',
            'SCENARIO_RISK_COUNT','SCENARIO_TYPES_RISK','TOTAL_SCORE','RISK_PERCENT');

        $modulePriorities = [
            'psy' => $PSY_RAW_MEAN >= 3.5,
            'tech'  => $TECH_SCORE   <= 15,
            'beh'   => $BEH_SCORE    <= 18,
            'meta'  => $overclaiming,
            'ac_fail' => $attentionFlag,
            'scenario_risk_count' => $SCENARIO_RISK_COUNT >=2,
            'scenarios' => array_slice($SCENARIO_TYPES_RISK, 0, 2),
        ];

        //Salvataggio
        $assessment = Assessment::create([
            'user_id'          => Auth::id(),
            'raw_scores'       => $raw,
            'processed_scores' => $processed,
            'dimension_scores' => $dimensionScores,
            'weaknesses'       => $weaknesses,
            'derived_metrics'  => $derived,
            'training_modules' => $modulePriorities,
            'overclaiming'     => $overclaiming,
            'attention_flag'   => $attentionFlag,
            'risk_score'       => $RISK_PERCENT,
            'risk_level'       => $riskLevel,
            'started_at'       => $started,
            'completed_at'     => now(),
        ]);

        $user->questionnaires_completed = now();
        $user->save();

        session()->forget('phishing_started_at');

        $pendingTraining = $user->trainings()->where('generated', false)->latest('id')->first();

        if (!$pendingTraining) {
            $pendingTraining = Training::create([
                'user_id' => $user->id,
                'assessment_id' => $assessment->id,
                'generated' => ($user->training_personalization === 'no'),
            ]);

            if ($user->training_personalization !== 'no') {
                ProcessTraining::dispatch($pendingTraining->id, $user);
            } else {
                $pendingTraining->setNonCustomizedVersion();
            }
        }

        return redirect()->route("emails", ['folder' => 'inbox'])->with('success', trans('phishing_assessment.completed_successfully'));
    }
    private function getRiskLevel(float $score): string
    {
        if ($score <= 33)  return trans('phishing_assessment.risk_levels.low');
        if ($score <= 66)  return trans('phishing_assessment.risk_levels.medium');
        if ($score <= 100)  return trans('phishing_assessment.risk_levels.high');
        return false;
    }

    protected static function booted(): void
    {
        static::created(function (Assessment $assessment) {
            $user = $assessment->user;        // assumes belongsTo(User::class)

            // Always personalized → never mark generated upfront
            $training = Training::firstOrCreate(
                ['user_id' => $user->id, 'assessment_id' => $assessment->id],
                ['generated' => false]
            );

            // Queue the personalized generation (after DB commit)
            ProcessTraining::dispatch($training->id, $user)->afterCommit();

        });

    }
}
