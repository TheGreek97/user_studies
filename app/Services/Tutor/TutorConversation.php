<?php

namespace App\Services\Tutor;

use App\Models\TrainingResponse;
use App\Models\TutorSession;
use App\Models\TutorTurn;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Module 4 conversation of ONE user over the whole email sequence (used by the TutorChat Livewire component).
 *
 * The state lives in the DB (tutor_sessions): sequence draw, position and DialogSession::toArray() of the current
 * email, so a page reload resumes where the user left. Every tutor turn is logged in tutor_turns, the user's
 * choice and reasoning per email in training_responses (module 4, item = email id).
 *
 * The session is always resolved from the authenticated user: the browser never sends a session id.
 */
class TutorConversation
{
    public const MODULE = 4;

    private Materials $materials;

    /** @var array{_meta: array<string, mixed>, emails: list<array<string, mixed>>} */
    private array $bank;

    private ?TutorSession $session = null;

    public function __construct(private readonly User $user, ?Materials $materials = null)
    {
        $this->materials = $materials ?? new Materials();
        $this->bank = $this->materials->loadEmailBank();
    }

    public function meta(): array
    {
        return $this->bank['_meta'];
    }

    private function trainingId(): ?int
    {
        return $this->user->training?->id;
    }

    /**
     * Current session of the user, created (with a fresh sequence draw) on first access.
     * A user without a training has no Module 4 to run: 409, no session is created (training_id is NOT NULL).
     */
    public function session(bool $fresh = false): TutorSession
    {
        if ($this->session !== null && ! $fresh) {
            return $this->session;
        }
        $trainingId = $this->trainingId();
        abort_if($trainingId === null, 409, 'No training to attach the tutor to.');
        $session = TutorSession::query()
            ->where('user_id', $this->user->id)
            ->where('training_id', $trainingId)
            ->first();
        if ($session === null) {
            $draw = $this->trainingSequence() ?? DialogSession::drawSequence($this->meta());
            $session = TutorSession::firstOrCreate(
                ['user_id' => $this->user->id, 'training_id' => $trainingId],
                [
                    'seed' => $draw['seed'],
                    'sequence' => $draw['sequence'],
                    'legitimate_position' => $draw['legitimate_position'],
                    'position' => 1,
                    'closed_states' => [],
                    'missed_clues' => [],
                ],
            );
        }

        return $this->session = $session;
    }

    /** trainings.interactive_data written by the generation job (ProcessTraining, "interactive" condition). */
    private function interactiveData(): array
    {
        $data = $this->user->training?->interactive_data;

        return is_array($data) ? $data : [];
    }

    /**
     * Sequence fixed at generation time (`interactive_data.m4.sequence`), if present and made only of bank ids.
     *
     * @return array{seed: ?int, sequence: list<string>, legitimate_position: int}|null
     */
    private function trainingSequence(): ?array
    {
        $m4 = $this->interactiveData()['m4'] ?? null;
        $sequence = is_array($m4) && is_array($m4['sequence'] ?? null) ? array_values($m4['sequence']) : [];
        $known = array_column($this->bank['emails'], 'id');
        if ($sequence === [] || array_diff($sequence, $known) !== [] || ! array_key_exists((string) count($sequence), $this->meta()['help_level_by_position'] ?? [])) {
            return null;
        }
        $legitimate = 0;
        foreach ($sequence as $i => $id) {
            if (empty(Materials::findCard($this->bank, $id)['is_phishing'])) {
                $legitimate = $i + 1;
                break;
            }
        }

        return ['seed' => isset($m4['seed']) ? (int) $m4['seed'] : null, 'sequence' => $sequence, 'legitimate_position' => $legitimate];
    }

    /**
     * Module 3 guidelines of the generated training (`interactive_data.defense_guidelines`, items {id, text,
     * email_part}), passed as they are to the tutor prompt; empty for trainings without them.
     *
     * @return list<array{id: string, text: string, email_part: string}>
     */
    private function module3Guidelines(): array
    {
        $guidelines = $this->interactiveData()['defense_guidelines'] ?? [];

        return is_array($guidelines) ? array_values($guidelines) : [];
    }

    /** Value of {{first_name}} on screen (same source as Training::renderSection); never sent to the LLM. */
    public function displayName(): string
    {
        $name = trim((string) $this->user->name);

        return $name !== '' ? $name : 'there';
    }

    public function total(): int
    {
        return count($this->session()->sequence);
    }

    public function card(int $position): array
    {
        return Materials::findCard($this->bank, $this->session()->sequence[$position - 1]);
    }

