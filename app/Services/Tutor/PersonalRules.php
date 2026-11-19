<?php

namespace App\Services\Tutor;

use App\Models\RulesComment;
use App\Models\Training;
use App\Models\TrainingResponse;
use App\Models\TutorSession;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Module 5 personal rules of ONE user, interactive condition (cap4 4.3.5; prompt 5b):
 *
 *   write  --(2-3 rules "When ..., I ...")--> ONE tutor comment (or skipped) --> revise
 *   revise --(one revision, prefilled; may be unchanged)--> done (the "thanks" part is shown, training can complete)
 *
 * Storage: training_responses (module 5, item rules_v1 / rules_v2) and rules_comments (one row per user+training).
 * The stage is always derived from the DB of the authenticated user; the browser never sends an id.
 * Paid calls: at most one per user, guaranteed by the per-user lock + stage re-check under the lock, and by the
 * unique key of rules_comments as a last line of defence.
 */
class PersonalRules
{
    public const MODULE = 5;

    public const V1 = 'rules_v1';

    public const V2 = 'rules_v2';

    public const STAGE_WRITE = 'write';

    public const STAGE_REVISE = 'revise';

    public const STAGE_DONE = 'done';

    /** Rules shown as fields; at least MIN_RULES must be filled in. */
    public const MAX_RULES = 3;

    public const MIN_RULES = 2;

    public const MIN_WORDS = 4;

    private Materials $materials;

    public function __construct(private readonly User $user, ?Materials $materials = null)
    {
        $this->materials = $materials ?? new Materials();
    }

    /** The user's training; Module 5 rules exist only for the interactive condition. */
    public function training(): Training
    {
        $training = $this->user->training;
        abort_if($training === null, 409, 'No training.');
        abort_unless($training->isInteractive(), 403);

        return $training;
    }

    /** Whether Module 5 is complete for this training (revised rules saved): gate of TrainingController. */
    public static function completed(Training $training): bool
    {
        return TrainingResponse::query()
            ->where('user_id', $training->user_id)
            ->where('training_id', $training->id)
            ->where('module', self::MODULE)
            ->where('item_key', self::V2)
            ->exists();
    }

    /** Whether the user has already sent the first version of the rules (training page resume). */
    public static function started(Training $training): bool
    {
        return TrainingResponse::query()
            ->where('user_id', $training->user_id)
            ->where('training_id', $training->id)
            ->where('module', self::MODULE)
            ->where('item_key', self::V1)
            ->exists();
    }

    public function stage(): string
    {
        $training = $this->training();
        if (self::completed($training)) {
            return self::STAGE_DONE;
        }

        return self::started($training) ? self::STAGE_REVISE : self::STAGE_WRITE;
    }

    /** @return list<string> rules saved under `$itemKey` (empty when not saved) */
    public function savedRules(string $itemKey): array
    {
        $response = $this->response($itemKey);
        $rules = $response?->response_json['rules'] ?? [];

        return is_array($rules) ? array_values(array_map('strval', $rules)) : [];
    }

    private function response(string $itemKey): ?TrainingResponse
    {
        return TrainingResponse::query()
            ->where('user_id', $this->user->id)
            ->where('training_id', $this->training()->id)
            ->where('module', self::MODULE)
            ->where('item_key', $itemKey)
            ->first();
    }

    public function comment(): ?RulesComment
    {
        return RulesComment::query()
            ->where('user_id', $this->user->id)
            ->where('training_id', $this->training()->id)
            ->first();
    }

    /**
     * Trims every rule, folds whitespace (newlines included) into single spaces, drops control characters and
     * empty fields, keeps at most MAX_RULES.
     *
     * @return list<string>
     */
    public static function normalise(array $rules): array
    {
        $clean = [];
        foreach (array_slice(array_values($rules), 0, self::MAX_RULES) as $rule) {
            $text = is_string($rule) ? $rule : '';
            $text = preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $text) ?? '';
            $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
            if ($text !== '') {
                $clean[] = $text;
            }
        }

