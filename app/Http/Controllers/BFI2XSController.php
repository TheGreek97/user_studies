<?php

namespace App\Http\Controllers;

use App\Models\UserQuestionnaireScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BFI2XS;
use App\Models\Questionnaire;
use App\Models\UserQuestionnaireAnswer;
use App\Models\QuestionnaireCampaign;
use App\Models\UserHfThreat;
use App\Models\HumanFactor;
use App\Models\Threat;

class BFI2XSController extends Controller
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
            'trivial_question' => ['required', 'boolean'],
            'fastClickCount' => ['required', 'integer'],
        ]);

        $alreadyAnswered = BFI2XS::where([
            'user_id' => Auth::id(),
        ])->exists();

        if (!$alreadyAnswered) {
            $validatedData['user_id'] = Auth::id();
            $answer = BFI2XS::create($validatedData);

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
        session(['questionnaire_1done' => true]);
        return redirect()->route('questionnaire', ['step' => 2])->with('success', 'Questionnaire 1 completed successfully!');
    }

    public function calculateScales($id)
    {
        $bfi2xs = BFI2XS::findOrFail($id);
        // Reverse-scored items:
        $reverseItems = [1, 3, 7, 8, 10, 14];
        $maxScore = 5; // scores range

        // Extract responses and apply reverse scoring
        $responses = [];
        for ($i = 1; $i <= 15; $i++) {
            $question = 'q' . $i;
            $responses[$i] = in_array($i, $reverseItems) ? ($maxScore + 1 - $bfi2xs->$question) : $bfi2xs->$question;
        }

        // Calculate scales
        $scales = [
            'bfi_extraversion' => ($responses[1] + $responses[6] + $responses[11]) / 3,
            'bfi_agreeableness' => ($responses[2] + $responses[7] + $responses[12]) / 3,
            'bfi_conscientiousness' => ($responses[3] + $responses[8] + $responses[13]) / 3,
            'bfi_negative_emotionality' => ($responses[4] + $responses[9] + $responses[14]) / 3,
            'bfi_open_mindedness' => ($responses[5] + $responses[10] + $responses[15]) / 3,
        ];

        return $scales;
    }

}