    /** DialogSession of the current email: restored from the DB, or new (and saved) when the email starts. */
    public function dialog(): DialogSession
    {
        $session = $this->session();
        if ($session->state !== null) {
            $card = Materials::findCard($this->bank, $session->state['card_id']);

            return DialogSession::fromArray($session->state, $card);
        }
        $position = $session->position;
        $dialog = new DialogSession(
            card: $this->card($position),
            position: $position,
            levelMap: $this->meta()['help_level_by_position'] ?? null,
            config: ['exerciseTotal' => $this->total()],
            module3Guidelines: $this->module3Guidelines(),
            previousMissedClues: array_values($session->missed_clues ?? []),
        );
        $session->state = $dialog->toArray();
        $session->save();

        return $dialog;
    }

    /**
     * Browser-safe view of the current email (only `_meta.browser_safe_fields`, first name filled in).
     * The card id is never projected even if listed: ids encode the answer (m4-p* phishing, m4-l* legitimate).
     */
    public function browserEmail(DialogSession $dialog): array
    {
        $fields = array_values(array_diff($this->meta()['browser_safe_fields'], ['id']));
        $email = BrowserEmail::project($dialog->card, $fields);

        return BrowserEmail::personalise($email, $this->displayName());
    }

    private function rateLimitKey(): string
    {
        return 'tutor-turn:'.$this->user->id;
    }

    /**
     * One user message: input gate, rate limit, dialog turn and/or closing turn, persistence and logs.
     */
    public function submit(string $choice, string $text): ChatSubmitResult
    {
        $max = (int) config('tutor.max_message_chars', 1000);
        if (mb_strlen($text) > $max) {
            return new ChatSubmitResult(ChatSubmitResult::REJECTED, "Please keep your message under {$max} characters.");
        }

        $lock = Cache::lock('tutor-chat:'.$this->user->id, (int) config('tutor.lock_seconds', 120));
        if (! $lock->get()) {
            return new ChatSubmitResult(ChatSubmitResult::BUSY, 'Your previous message is still being processed. Please wait a moment.');
        }
        try {
            return $this->submitLocked($choice, $text);
        } finally {
            $lock->release();
        }
    }

    private function submitLocked(string $choice, string $text): ChatSubmitResult
    {
        $session = $this->session(fresh: true);
        if ($session->isCompleted()) {
            return new ChatSubmitResult(ChatSubmitResult::NOT_ACCEPTING, 'This activity is already complete.');
        }
        $dialog = $this->dialog();
        if ($dialog->phase !== Phase::AwaitingExplanation && $dialog->phase !== Phase::AwaitingReply) {
            return new ChatSubmitResult(ChatSubmitResult::NOT_ACCEPTING, 'This email is finished. Continue with the next one.');
        }

        // Input gate first: an input that does not pass never costs a model call nor a rate-limit slot.
        $classification = Classification::tryFrom($choice);
        $check = DialogSession::checkUserInput($text, $classification, $dialog->phase === Phase::AwaitingExplanation, $dialog->config);
        if (! $check->ok) {
            return new ChatSubmitResult(ChatSubmitResult::REJECTED, $check->message);
        }

        $limit = max(1, (int) config('tutor.rate_limit_per_minute', 20));
        if (RateLimiter::tooManyAttempts($this->rateLimitKey(), $limit)) {
            $seconds = RateLimiter::availableIn($this->rateLimitKey());

            return new ChatSubmitResult(
                ChatSubmitResult::RATE_LIMITED,
                "You are sending messages very quickly. Take a moment to look at the email again and try in {$seconds} seconds.",
            );
        }
        RateLimiter::hit($this->rateLimitKey(), 60);

        $metered = new MeteredLlmClient(app('tutor.llm'));
        $engine = Tutor::fromMaterials($metered, $this->materials, (int) config('tutor.timeout_ms', Tutor::DEFAULT_TIMEOUT_MS));

        $next = $dialog->submitUserReply($text, $classification);
        $userMessage = $text;
        if ($next === TurnType::Dialog) {
            $start = hrtime(true);
            $result = $engine->requestTurn(
                $dialog->promptContext(TurnType::Dialog),
                $dialog->validationContext(TurnType::Dialog),
                fn () => $dialog->fallbackDialogOutput(),
            );
            $latency = self::since($start);
            $applied = $dialog->applyDialogResult($result);
            $this->logTurn($dialog, TurnType::Dialog, $result, $applied['shown'], $userMessage, $classification, $metered->take(), $latency);
            $userMessage = null; // a closing turn chained in the same step is not a new user message
        }
        if ($dialog->phase === Phase::Closing) {
            $start = hrtime(true);
            $result = $engine->requestTurn(
                $dialog->promptContext(TurnType::Closing),
                $dialog->validationContext(TurnType::Closing),
                fn () => $dialog->fallbackClosingOutput(),
            );
            $latency = self::since($start);
            $view = $dialog->applyClosingResult($result);
            $this->logTurn($dialog, TurnType::Closing, $result, $view->message, $userMessage, $userMessage !== null ? $classification : null, $metered->take(), $latency);
        }

        $session->state = $dialog->toArray();
        if ($dialog->phase === Phase::Closed) {
            $session->closed_states = array_merge($session->closed_states ?? [], [$dialog->toArray()]);
            $session->missed_clues = array_values(array_unique(array_merge($session->missed_clues ?? [], $dialog->missedMainLabels())));
            if ($session->position >= $this->total()) {
                $session->completed_at = now();
            }
        }
        $session->save();
        $this->recordResponse($dialog);

        return new ChatSubmitResult(ChatSubmitResult::OK);
    }

