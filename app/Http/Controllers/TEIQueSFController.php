<?php

namespace App\Http\Controllers;

use App\Models\UserQuestionnaireScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TEIQueSF;
use App\Models\Questionnaire;
use App\Models\UserQuestionnaireAnswer;
use App\Models\QuestionnaireCampaign;
use App\Models\UserHfThreat;
use App\Models\HumanFactor;
use App\Models\Threat;

class TEIQueSFController extends Controller
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
            'q7' => ['required', 'integer'],
            'q8' => ['required', 'integer'],
            'q9' => ['required', 'integer'],
            'q10' => ['required', 'integer'],
            'q11' => ['required', 'integer'],
            'q12' => ['required', 'integer'],
            'q13' => ['required', 'integer'],
            'q14' => ['required', 'integer'],
            'q15' => ['required', 'integer'],
            'q16' => ['required', 'integer'],
            'q17' => ['required', 'integer'],
            'q18' => ['required', 'integer'],
            'q19' => ['required', 'integer'],
            'q20' => ['required', 'integer'],
            'q21' => ['required', 'integer'],
            'q22' => ['required', 'integer'],
            'q23' => ['required', 'integer'],
            'q24' => ['required', 'integer'],
            'q25' => ['required', 'integer'],
            'q26' => ['required', 'integer'],
            'q27' => ['required', 'integer'],
            'q28' => ['required', 'integer'],
            'q29' => ['required', 'integer'],
            'q30' => ['required', 'integer'],
        ]);

        $alreadyAnswered = TEIQueSF::where([
            'user_id' => Auth::id(),
        ])->exists();

        // Check if the user has already answered
        if (!$alreadyAnswered) {
            $validatedData['user_id'] = Auth::id();
            $answer = TEIQueSF::create($validatedData);

            $scales = $this->calculateScales($answer->id);
            $userScale = UserQuestionnaireScale::where('user_id', Auth::id())->first();
            if ($userScale) {
                $userScale->update($scales);
            } else {
                UserQuestionnaireScale::create(array_merge(['user_id' => Auth::id()], $scales));
            }

        } else {
            return back()->with('error', 'Already answered');
        }
        session(['questionnaire_3done' => true]);
        session(['questionnaire3_view' => false]);
        return redirect()->route('questionnaires')->with('success', 'Questionnaire completed successfully!');
    }

    public function calculateScales($id)
    {
        $teiQueSf = TEIQueSF::findOrFail($id);
        // Reverse-scored items:
        $reverseItems = [16, 2, 18, 4, 5, 7, 22, 8, 10, 25, 26, 12, 13, 28, 14];
        $maxScore = 7; // scores range

        // Extract responses and apply reverse scoring
        $responses = [];
        for ($i = 1; $i <= 30; $i++) {
            $question = 'q' . $i;
            $responses[$i] = in_array($i, $reverseItems) ? ($maxScore + 1 - $teiQueSf->$question) : $teiQueSf->$question;
        }

        // Calculate scales
        $scales = [
            'total_tei' => array_sum($responses) / count($responses),
            'well_being' => ($responses[5] + $responses[20] + $responses[9] + $responses[24] + $responses[12] + $responses[27]) / 6,
            'self_controll' => ($responses[4] + $responses[19] + $responses[7] + $responses[22] + $responses[15] + $responses[30]) / 6,
            'emotionality' => ($responses[1] + $responses[16] + $responses[2] + $responses[17] + $responses[8] + $responses[23] + $responses[13] + $responses[28]) / 8,
            'sociability' => ($responses[6] + $responses[21] + $responses[10] + $responses[25] + $responses[11] + $responses[26]) / 6,
        ];

        return $scales;
    }

}
