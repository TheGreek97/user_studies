<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Training;
use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessTraining implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int|string $trainingId;
    protected User $user;

    public int $tries = 3;
    public array $backoff = [30, 90, 180];

    private array $defaults = [
        'language'          => 'en',
        'prompt_pack_path'  => 'storage/app/prompts/prompts.json',
        'scenario_priority' => ['IT','Boss','Svishing','Smishing','Colleague','Offer'],
    ];

    public function __construct($trainingId, User $user)
    {
        $this->trainingId = $trainingId;
        $this->user       = $user;
        $this->onQueue('training');
    }

    public function handle(): void
    {
        $training = Training::find($this->trainingId);
        if (!$training) throw new \RuntimeException("Training id {$this->trainingId} not found");

        $assessment = Assessment::find($training->assessment_id)
            ?: Assessment::where('user_id', $this->user->id)->latest()->first();

        if (!$assessment) throw new \RuntimeException("Assessment not found for user {$this->user->id}");

        // Inputs
        $riskScore   = $assessment->risk_score;
        $weaknesses  = $this->toArray($assessment->weaknesses);
        $derived     = $this->toArray($assessment->derived_metrics);
        $tm          = $this->toArray($assessment->training_modules);

        $length        = $this->user->training_length ?? 'short'; // 'short'|'long'
        $maxModules    = ($length === 'long') ? 3 : 2;
        $maxScenarios  = ($length === 'long') ? 3 : 2;

        $modules       = $this->selectModulesFromFlags($tm, $weaknesses, $maxModules);
        $scenarioTypes = $this->selectScenarioTypes($tm, $derived, $maxScenarios);

        // Prompts
        [$systemPrompt, $introPrompt, $modulePrompt, $miniPrompt, $quizPrompt, $conPrompt] =
            $this->loadPromptsOrDefaults($this->defaults['prompt_pack_path']);

        $lang   = $this->defaults['language'];
        $system = $systemPrompt . "\nAlways write in {$lang}.";

        $name   = trim((string) ($this->user->name ?? ''));
        $role   = $this->user->role   ?? '';
        $sector = $this->user->sector ?? '';

        // User prompts
        $introUser = $this->fill($introPrompt, [
            '[NAME]'      => $name !== '' ? $name : 'Participant',
            '[ROLE]'      => $role,
            '[SECTOR]'    => $sector,
            '[RiskScore]' => is_null($riskScore) ? '' : round((float)$riskScore),
            '[WEAKNESSES]'=> implode(', ', array_column($modules, 'label')),
        ]);

        $moduleUsers = [];
        foreach ($modules as $m) {
            $moduleUsers[] = $this->fill($modulePrompt, [
                '[MODULE_LABEL]'   => $m['label'],
                '[PRIORITY_LEVEL]' => $m['priority'],
                '[NAME]'           => $name !== '' ? $name : 'Participant',
                '[ROLE]'           => $role,
                '[SECTOR]'         => $sector,
            ]);
        }

        $miniUsers = [];
        foreach ($scenarioTypes as $t) {
            $miniUsers[] = $this->fill($miniPrompt, [
                '[SCENARIO_TYPE]' => $t,
                '[NAME]'          => $name !== '' ? $name : 'Participant',
                '[ROLE]'          => $role,
                '[SECTOR]'        => $sector,
            ]);
        }

        $quizUser = $this->fill($quizPrompt, ['[MODULE_TITLES]' => implode(', ', array_column($modules,'label'))]);
        $conUser  = $this->fill($conPrompt,  ['[MODULE_TITLES]' => implode(', ', array_column($modules,'label'))]);

        // HTTP config
        $apiKey = (string) config('services.openai.api_key');
        if ($apiKey === '') {
            Log::error('OPENAI_API_KEY is missing');
            $this->fail(new \RuntimeException('OPENAI_API_KEY is missing'));
            return;
        }

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $headers  = [
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
        ];
        $model = (string) config('services.openai.training_model', 'gpt-4o-mini');

        // ---------------------------
        // PHASE 1: intro + first module + first scenario (with QA/regen)
        // ---------------------------
        $phase1Responses = Http::pool(function (Pool $pool) use ($endpoint, $headers, $model, $system, $introUser, $moduleUsers, $miniUsers) {
            $reqs = [];

            $reqs['intro'] = $pool->as('intro')->withHeaders($headers)->timeout(120)->post($endpoint, [
                'model'=>$model,
                'messages'=>[
                    ['role'=>'system','content'=>$system . ' Output VALID HTML wrapped in a single <div>. Do NOT use markdown asterisks or bracket cues.'],
                    ['role'=>'user','content'=>$introUser],
                ],
            ]);

            if (!empty($moduleUsers[0])) {
                $reqs['mod0'] = $pool->as('mod0')->withHeaders($headers)->timeout(120)->post($endpoint, [
                    'model'=>$model,
                    'messages'=>[
                        ['role'=>'system','content'=>$system . ' Output VALID HTML wrapped in a single <div>.'],
                        ['role'=>'user','content'=>$moduleUsers[0]],
                    ],
                ]);
            }

            if (!empty($miniUsers[0])) {
                $reqs['mini0'] = $pool->as('mini0')->withHeaders($headers)->timeout(120)->post($endpoint, [
                    'model'=>$model,
                    'response_format' => ['type' => 'json_object'],
                    'messages'=>[
                        ['role'=>'system','content'=> $system .
                            ' Return ONLY JSON of shape: {'.
                            '"channel":"email|sms|chat","platform":"Gmail|Outlook|Teams|WhatsApp|iMessage|Generic",'.
                            '"sender_name":"","sender_email":"","recipient_name":"",'.
                            '"subject":"","snippet":"",'.
                            '"paragraphs":["..."],'.
                            '"messages":[{"from":"sender|you","text":""}],'.
                            '"timestamp_iso":"ISO8601",'.
                            '"question":{"text":"","options":{"A":"","B":"","C":"","D":""},"correct":"A|B|C|D","explanation":""}'.
                            '}'
                        ],
                        ['role'=>'user','content'=>$miniUsers[0]],
                    ],
                ]);
            }

            return $reqs;
        });

        $introHtml = $this->extractHtml($phase1Responses['intro']);

        $mod0Html  = isset($phase1Responses['mod0'])
            ? $this->enforceMcqCorrect($this->softFormat($phase1Responses['mod0']->json('choices.0.message.content') ?? ''))
            : '';

        // --- QA + (up to 2) regeneration attempts for mini0 JSON
        $mini0 = isset($phase1Responses['mini0'])
            ? json_decode($phase1Responses['mini0']->json('choices.0.message.content') ?? '{}', true)
            : null;
        $mini0 = $this->qaOrRegenerateMini($mini0, $miniUsers[0] ?? null, $system, $model, $endpoint, $headers);
        $mini0Html = $mini0 ? $this->renderScenarioCard($mini0) : '';

        // Save progressive
        $training->refresh();
        $training->introduction       = $introHtml;
        if ($mini0Html) $training->scenario = $mini0Html;
        if ($mod0Html)  $training->defense_strategies = $mod0Html;
        $training->generated = false;
        $training->save();

        // ---------------------------
        // PHASE 2: remaining modules & scenarios (each mini gets QA/regen)
        // ---------------------------
        $phase2Payloads = [];
        for ($i=1; $i<count($moduleUsers); $i++) {
            $phase2Payloads["mod{$i}"] = [
                'model'=>$model, 'messages'=>[
                    ['role'=>'system','content'=>$system.' Output VALID HTML in a single <div>.'],
                    ['role'=>'user','content'=>$moduleUsers[$i]],
                ]
            ];
        }
        for ($i=1; $i<count($miniUsers); $i++) {
            $phase2Payloads["mini{$i}"] = [
                'model'=>$model,
                'response_format' => ['type' => 'json_object'],
                'messages'=>[
                    ['role'=>'system','content'=>$system .
                        ' Return ONLY JSON of shape: {'.
                        '"channel":"email|sms|chat","platform":"...",'.
                        '"sender_name":"","sender_email":"","recipient_name":"",'.
                        '"subject":"","snippet":"",'.
                        '"paragraphs":["..."],'.
                        '"messages":[{"from":"sender|you","text":""}],'.
                        '"timestamp_iso":"ISO8601",'.
                        '"question":{"text":"","options":{"A":"","B":"","C":"","D":""},"correct":"A|B|C|D","explanation":""}'.
                        '}'
                    ],
                    ['role'=>'user','content'=>$miniUsers[$i]],
                ]
            ];
        }

        if ($phase2Payloads) {
            $phase2Responses = Http::pool(function (Pool $pool) use ($endpoint, $headers, $phase2Payloads) {
                $reqs = [];
                foreach ($phase2Payloads as $key=>$payload) {
                    $reqs[$key] = $pool->as($key)->withHeaders($headers)->timeout(120)->post($endpoint, $payload);
                }
                return $reqs;
            });

            $appendModules   = [];
            $appendScenarios = [];
            foreach ($phase2Payloads as $key => $_) {
                if (Str::startsWith($key,'mod')) {
                    $mHtml = $this->softFormat($phase2Responses[$key]->json('choices.0.message.content') ?? '');
                    $appendModules[] = $this->enforceMcqCorrect($mHtml);
                } else {
                    $idx = (int) Str::after($key, 'mini');
                    $obj = json_decode($phase2Responses[$key]->json('choices.0.message.content') ?? '{}', true) ?: [];
                    // QA + regen for each
                    $obj = $this->qaOrRegenerateMini($obj, $miniUsers[$idx] ?? null, $system, $model, $endpoint, $headers);
                    $appendScenarios[] = $this->renderScenarioCard($obj);
                }
            }

            $training->refresh();
            if ($appendModules) {
                $training->defense_strategies = implode("\n", array_filter([
                    $training->defense_strategies,
                    ...$appendModules
                ]));
            }
            if ($appendScenarios) {
                $training->scenario = implode("\n", array_filter([
                    $training->scenario,
                    ...$appendScenarios
                ]));
            }
            $training->generated = false;
            $training->save();
        }

        // ---------------------------
        // PHASE 3: quiz (3×A–D) + consolidation + CTA (+ runtime UI)
        // ---------------------------
        $quizResponse = Http::withHeaders($headers)->timeout(120)->post($endpoint, [
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role'=>'system','content'=> 'Return ONLY JSON like: {"questions":[{"text":"","options":{"A":"","B":"","C":"","D":""},"correct":"A|B|C|D","explanations":{"A":"","B":"","C":"","D":""}}]}' ],
                ['role'=>'user','content'=> $quizUser],
            ],
        ]);

        $quizJson = json_decode($quizResponse->json('choices.0.message.content') ?? '{}', true) ?: [];
        $quizJson = $this->qaOrRegenerateQuiz(
            $quizJson,
            $quizUser,
            $system,
            $model,
            $endpoint,
            $headers
        );
        $quizHtml = $this->buildQuizHtmlFromJson($quizJson);

        // Consolidation
        $conResponse = Http::withHeaders($headers)->timeout(120)->post($endpoint, [
            'model'=>$model, 'messages'=>[
                ['role'=>'system','content'=>$system.' Output VALID HTML wrapped in a single <div>.'],
                ['role'=>'user','content'=>$conUser],
            ]
        ]);
        $conHtml = $this->extractHtml($conResponse);

        // CTA (nudges)
        $ctaResponse = Http::withHeaders($headers)->timeout(120)->post($endpoint, [
            'model'=>$model, 'messages'=>[
                ['role'=>'system','content'=>$system.' Output VALID HTML in a single <div>. Keep it actionable: 2–3 short bullet nudges (no external links) + one concise “Do it now” call-to-action line.'],
                ['role'=>'user','content'=> "Write brief follow-up nudges (e.g., enable 2FA, verify sensitive requests by phone, report suspicious messages). End with one clear CTA line."],
            ]
        ]);
        $ctaHtml = $this->extractHtml($ctaResponse);

        $runtime = $this->runtimeUiScript();   // styling + MCQ runtime

        // Final save (pack CTA after consolidation with a marker)
        $training->refresh();
        $training->exercises   = $quizHtml;
        $training->conclusions = implode("\n", array_filter([
            $conHtml,
            '<!--CTA-->',
            $ctaHtml,
            $runtime
        ]));
        $training->generated   = true;
        $training->save();

        Log::info("Training {$training->id} generated for user {$this->user->id} (QA/regen enabled; realistic email/chat scenarios; 3×A–D quiz; consolidation + CTA).");
    }

    /* ============================
       QA + Regeneration helpers
    ============================ */

    private function qaOrRegenerateMini(?array $j, ?string $userPrompt, string $system, string $model, string $endpoint, array $headers, int $maxAttempts=2): array
    {
        $attempt = 0;
        while (true) {
            if ($this->isValidMini($j)) return $j;

            if ($attempt >= $maxAttempts || !$userPrompt) {
                // last resort: fill minimal viable structure to avoid empty UI
                return $this->miniFallback($j);
            }

            // Try again with stricter system reminder
            $response = Http::withHeaders($headers)->timeout(120)->post($endpoint, [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' =>
                        $system .
                        ' Return STRICT JSON (no markdown). Ensure "recipient_name" is present, and "question.options" includes keys A..D with "correct" set to one of them.']
                    ,
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);
            $j = json_decode($response->json('choices.0.message.content') ?? '{}', true) ?: [];
            $attempt++;
        }
    }

    private function qaOrRegenerateQuiz(array $json, string $userPrompt, string $system, string $model, string $endpoint, array $headers, int $maxAttempts=2): array
    {
        $attempt = 0;
        while (true) {
            if ($this->isValidQuiz($json)) return $json;

            if ($attempt >= $maxAttempts) {
                return $this->quizFallback($json);
            }

            $response = Http::withHeaders($headers)->timeout(120)->post($endpoint, [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role'=>'system','content'=> $system .
                        ' Return STRICT JSON (no markdown). JSON shape: {"questions":[{"text":"","options":{"A":"","B":"","C":"","D":""},"correct":"A|B|C|D","explanations":{"A":"","B":"","C":"","D":""}}]}. Exactly ONE correct per question.'
                    ],
                    ['role'=>'user','content'=> $userPrompt],
                ],
            ]);
            $json = json_decode($response->json('choices.0.message.content') ?? '{}', true) ?: [];
            $attempt++;
        }
    }

    private function isValidMini(?array $j): bool
    {
        if (!$j || !is_array($j)) return false;
        foreach (['channel','platform','sender_name','recipient_name','subject','question'] as $k) {
            if (!array_key_exists($k, $j)) return false;
        }
        $q = $j['question'] ?? [];
        if (!is_array($q) || empty($q['text']) || empty($q['options']) || empty($q['correct'])) return false;
        $opts = $q['options'] ?? [];
        if (!isset($opts['A'],$opts['B'],$opts['C'],$opts['D'])) return false;
        if (!in_array(strtoupper($q['correct']), ['A','B','C','D'], true)) return false;
        return true;
    }

    private function isValidQuiz(array $json): bool
    {
        $qs = $json['questions'] ?? [];
        if (!is_array($qs) || count($qs) < 3) return false;
        foreach ($qs as $q) {
            if (empty($q['text']) || empty($q['options']) || empty($q['correct'])) return false;
            $opts = $q['options'] ?? [];
            foreach (['A','B','C','D'] as $k) if (!isset($opts[$k])) return false;
            if (!in_array(strtoupper($q['correct']), ['A','B','C','D'], true)) return false;
        }
        return true;
    }

    private function miniFallback(?array $j): array
    {
        $recipient = $this->user->name ?: 'User';
        return [
            'channel' => 'email',
            'platform'=> 'Outlook',
            'sender_name'  => $j['sender_name']  ?? 'IT Support',
            'sender_email' => $j['sender_email'] ?? 'support@example.com',
            'recipient_name'=> $recipient,
            'subject' => $j['subject'] ?? 'Security Notice',
            'snippet' => $j['snippet'] ?? 'Please review your account details.',
            'paragraphs' => $j['paragraphs'] ?? [
                    "Dear {$recipient},",
                    "We noticed unusual activity. Please verify your details using the secure link below."
                ],
            'messages' => $j['messages'] ?? [],
            'timestamp_iso' => now()->toIso8601String(),
            'question' => [
                'text' => 'What is the safest next step?',
                'options' => [
                    'A' => 'Click the link and sign in.',
                    'B' => 'Verify the sender via official channels.',
                    'C' => 'Reply with your credentials.',
                    'D' => 'Forward to everyone.'
                ],
                'correct' => 'B',
                'explanation' => 'Always verify through trusted channels; do not click or reply.'
            ]
        ];
    }

    private function quizFallback(array $json): array
    {
        return [
            'questions' => [
                [
                    'text' => 'You get an urgent password reset email with a link. What is safest?',
                    'options' => [
                        'A'=>'Click the link',
                        'B'=>'Reply to confirm',
                        'C'=>'Use your password manager or type the site URL',
                        'D'=>'Forward to colleagues'
                    ],
                    'correct' => 'C',
                    'explanations' => [
                        'A'=>'Links can be spoofed.',
                        'B'=>'Engaging confirms your address.',
                        'C'=>'Out-of-band is safest.',
                        'D'=>'Unnecessary spread.',
                    ],
                ],
                [
                    'text' => 'A message asks for gift cards for a “CEO”. What is best?',
                    'options' => [
                        'A'=>'Buy them now',
                        'B'=>'Ask for the number via SMS',
                        'C'=>'Call the CEO using a known number',
                        'D'=>'Send codes to show speed'
                    ],
                    'correct' => 'C',
                    'explanations' => [
                        'A'=>'Classic BEC tactic.',
                        'B'=>'Attackers control SMS too.',
                        'C'=>'Trusted-channel verification.',
                        'D'=>'That’s what attackers want.',
                    ],
                ],
                [
                    'text' => 'Which URL is most likely legitimate?',
                    'options' => [
                        'A'=>'https://secure-update-bank.com',
                        'B'=>'http://mybank.com.login-check.net',
                        'C'=>'https://www.mybank.com',
                        'D'=>'https://update-your-account.info'
                    ],
                    'correct' => 'C',
                    'explanations' => [
                        'A'=>'Impersonation domain.',
                        'B'=>'Lookalike subdomain.',
                        'C'=>'Official domain pattern.',
                        'D'=>'Suspicious TLD.',
                    ],
                ],
            ]
        ];
    }

    /* ============================
       Rendering helpers
    ============================ */

    private function renderScenarioCard(array $j): string
    {
        $channel = strtolower((string)($j['channel'] ?? 'email'));
        if ($channel === 'sms' || $channel === 'chat') {
            return $this->renderChatScenario($j);
        }
        return $this->renderEmailScenario($j);
    }

    private function renderEmailScenario(array $j): string
    {
        $senderName  = e($j['sender_name']  ?? 'Support');
        $senderEmail = e($j['sender_email'] ?? 'support@example.com');
        $recipient   = e($j['recipient_name'] ?? ($this->user->name ?: 'You'));
        $subject     = e($j['subject'] ?? 'Notice');
        $snippet     = e($j['snippet'] ?? '');
        $paras       = $j['paragraphs'] ?? [];
        $tsIso       = $j['timestamp_iso'] ?? now()->toIso8601String();
        $tsText      = $this->humanTs($tsIso);
        $platform    = e($j['platform'] ?? 'Gmail');

        $avatar = $this->initials($senderName);

        // Allow minimal inline HTML (links/emphasis), build realistic link labels
        $body = '';
        if (is_array($paras) && $paras) {
            foreach ($paras as $p) {
                $clean = $this->allowInlineTags((string)$p);
                $clean = $this->realisticizeAnchors($clean, $senderEmail, $subject);
                $body .= '<p>'.$clean.'</p>';
            }
        }

        $q = $j['question'] ?? [];
        $mcq = $this->buildScenarioMcq($q);

        return <<<HTML
<div class="mail-card">
  <div class="mail-top">
    <div class="mail-avatar">{$avatar}</div>
    <div class="mail-headlines">
      <div class="mail-from"><span class="label">From:</span> {$senderName} &lt;{$senderEmail}&gt;</div>
      <div class="mail-to"><span class="label">To:</span> {$recipient}</div>
    </div>
    <div class="mail-meta">
      <div class="mail-platform">{$platform}</div>
      <div class="mail-ts">{$tsText}</div>
    </div>
  </div>
  <div class="mail-subject"><span class="label">Subject:</span> {$subject}</div>
  <div class="mail-snippet">{$snippet}</div>
  <div class="mail-body">{$body}</div>
  {$mcq}
</div>
HTML;
    }

    private function renderChatScenario(array $j): string
    {
        $platform = e($j['platform'] ?? 'Messages');
        $senderName  = e($j['sender_name']  ?? 'IT Support');
        $recipient   = e($j['recipient_name'] ?? ($this->user->name ?: 'You'));
        $tsIso       = $j['timestamp_iso'] ?? now()->toIso8601String();
        $tsText      = $this->humanTs($tsIso);

        $avatarSender = $this->initials($senderName);
        $avatarYou    = $this->initials($recipient);

        $msgs = $j['messages'] ?? [];
        if (!is_array($msgs) || !$msgs) {
            $text = $j['snippet'] ?? implode(" ", (array)($j['paragraphs'] ?? []));
            $msgs = [['from'=>'sender','text'=>$text]];
        }

        $thread = '';
        foreach ($msgs as $m) {
            $from = strtolower((string)($m['from'] ?? 'sender'));
            $text = e($m['text'] ?? '');
            $mine = $from === 'you';
            $bubbleCls = $mine ? 'bubble me' : 'bubble them';
            $avatar = $mine ? $avatarYou : $avatarSender;
            $thread .= '<div class="msg-row '.$bubbleCls.'"><div class="avatar">'.$avatar.'</div><div class="msg">'.$text.'</div></div>';
        }

        $q = $j['question'] ?? [];
        $mcq = $this->buildScenarioMcq($q);

        return <<<HTML
<div class="chat-card">
  <div class="chat-header">
    <div class="title">{$platform}</div>
    <div class="subtitle">{$senderName} • {$tsText}</div>
  </div>
  <div class="chat-thread">{$thread}</div>
  {$mcq}
</div>
HTML;
    }

    private function buildScenarioMcq(array $q): string
    {
        if (empty($q['text']) || empty($q['options'])) return '';
        $opts = $q['options'];
        $corr = strtoupper((string)($q['correct'] ?? ''));
        $ex   = e($q['explanation'] ?? '');

        $html  = '<div class="mcq" data-correct="'.$corr.'" data-explain-all="'.$ex.'">';
        $html .= '<p class="font-semibold mb-2">'.e($q['text']).'</p>';
        foreach (['A','B','C','D'] as $L) {
            if (!isset($opts[$L])) continue;
            $label = e($opts[$L]);
            $html .= '<button type="button" class="option-btn" data-letter="'.$L.'" data-explain="'.$ex.'">'.$L.'. '.$label.'</button>';
        }
        $html .= '</div>';
        return $html;
    }

    private function buildQuizHtmlFromJson(array $json): string
    {
        $qs = $json['questions'] ?? [];
        if (!is_array($qs) || !$qs) return ''; // hide step if empty

        $letters = ['A','B','C','D'];
        $html = '<div>';
        foreach ($qs as $q) {
            $text   = e($q['text'] ?? 'Question');
            $opts   = $q['options'] ?? [];
            $corr   = strtoupper($q['correct'] ?? '');
            $expl   = $q['explanations'] ?? [];

            $html .= '<div class="mcq" data-correct="'.$corr.'" style="margin:1rem 0">';
            $html .= '<p class="font-semibold mb-2">'.$text.'</p>';
            foreach ($letters as $L) {
                if (!isset($opts[$L])) continue;
                $label = e($opts[$L]);
                $why   = e($expl[$L] ?? '');
                $html .= '<button type="button" class="option-btn" data-letter="'.$L.'" data-explain="'.$why.'">'.$L.'. '.$label.'</button>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    private function extractHtml($response): string
    {
        if (!$response->successful()) {
            throw new \RuntimeException("LLM error: {$response->status()} {$response->body()}");
        }
        $content = $response->json('choices.0.message.content') ?? '';
        return Str::contains($content,'<div') ? $content : "<div><p>".e($content)."</p></div>";
    }

    // Clean up occasional markdown/[]/_ cues, convert to safe HTML
    private function softFormat(string $content): string
    {
        if ($content === '') return '';
        $clean = $content;
        $clean = preg_replace('/\[(.+?)\]/', '<span class="risk-cue">$1</span>', $clean);
        $clean = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $clean);
        // convert _italic_ safely (avoid inside tags)
        $clean = preg_replace('/(^|[^0-9A-Za-z])_([^_<>\r\n]{1,200})_(?![0-9A-Za-z])/', '$1<em>$2</em>', $clean);

        if (!Str::contains($clean, '<div')) {
            $clean = '<div>'.nl2br(e($clean)).'</div>';
        }
        return $clean;
    }

    // Guarantee each module MCQ has at least one data-correct="true"
    private function enforceMcqCorrect(string $html): string
    {
        // Look for a <div class='mcq'> block and ensure one button has data-correct="true"
        return preg_replace_callback('/<div[^>]*class=[\'"][^\'"]*mcq[^\'"]*[\'"][^>]*>.*?<\/div>/si', function($m){
            $block = $m[0];
            if (stripos($block, 'data-correct="true"') !== false) return $block;
            // set the FIRST option button as correct
            $block = preg_replace('/<button([^>]*)class="([^"]*option-btn[^"]*)"([^>]*)>/i', '<button$1class="$2"$3 data-correct="true">', $block, 1);
            return $block;
        }, $html);
    }

    // Allow only a, em, strong, br tags in paragraphs
    private function allowInlineTags(string $s): string
    {
        // remove all tags except the allowed set
        $s = strip_tags($s, '<a><em><strong><br>');
        return $s;
    }

    // Replace anchor text with a realistic brand-like domain while keeping original href
    private function realisticizeAnchors(string $html, string $senderEmail, string $subject): string
    {
        $domain = 'secure-notice.com';
        if (strpos($senderEmail, '@') !== false) {
            $d = substr(strrchr($senderEmail, '@'), 1);
            $brand = preg_replace('/\..*$/', '', $d);
            $domain = strtolower($brand).'-secure.com';
        } elseif ($subject) {
            $brand = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', $subject)[0] ?? 'secure'));
            $domain = $brand.'-secure.com';
        }

        return preg_replace_callback('/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>(.*?)<\/a>/i', function($m) use ($domain){
            $href = $m[2];
            $label = 'https://'.$domain.'/verify';
            return '<a '.$m[1].' class="ph-link">'.$label.'</a>';
        }, $html);
    }

    private function runtimeUiScript(): string
    {
        return <<<HTML
<script>
(function(){
  function closest(el, sel){ while (el && el.nodeType===1){ if (el.matches(sel)) return el; el = el.parentElement; } return null; }
  function msg(html, cls){ var p=document.createElement('p'); p.className='rem-msg '+(cls||''); p.innerHTML=html; return p; }
  function ensureCheckBtn(mcq){
    if (mcq.querySelector('.check-answer')) return;
    var b=document.createElement('button'); b.type='button'; b.className='check-answer'; b.textContent='Check answer';
    mcq.appendChild(b);
    b.addEventListener('click', function(){
      mcq.querySelectorAll('.rem-msg').forEach(m=>m.remove());
      var chosen = mcq.querySelector('.option-btn.selected');
      if (!chosen){ mcq.appendChild(msg('Please choose an option.','warn')); return; }
      var correctLetter = (mcq.getAttribute('data-correct')||'').toUpperCase();
      var isCorrect = (chosen.getAttribute('data-letter')||'').toUpperCase() === correctLetter
                      || chosen.getAttribute('data-correct') === 'true';
      var explain = chosen.getAttribute('data-explain') || mcq.getAttribute('data-explain-all') || '';
      mcq.appendChild(msg((isCorrect?'Correct ✓ ':'Not quite ✱ ')+explain, isCorrect?'ok':'warn'));
      // lock FIRST graded choice (for auto-save UIs) but still allow further exploration
      if (!mcq.dataset.firstChosen){
        mcq.dataset.firstChosen = (chosen.getAttribute('data-letter')||'').toUpperCase();
        mcq.dataset.firstResult = isCorrect ? '1' : '0';
      }
      mcq.dataset.chosen = (chosen.getAttribute('data-letter')||'').toUpperCase();
      mcq.dataset.result = isCorrect ? '1' : '0';
      mcq.dataset.checked = '1';
    });
  }

  document.addEventListener('click', function(e){
    var ob = e.target.closest('.mcq .option-btn');
    if (ob){
      e.preventDefault();
      var mcq = closest(ob,'.mcq');
      mcq.querySelectorAll('.option-btn').forEach(b=>b.classList.remove('selected'));
      ob.classList.add('selected'); // keep highlighted
      ensureCheckBtn(mcq);
      return;
    }
    if (e.target.matches('.mail-card a, .chat-card a')) e.preventDefault();
  }, false);
})();
</script>
<style>
/* Feedback text */
.rem-msg{margin:.5rem 0;font-size:.95rem}
.rem-msg.ok{color:#0a7a0a}
.rem-msg.warn{color:#b25c00}

/* MCQ vertical options */
.mcq .option-btn{
  display:block;width:100%;text-align:left;
  padding:.6rem .9rem;margin:.35rem 0;border:0;border-radius:.75rem;
  background:#1e3a8a;color:#fff;
}
.mcq .option-btn.selected{ background:#111827; }
.check-answer{ margin-top:.35rem; padding:.5rem .8rem; border-radius:.5rem; background:#2563eb; color:#fff; border:0; }

/* Email card */
.mail-card{ border:1px solid #e5e7eb; border-radius:14px; padding:1rem 1.25rem; background:#fff; }
.mail-top{ display:flex; align-items:center; gap:.75rem; }
.mail-avatar{ width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;
  background:#e0e7ff;color:#1e3a8a;font-weight:700; }
.mail-headlines{ flex:1; min-width:0; }
.mail-headlines .label{ font-weight:600; }
.mail-meta{ text-align:right; color:#6b7280; font-size:.85rem; }
.mail-subject{ margin:.5rem 0 .35rem 0; font-weight:600; }
.mail-subject .label{ font-weight:600; }
.mail-snippet{ color:#4b5563; margin-bottom:.65rem; }
.mail-body p{ margin:.5rem 0; }
.risk-cue{ background:#fff4ce; padding:0 .25rem; border-radius:.25rem; }
.ph-link{ text-decoration: underline; }

/* Chat card */
.chat-card{ border:1px solid #e5e7eb; border-radius:14px; background:#fff; }
.chat-header{ padding:.75rem 1rem; border-bottom:1px solid #e5e7eb; }
.chat-header .title{ font-weight:700; }
.chat-header .subtitle{ color:#6b7280; font-size:.9rem; }
.chat-thread{ padding: .75rem 1rem; }
.msg-row{ display:flex; align-items:flex-end; gap:.5rem; margin:.4rem 0; max-width: 90%; }
.msg-row .avatar{ width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;
  background:#e0e7ff;color:#1e3a8a;font-weight:700; font-size:.8rem;}
.msg-row .msg{ padding:.5rem .7rem; border-radius:12px; }
.msg-row.bubble.them{ flex-direction:row; }
.msg-row.bubble.them .msg{ background:#f3f4f6; }
.msg-row.bubble.me{ flex-direction:row-reverse; margin-left:auto; }
.msg-row.bubble.me .msg{ background:#dbeafe; }
</style>
HTML;
    }

    private function humanTs(string $iso): string
    {
        try { $dt = new \DateTime($iso); } catch (\Throwable $e) { $dt = now(); }
        $now = now();
        if ($dt->format('Y-m-d') === $now->format('Y-m-d')) return 'Today • '.$dt->format('H:i');
        return $dt->format('M j, Y • H:i');
    }

    private function initials(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '•';
        $parts = preg_split('/\s+/', $name);
        $ini = strtoupper(mb_substr($parts[0],0,1).(isset($parts[1])?mb_substr($parts[1],0,1):'')); // e.g., JD
        return e($ini);
    }

    /* ============================
       Selection + prompts + utils
    ============================ */

    private function selectModulesFromFlags(array $tm, array $weaknesses, int $max): array
    {
        $labelMap = [
            'tech' => ['label' => 'Technical Competence',         'priority' => 'High'],
            'beh'  => ['label' => 'Security Behaviors',           'priority' => 'High'],
            'psy'  => ['label' => 'Psychological Vulnerabilities','priority' => 'Medium'],
            'meta' => ['label' => 'Metacognitive Awareness',      'priority' => 'Medium'],
        ];
        $mods = [];
        foreach (['tech','beh','psy','meta'] as $k) {
            if (!empty($tm[$k]) && isset($labelMap[$k])) $mods[] = $labelMap[$k];
        }
        if (!$mods && $weaknesses) {
            foreach ($weaknesses as $w) {
                if ($w === 'Technical_Competence')              $mods[] = $labelMap['tech'];
                elseif ($w === 'Security_Behaviors')            $mods[] = $labelMap['beh'];
                elseif ($w === 'Psychological_Vulnerabilities') $mods[] = $labelMap['psy'];
                elseif ($w === 'Metacognitive_Awareness')       $mods[] = $labelMap['meta'];
            }
        }
        if (!$mods) $mods = [$labelMap['beh']]; // sensible default
        return array_slice($mods, 0, $max);
    }

    private function selectScenarioTypes(array $tm, array $derived, int $max): array
    {
        $sc = $this->toArray($tm['scenarios'] ?? null);
        if (!$sc) {
            $sc  = $this->toArray($derived['SCENARIO_TYPES_RISK'] ?? []);
            $pri = $this->defaults['scenario_priority'];
            usort($sc, fn($a,$b)=> array_search($a,$pri) <=> array_search($b,$pri));
        }
        return array_slice($sc, 0, $max);
    }

    private function loadPromptsOrDefaults(string $path): array
    {
        $system = "You are a veteran cybersecurity trainer. Keep content practical and phishing-focused. Use neutral HTML only.";
        $intro  = "Write a short, motivating introduction for [NAME] (role: [ROLE], sector: [SECTOR]). Risk score: [RiskScore]%. Focus on weaknesses: [WEAKNESSES]. Output a single <div> with 2–4 short sentences, include [NAME].";
        $module = "Create an anti-phishing module on [MODULE_LABEL] for [NAME] ([ROLE], [SECTOR]) priority [PRIORITY_LEVEL]. (1) Body 100–140 words; (2) one realistic phishing example 80–100 words (force <em>, forbid *, _ and [brackets]); (3) one defensive rule <=15 words; (4) 3-bullet checklist; (5) one MCQ rendered as <div class='mcq'> with four <button> options that include data-letter,data-correct,data-explain. Output VALID HTML in one <div>.";
        $mini   = "Generate a realistic [SCENARIO_TYPE] phishing scenario for [NAME] ([ROLE],[SECTOR]). Return ONLY JSON with keys: channel(email|sms|chat), platform, sender_name, sender_email, recipient_name, subject, snippet, paragraphs(array), messages(array of {from:'sender|you',text}), timestamp_iso, question {text, options{A..D}, correct, explanation}. Always address the recipient by their name in the opening.";
        $quiz   = "Create THREE short anti-phishing quiz questions tied to [MODULE_TITLES]. Each question must have options A–D, exactly ONE correct letter, and brief per-option explanations.";
        $con    = "Provide 3 key rules and a short action plan tied to [MODULE_TITLES]. Output a single <div>.";

        if (is_file(base_path($path))) {
            $raw = file_get_contents(base_path($path));
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $idx = [];
                foreach (($data['prompts'] ?? []) as $p) $idx[$p['id'] ?? ''] = $p;
                $system = Arr::get($idx['system_orchestrator'] ?? [], 'content', $system);
                $intro  = Arr::get($idx['intro_personalized'] ?? [], 'user_prompt', $intro);
                $module = Arr::get($idx['module_template'] ?? [], 'user_prompt', $module);
                $mini   = Arr::get($idx['mini_scenario'] ?? [], 'user_prompt', $mini);
                // If your prompt pack still says "exercises", we reuse its text as $quiz
                $quiz   = Arr::get($idx['interactive_exercises'] ?? [], 'user_prompt', $quiz);
                $con    = Arr::get($idx['conclusions'] ?? [], 'user_prompt', $con);
            }
        }
        return [$system, $intro, $module, $mini, $quiz, $con];
    }

    private function toArray($maybeJsonOrArray): array
    {
        if (is_array($maybeJsonOrArray)) return $maybeJsonOrArray;
        if (is_string($maybeJsonOrArray)) {
            $a = json_decode($maybeJsonOrArray, true);
            if (is_array($a)) return $a;
        }
        return [];
    }

    private function fill(string $tpl, array $pairs): string
    {
        return str_replace(array_keys($pairs), array_values($pairs), $tpl);
    }
}