    /** Moves to the next email once the current one is closed. Returns false when there is nothing to advance. */
    public function advance(): bool
    {
        $session = $this->session(fresh: true);
        if ($session->isCompleted() || $session->state === null || $session->position >= $this->total()) {
            return false;
        }
        if (Phase::from($session->state['phase']) !== Phase::Closed) {
            return false;
        }
        $session->position += 1;
        $session->state = null;
        $session->save();

        return true;
    }

    /** Clears the per-user rate limiter (tests, admin). */
    public function resetRateLimit(): void
    {
        RateLimiter::clear($this->rateLimitKey());
    }

    /** Previous user messages and tutor messages of the current email, for the chat transcript. */
    public static function transcript(DialogSession $dialog): array
    {
        return array_map(fn (DialogTurn $t) => $t->toArray(), $dialog->history);
    }

    private function recordResponse(DialogSession $dialog): void
    {
        $replies = [];
        foreach ($dialog->history as $turn) {
            if ($turn->author === 'user') {
                $replies[] = ['classification' => $turn->classification?->value, 'text' => $turn->text];
            }
        }
        TrainingResponse::record(
            $this->user->id,
            $this->trainingId(),
            self::MODULE,
            $dialog->card['id'],
            $replies[0]['text'] ?? null,
            [
                'position' => $dialog->position,
                'help_level' => $dialog->helpLevel->value,
                'first_classification' => $dialog->firstClassification?->value,
                'final_classification' => $dialog->classification?->value,
                'replies' => $replies,
                // Server-accepted recognized ids (ClueEvidence), not the model's claim (see tutor_turns).
                'recognized' => $dialog->recognized,
                'closed' => $dialog->phase === Phase::Closed,
                'close_reason' => $dialog->closeReason?->value,
            ],
        );
    }

    /** @param list<array{model: ?string, input_tokens: ?int, output_tokens: ?int, latency_ms: int, error: ?string}> $calls */
    private function logTurn(
        DialogSession $dialog,
        TurnType $type,
        TurnResult $result,
        ?string $shown,
        ?string $userMessage,
        ?Classification $choice,
        array $calls,
        int $latencyMs,
    ): void {
        $session = $this->session();
        $attempts = array_map(fn (AttemptRecord $a) => [
            'attempt' => $a->attempt,
            'ok' => $a->ok,
            'failure' => $a->failure?->value,
            'error' => $a->error,
            'violations' => array_map(fn (Violation $v) => $v->key(), $a->violations),
            'raw' => $a->raw,
        ], $result->attempts);
        $errors = [];
        foreach ($result->attempts as $a) {
            foreach ($a->violations as $v) {
                $errors[] = ['attempt' => $a->attempt, 'rule' => $v->rule, 'code' => $v->code, 'detail' => $v->detail];
            }
        }
        $sum = function (string $key) use ($calls): ?int {
            $values = array_filter(array_column($calls, $key), fn ($v) => $v !== null);

            return $values === [] ? null : (int) array_sum($values);
        };
        $models = array_values(array_filter(array_column($calls, 'model')));

        TutorTurn::create([
            'user_id' => $this->user->id,
            'training_id' => $session->training_id,
            'tutor_session_id' => $session->id,
            'email_id' => $dialog->card['id'],
            'position' => $dialog->position,
            'help_level' => $dialog->helpLevel->value,
            'turn_index' => TutorTurn::where('tutor_session_id', $session->id)->where('email_id', $dialog->card['id'])->count() + 1,
            'turn_type' => $type->value,
            'state' => $dialog->phase->value,
            'user_message' => $userMessage,
            'user_choice' => $choice?->value,
            'tutor_message' => $shown,
            'raw_output' => $attempts,
            'validated_output' => $result->output->toArray(), // the model's raw claim of recognized ids
            'accepted_clue_ids' => $dialog->lastClueCheck['accepted'] ?? [],
            'used_fallback' => $result->source === 'fallback',
            'validator_errors' => $errors === [] ? null : $errors,
            'model' => $models === [] ? null : end($models),
            'input_tokens' => $sum('input_tokens'),
            'output_tokens' => $sum('output_tokens'),
            'latency_ms' => $latencyMs,
        ]);
    }

    private static function since(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1e6);
    }
}