        return $clean;
    }

    /** Input gate: an input that does not pass never costs a model call nor a rate-limit slot. */
    public static function checkInput(array $rules): ?string
    {
        $max = self::maxChars();
        if (count($rules) < self::MIN_RULES) {
            return 'Please write at least '.self::MIN_RULES.' rules.';
        }
        if (mb_strlen(implode('', $rules)) > $max) {
            return "Please keep your rules under {$max} characters in total.";
        }
        foreach ($rules as $rule) {
            if (Text::wordCount($rule) < self::MIN_WORDS) {
                return 'Write each rule as a full sentence, for example "When ..., I ...".';
            }
        }

        return null;
    }

    public static function maxChars(): int
    {
        return (int) config('tutor.rules_max_chars', 1000);
    }

    private function rateLimitKey(): string
    {
        return 'rules-submit:'.$this->user->id;
    }

    /** Clears the per-user rate limiter (tests, admin). */
    public function resetRateLimit(): void
    {
        RateLimiter::clear($this->rateLimitKey());
    }

    /** First version of the rules: saves rules_v1 and asks the ONE tutor comment (or records it as skipped). */
    public function submitRules(array $rules): ChatSubmitResult
    {
        return $this->locked(fn () => $this->submitRulesLocked(self::normalise($rules)));
    }

    /** The one revision: saves rules_v2 (identical rules allowed); no second comment. */
    public function submitRevision(array $rules): ChatSubmitResult
    {
        return $this->locked(fn () => $this->submitRevisionLocked(self::normalise($rules)));
    }

    private function locked(callable $fn): ChatSubmitResult
    {
        $lock = Cache::lock('rules-lock:'.$this->user->id, (int) config('tutor.lock_seconds', 120));
        if (! $lock->get()) {
            return new ChatSubmitResult(ChatSubmitResult::BUSY, 'Your rules are still being processed. Please wait a moment.');
        }
        try {
            return $fn();
        } finally {
            $lock->release();
        }
    }

    private function gate(array $rules, string $expectedStage): ?ChatSubmitResult
    {
        if (! $this->tutorCompleted()) {
            return new ChatSubmitResult(ChatSubmitResult::NOT_ACCEPTING, 'Please complete the Module 4 exercises first.');
        }
        if ($this->stage() !== $expectedStage) {
            return new ChatSubmitResult(ChatSubmitResult::NOT_ACCEPTING, 'Your rules are already saved.');
        }
        $error = self::checkInput($rules);
        if ($error !== null) {
            return new ChatSubmitResult(ChatSubmitResult::REJECTED, $error);
        }
        $limit = max(1, (int) config('tutor.rules_rate_limit_per_minute', 6));
        if (RateLimiter::tooManyAttempts($this->rateLimitKey(), $limit)) {
            $seconds = RateLimiter::availableIn($this->rateLimitKey());

            return new ChatSubmitResult(ChatSubmitResult::RATE_LIMITED, "Please wait {$seconds} seconds before sending again.");
        }
        RateLimiter::hit($this->rateLimitKey(), 60);

        return null;
    }

    private function submitRulesLocked(array $rules): ChatSubmitResult
    {
        if (($refused = $this->gate($rules, self::STAGE_WRITE)) !== null) {
            return $refused;
        }
        $training = $this->training();
        $missed = $this->missedClues();
        $missedIds = RulesCommentPrompt::missedIds($missed);
        $prompt = RulesCommentPrompt::build(
            RulesCommentPrompt::loadTemplate($this->materials),
            $missed,
            $this->module3Guidelines(),
            [],
            $rules,
        );
        $result = $this->requestComment($prompt, $missedIds);

        DB::transaction(function () use ($training, $rules, $result, $missedIds) {
            TrainingResponse::record($this->user->id, $training->id, self::MODULE, self::V1, implode("\n", $rules), [
                'rules' => $rules,
                'comment_status' => $result['status'],
            ]);
            RulesComment::create(array_merge($result['row'], [
                'user_id' => $this->user->id,
                'training_id' => $training->id,
                'status' => $result['status'],
                'missed_clue_ids' => $missedIds,
            ]));
        });
        if ($result['status'] === RulesComment::SKIPPED) {
            // Reserve intervention (cap4 4.4.5): no comment, the revision is still allowed.
            Log::warning('rules_comment.skipped', ['user_id' => $this->user->id, 'training_id' => $training->id, 'errors' => $result['row']['validator_errors']]);
        }

        return new ChatSubmitResult(ChatSubmitResult::OK);
    }

    private function submitRevisionLocked(array $rules): ChatSubmitResult
    {
        if (($refused = $this->gate($rules, self::STAGE_REVISE)) !== null) {
            return $refused;
        }
        TrainingResponse::record($this->user->id, $this->training()->id, self::MODULE, self::V2, implode("\n", $rules), [
            'rules' => $rules,
            'unchanged' => $rules === $this->savedRules(self::V1),
        ]);

        return new ChatSubmitResult(ChatSubmitResult::OK);
    }

    /**
     * Call -> validate; on error, timeout or invalid output retry ONCE; then skip (no comment).
     *
     * @return array{status: string, row: array<string, mixed>}
     */
    private function requestComment(BuiltPrompt $prompt, array $missedIds): array
    {
        $schema = RulesCommentPrompt::loadSchema($this->materials);
        $metered = new MeteredLlmClient(app('tutor.llm'));
        $engine = new Tutor($metered, $schema, $prompt->system, (int) config('tutor.timeout_ms', Tutor::DEFAULT_TIMEOUT_MS));
        $start = hrtime(true);
        $attempts = [];
        $errors = [];
        $output = null;
        for ($attempt = 1; $attempt <= 2 && $output === null; $attempt++) {
            try {
                $raw = $engine->callWithTimeout($prompt->system, $prompt->user);
            } catch (Exception $e) {
                $failure = $e instanceof TutorTimeoutException ? FailureType::Timeout : FailureType::ProviderError;
                $attempts[] = ['attempt' => $attempt, 'ok' => false, 'failure' => $failure->value, 'error' => $e->getMessage(), 'violations' => [], 'raw' => null];
                $errors[] = ['attempt' => $attempt, 'rule' => null, 'code' => $failure->value, 'detail' => $e->getMessage()];

                continue;
            }
            $check = RulesCommentValidator::validate($raw, $schema, $missedIds);
            $attempts[] = [
                'attempt' => $attempt,
                'ok' => $check['ok'],
                'failure' => $check['ok'] ? null : FailureType::InvalidOutput->value,
                'error' => null,
                'violations' => array_map(fn (Violation $v) => $v->key(), $check['violations']),
                'raw' => $raw,
            ];
            foreach ($check['violations'] as $v) {
                $errors[] = ['attempt' => $attempt, 'rule' => $v->rule, 'code' => $v->code, 'detail' => $v->detail];
            }
            if ($check['ok']) {
                $output = $check['output'];
            }
        }
        $latency = (int) round((hrtime(true) - $start) / 1e6);
        $calls = $metered->take();
        $sum = function (string $key) use ($calls): ?int {
            $values = array_filter(array_column($calls, $key), fn ($v) => $v !== null);

            return $values === [] ? null : (int) array_sum($values);
        };
        $models = array_values(array_filter(array_column($calls, 'model')));
        $row = [
            'message' => $output['message'] ?? null,
            'covered_clue_ids' => $output['covered_clue_ids'] ?? null,
            'focus_clue_id' => $output['focus_clue_id'] ?? null,
            'generic_rule' => $output['generic_rule'] ?? null,
            'raw_output' => $attempts,
            'validator_errors' => $errors === [] ? null : $errors,
            'model' => $models === [] ? null : end($models),
            'input_tokens' => $sum('input_tokens'),
            'output_tokens' => $sum('output_tokens'),
            'latency_ms' => $latency,
        ];

        return ['status' => $output !== null ? RulesComment::SHOWN : RulesComment::SKIPPED, 'row' => $row];
    }

    private function tutorSession(): ?TutorSession
    {
        return TutorSession::query()
            ->where('user_id', $this->user->id)
            ->where('training_id', $this->training()->id)
            ->first();
    }

    private function tutorCompleted(): bool
    {
        return $this->tutorSession()?->isCompleted() === true;
    }

    /** {{missed_clues}} of this user: from their own Module 4 session and the bank cards. */
    public function missedClues(): array
    {
        $closed = $this->tutorSession()?->closed_states ?? [];

        return RulesCommentPrompt::missedClues($this->materials->loadEmailBank(), is_array($closed) ? $closed : []);
    }

    /** interactive_data.defense_guidelines, as passed to the M4 tutor. */
    private function module3Guidelines(): array
    {
        $guidelines = $this->training()->interactive_data['defense_guidelines'] ?? [];

        return is_array($guidelines) ? array_values($guidelines) : [];
    }
}
