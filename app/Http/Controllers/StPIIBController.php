<?php

namespace App\Http\Controllers;

use App\Models\UserQuestionnaireScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StPIIB;
use App\Models\Questionnaire;
use App\Models\UserQuestionnaireAnswer;
use App\Models\QuestionnaireCampaign;
use App\Models\UserHfThreat;
use App\Models\HumanFactor;
use App\Models\Threat;

class StPIIBController extends Controller
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

        $alreadyAnswered = StPIIB::where([
            'user_id' => Auth::id(),
        ])->exists();

        // Check if the user has already answered
        if (!$alreadyAnswered) {
            $validatedData['user_id'] = Auth::id();
            $answer = StPIIB::create($validatedData);

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
        session(['questionnaire_2done' => true]);
        session(['questionnaire2_view' => false]);
        return redirect()->route('questionnaires')->with('success', 'Questionnaire completed successfully!');
    }

    public function calculateScales($id)
    {
        $stp_ii_b = StPIIB::findOrFail($id);
        // Reverse-scored items:
        $reverseItems = [16, 17, 18];
        $maxScore = 7; // scores range

        // Extract responses and apply reverse scoring
        $responses = [];
        for ($i = 1; $i <= 30; $i++) {
            $question = 'q' . $i;
            $responses[$i] = in_array($i, $reverseItems) ? ($maxScore + 1 - $stp_ii_b->$question) : $stp_ii_b->$question;
        }

        // Calculate scales
        $scales = [
            'lack_of_premeditation' => ($responses[1] + $responses[2] + $responses[3]) / 3,
            'need_for_consistency' => ($responses[4] + $responses[5] + $responses[6]) / 3,
            'sensation_seeking' => ($responses[7] + $responses[8] + $responses[9]) / 3,
            'lack_of_self_control' => ($responses[10] + $responses[11] + $responses[12]) / 3,
            'social_influence' => ($responses[13] + $responses[14] + $responses[15]) / 3,
            'need_for_avoidance_of_similarity' => ($responses[16] + $responses[17] + $responses[18]) / 3,
            'risk_preferences' => ($responses[19] + $responses[20] + $responses[21]) / 3,
            'positive_attitudes_towards_advertising' => ($responses[22] + $responses[23] + $responses[24]) / 3,
            'need_for_cognition' => ($responses[25] + $responses[26] + $responses[27]) / 3,
            'need_for_uniqueness' => ($responses[28] + $responses[29] + $responses[30]) / 3,
        ];

        return $scales;
    }

}
