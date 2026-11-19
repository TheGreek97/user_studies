<?php

namespace App\Services\Tutor;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;

/**
 * Dialog state machine for ONE email of Module 4 (cap4 par. 4.4.2-4.4.5, Tabella 13; port of `src/state-machine.ts`).
 *
 *   awaiting_explanation --(classification + explanation >= 4 words)--> [dialog turn] --> awaiting_reply
 *   awaiting_reply --(reply)--> reply_count == max ? closing : [dialog turn]
 *   after a dialog turn: reply_count >= min AND (all main items recognized OR 2 consecutive minimal replies) ? closing : awaiting_reply
 *   closing --[closing turn: verdict from the card, tutor feedback, annotated email]--> closed
 *
 * Help level: fixed by the position of the email (1 high, 2 medium, 3-4 low). The "don't know" exception is one
 * turn only: the prompt keeps the level and the model uses the "don't know" column of the same row; the
 * deterministic fallback uses the hints of the level above (cap4 4.4.3: "per un solo turno sale di un livello").
 *
 * Persistence between HTTP requests: `toArray()` / `fromArray()` (JSON-plain, card referenced by id only,
 * so the solution card is never stored with the dialog state).
 */
class DialogSession
{
    public const DIALOG_CONFIG = [
        /** Minimum user replies before closing, first explanation included (cap4 4.4.4). */
        'minUserReplies' => 2,
        /** Maximum user replies (cap4 4.4.4, 4.6). */
        'maxUserReplies' => 4,
        /** Minimum words of the first explanation (cap4 4.6). */
        'minFirstExplanationWords' => 4,
        /** Consecutive minimal replies that close the dialog (cap4 4.4.4). */
        'minimalRepliesToClose' => 2,
        'exerciseTotal' => 4,
    ];

    private const DEFAULT_LEVELS = ['1' => 'high', '2' => 'medium', '3' => 'low', '4' => 'low'];

    private const STATE_VERSION = 1;

    public readonly HelpLevel $helpLevel;

    /** @var array{minUserReplies: int, maxUserReplies: int, minFirstExplanationWords: int, minimalRepliesToClose: int, exerciseTotal: int} */
    public readonly array $config;

    /** @var list<DialogTurn> */
    public array $history = [];

    /** @var list<LogEvent> */
    public array $events = [];

    /**
     * @var list<string> ids recognized so far (insertion-ordered set). Only ids accepted by ClueEvidence: the
     * model's raw `recognized_clue_ids` are a claim that must be supported by the user's own messages.
     */
    public array $recognized = [];

    /**
     * Outcome of the plausibility check of the last applied tutor turn (not persisted: read right after
     * applyDialogResult / applyClosingResult, e.g. by TutorConversation for tutor_turns.accepted_clue_ids).
     *
     * @var array{claimed: list<string>, accepted: list<string>, rejected: list<string>}|null
     */
    public ?array $lastClueCheck = null;

    public Phase $phase = Phase::AwaitingExplanation;

    public int $userReplyCount = 0;

    public ?Classification $classification = null;

    public ?Classification $firstClassification = null;

    public ?CloseReason $closeReason = null;

    public ?ClosingView $closingView = null;

    private int $consecutiveMinimal = 0;

    private int $prevConsecutiveMinimal = 0;

    private bool $lastMinimalHeuristic = false;

    private bool $lastDontKnowHeuristic = false;

    /** @var list<string> */
    private array $usedHints = [];

    /** @var Closure(): DateTimeInterface */
    private Closure $clock;

    /**
     * @param array<string, mixed> $card email card of the bank
     * @param int $position 1-based position in the sequence of Module 4
     * @param array<string|int, string>|null $levelMap `_meta.help_level_by_position` (default 1 high, 2 medium, 3-4 low)
     * @param array<string, int>|null $config overrides of DIALOG_CONFIG
     * @param list<array{id: string, text: string, email_part: string}> $module3Guidelines
     * @param list<string> $styleCard
     * @param list<string> $previousMissedClues
     * @param (callable(): DateTimeInterface)|null $clock
     */
    public function __construct(
        public readonly array $card,
        public readonly int $position,
        ?array $levelMap = null,
        ?array $config = null,
        private readonly array $module3Guidelines = [],
        private readonly array $styleCard = [],
        private readonly array $previousMissedClues = [],
        ?callable $clock = null,
    ) {
        $this->helpLevel = self::helpLevelForPosition($position, $levelMap);
        $this->config = array_merge(self::DIALOG_CONFIG, $config ?? []);
        $this->clock = $clock !== null ? Closure::fromCallable($clock) : fn () => new DateTimeImmutable('now');
    }

    // ---- static helpers ---------------------------------------------------------------------------------------

    public static function helpLevelForPosition(int $position, ?array $map = null): HelpLevel
    {
        $map ??= self::DEFAULT_LEVELS;
        $level = $map[(string) $position] ?? null;
        if ($level === null) {
            throw new InvalidArgumentException("No help level for position {$position}");
        }

        return $level instanceof HelpLevel ? $level : HelpLevel::from($level);
    }

    public static function raiseLevel(HelpLevel $level): HelpLevel
    {
        return $level === HelpLevel::Low ? HelpLevel::Medium : HelpLevel::High;
    }

    /**
     * Sequence of Module 4 (cap4 4.3.4): phishing emails in the default order, legitimate email inserted at a random
     * position between 2 and 4. `rng` (floats in [0, 1)) is injectable for tests and for logging the seed.
     *
     * @param (callable(): float)|null $rng
     * @return list<string>
     */
    public static function composeSequence(array $meta, ?callable $rng = null, ?string $legitimateId = null): array
    {
        $rng ??= fn () => mt_rand() / (mt_getrandmax() + 1);
        $seq = array_values($meta['default_sequence']);
        $position = 2 + (int) floor($rng() * 3); // 2, 3 or 4
        array_splice($seq, $position - 1, 0, [$legitimateId ?? $meta['legitimate_main']]);

        return $seq;
    }

    /**
     * Deterministic RNG (mulberry32) for a loggable, reproducible draw: same seed -> same sequence.
     *
     * @return Closure(): float
     */
    public static function seededRng(int $seed): Closure
    {
        $state = $seed & 0xFFFFFFFF;

        return function () use (&$state): float {
            $state = ($state + 0x6D2B79F5) & 0xFFFFFFFF;
            $t = $state;
            $t = self::imul($t ^ ($t >> 15), $t | 1);
            $t ^= ($t + self::imul($t ^ ($t >> 7), $t | 61)) & 0xFFFFFFFF;

            return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
        };
    }

    /** 32-bit unsigned multiplication (JS Math.imul without the sign). */
    private static function imul(int $a, int $b): int
    {
        $lo = (($a & 0xFFFF) * $b) & 0xFFFFFFFF;
        $hi = ((($a >> 16) & 0xFFFF) * $b) & 0xFFFF;

        return ($lo + ($hi << 16)) & 0xFFFFFFFF;
    }

    /**
     * Sequence draw with its seed, ready for the log: `['seed' => int, 'sequence' => list<string>, 'legitimate_position' => int]`.
     */
    public static function drawSequence(array $meta, ?int $seed = null): array
    {
        $seed ??= random_int(0, 0x7FFFFFFF);
        $legit = $meta['legitimate_main'];
        $sequence = self::composeSequence($meta, self::seededRng($seed), $legit);

        return ['seed' => $seed, 'sequence' => $sequence, 'legitimate_position' => array_search($legit, $sequence, true) + 1];
    }

    /** Input gate (cap4 4.3.4, 4.4.5; tests T02, T03): when it fails the send button stays disabled, no model call. */
    public static function checkUserInput(string $text, ?Classification $classification, bool $isFirstExplanation, ?array $config = null): InputCheck
    {
        $config = array_merge(self::DIALOG_CONFIG, $config ?? []);
        if ($classification === null) {
            return InputCheck::fail('missing_classification', 'Choose "Phishing" or "Legitimate".');
        }
        if (Text::trim($text) === '') {
            return InputCheck::fail('empty', 'Write at least one reason, even if you are unsure.');
        }
        if ($isFirstExplanation && Text::wordCount($text) < $config['minFirstExplanationWords']) {
            return InputCheck::fail('too_short', 'Write at least one reason, even if you are unsure.');
        }

        return InputCheck::pass();
    }

    private static function toClassification(Classification|string|null $c): ?Classification
    {
        return is_string($c) ? Classification::tryFrom($c) : $c;
    }

    // ---- state ------------------------------------------------------------------------------------------------

    public function consecutiveMinimalReplies(): int
    {
        return $this->consecutiveMinimal;
    }

    public function allMainRecognized(): bool
    {
        foreach (Materials::mainIds($this->card) as $id) {
            if (! in_array($id, $this->recognized, true)) {
                return false;
            }
        }

        return true;
    }

    private function log(string $type, ?array $detail = null): void
    {
        $at = DateTimeImmutable::createFromInterface(($this->clock)())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z');
        $this->events[] = new LogEvent($type, $at, $this->userReplyCount, $this->helpLevel, $detail);
    }

    /** @return list<LogEvent> */
    public function eventsOfType(string $type): array
    {
        return array_values(array_filter($this->events, fn (LogEvent $e) => $e->type === $type));
    }

    /**
     * Records a user reply; returns which tutor turn must follow.
     *
     * @throws InputRejectedException (no turn saved)
     */
    public function submitUserReply(string $text, Classification|string|null $classification): TurnType
    {
        if ($this->phase !== Phase::AwaitingExplanation && $this->phase !== Phase::AwaitingReply) {
            throw new LogicException("Cannot accept a reply in phase {$this->phase->value}");
        }
        $cls = self::toClassification($classification);
        $check = self::checkUserInput($text, $cls, $this->phase === Phase::AwaitingExplanation, $this->config);
        if (! $check->ok) {
            throw new InputRejectedException($check);
        }

        if ($this->firstClassification === null) {
            $this->firstClassification = $cls;
        } elseif ($this->classification !== $cls) {
            $this->log('classification_changed', ['from' => $this->classification?->value, 'to' => $cls->value]);
        }
        $this->classification = $cls;
        $this->userReplyCount += 1;
        $this->history[] = new DialogTurn('user', $text, $cls, count($this->history) + 1);
        $this->log('user_reply', ['classification' => $cls->value, 'words' => Text::wordCount($text)]);

        $this->lastDontKnowHeuristic = Text::isDontKnow($text);
        $this->lastMinimalHeuristic = $this->userReplyCount > 1 && Text::isMinimalReply($text);
        $this->prevConsecutiveMinimal = $this->consecutiveMinimal;
        $this->consecutiveMinimal = $this->lastMinimalHeuristic ? $this->prevConsecutiveMinimal + 1 : 0;

        if ($this->userReplyCount >= $this->config['maxUserReplies']) {
            $this->enterClosing(CloseReason::MaxReplies);

            return TurnType::Closing;
        }
        $this->phase = Phase::AwaitingReply;

        return TurnType::Dialog;
    }

    /** Adds a tutor message to the history without analysis (replay of a recorded dialog, e.g. in the harness). */
    public function appendTutorMessage(string $text): void
    {
        $this->history[] = new DialogTurn('tutor', $text, null, count($this->history) + 1);
    }

    /**
     * Applies the ids the model claims as recognized: an id not yet recognized counts only if the user's own
     * messages plausibly refer to it (ClueEvidence); the outcome is kept in `lastClueCheck` for the logs.
     *
     * @param list<string> $claimed
     * @return array{claimed: list<string>, accepted: list<string>, rejected: list<string>}
     */
    public function acceptClaimedRecognized(array $claimed): array
    {
        $claimed = array_values(array_unique($claimed));
        $new = array_values(array_filter($claimed, fn ($id) => ! in_array($id, $this->recognized, true)));
        $check = ClueEvidence::filter($this->card, $new, $this->userTexts());
        $this->markRecognized($check['accepted']);

        return $this->lastClueCheck = [
            'claimed' => $claimed,
            'accepted' => array_values(array_filter($claimed, fn ($id) => in_array($id, $this->recognized, true))),
            'rejected' => $check['rejected'],
        ];
    }

    /** @return list<string> the user's messages on this email */
    private function userTexts(): array
    {
        $texts = [];
        foreach ($this->history as $t) {
            if ($t->author === 'user') {
                $texts[] = $t->text;
            }
        }

        return $texts;
    }

    /** Marks ids as recognized without any check (replay of a recorded dialog, e.g. `recognizedBefore` in the harness). */
    public function markRecognized(array $ids): void
    {
        foreach ($ids as $id) {
            if (! in_array($id, $this->recognized, true)) {
                $this->recognized[] = $id;
            }
        }
    }

    private function enterClosing(CloseReason $reason): void
    {
        $this->phase = Phase::Closing;
        $this->closeReason = $reason;
    }

    public function lastUserText(): string
    {
        for ($i = count($this->history) - 1; $i >= 0; $i--) {
            if ($this->history[$i]->author === 'user') {
                return $this->history[$i]->text;
            }
        }

        return '';
    }

    public function promptContext(TurnType $turnType): PromptContext
    {
        return new PromptContext(
            card: $this->card,
            exerciseNumber: $this->position,
            exerciseTotal: $this->config['exerciseTotal'],
            turnType: $turnType,
            userReplyCount: $this->userReplyCount,
            maxUserReplies: $this->config['maxUserReplies'],
            helpLevel: $this->helpLevel,
            module3Guidelines: $this->module3Guidelines,
            styleCard: $this->styleCard,
            history: $this->history,
            previousMissedClues: $this->previousMissedClues,
        );
    }

    public function validationContext(TurnType $turnType): ValidationContext
    {
        return new ValidationContext($this->card, $turnType, $this->helpLevel, $this->lastDontKnowHeuristic, $this->userTexts(), $this->recognized);
    }

    private function baseOutput(string $message): TutorOutput
    {
        $all = Materials::cardIds($this->card);

        return new TutorOutput(
            message: $message,
            recognized_clue_ids: array_values(array_filter($all, fn ($id) => in_array($id, $this->recognized, true))),
            missing_clue_ids: array_values(array_filter($all, fn ($id) => ! in_array($id, $this->recognized, true))),
            asked_for_answer: Text::isAnswerRequest($this->lastUserText()),
            propose_close: false,
            focus_clue_id: null,
            user_said_dont_know: $this->lastDontKnowHeuristic,
            minimal_reply: $this->lastMinimalHeuristic,
            unsupported_claims: [],
            off_topic: false,
        );
    }

    /** Deterministic dialog fallback: first unused hint of the card for the level (one level up after "don't know"). */
    public function fallbackDialogOutput(): TutorOutput
    {
        $level = $this->lastDontKnowHeuristic ? self::raiseLevel($this->helpLevel) : $this->helpLevel;
        $hints = array_values($this->card['fallback_hints'][$level->value] ?? []);
        $unused = array_values(array_filter($hints, fn (string $h) => ! in_array($h, $this->usedHints, true)));
        // Prefer a hint that does not point back at a zone the user already recognized (it would repeat them).
        $fresh = array_values(array_filter($unused, fn (string $h) => ! $this->hintPointsOnlyToRecognizedZones($h)));
        $hint = $fresh[0] ?? $unused[0] ?? $hints[count($hints) - 1] ?? 'What are you basing your decision on?';
        if (! in_array($hint, $this->usedHints, true)) {
            $this->usedHints[] = $hint;
        }

        return $this->baseOutput($hint);
    }

    /** Card fields of each zone family of Validator::ZONE_FAMILIES (the generic "link" family is not a zone hint here). */
    private const ZONE_FIELDS = ['sender' => ['sender_address'], 'reply_to' => ['reply_to'], 'hover' => ['link'], 'attachment' => ['attachment']];

    /** True when every zone the hint points to belongs to an item the user already recognized. */
    private function hintPointsOnlyToRecognizedZones(string $hint): bool
    {
        $recognizedFields = [];
        foreach (Materials::cardItems($this->card) as $item) {
            if (in_array($item['id'], $this->recognized, true)) {
                $recognizedFields[] = $item['target']['field'] ?? null;
            }
        }
        $zones = array_keys(array_filter(self::ZONE_FIELDS, fn (array $_, string $family) => JsRegex::test(Validator::ZONE_FAMILIES[$family], $hint), ARRAY_FILTER_USE_BOTH));
        if ($zones === []) {
            return false;
        }
        foreach ($zones as $family) {
            if (array_intersect(self::ZONE_FIELDS[$family], $recognizedFields) === []) {
                return false;
            }
        }

        return true;
    }

    /** Deterministic closing fallback: the card's static feedback (cap4 4.4.5). */
    public function fallbackClosingOutput(): TutorOutput
    {
        return $this->baseOutput($this->card['static_feedback'])->with(['propose_close' => true]);
    }

    /** @return list<string> */
    private static function violationKeys(AttemptRecord $a): array
    {
        return array_map(fn (Violation $v) => $v->key(), $a->violations);
    }

    private function logTurnResult(TurnResult $result, TurnType $turnType): void
    {
        $failed = array_values(array_filter($result->attempts, fn (AttemptRecord $a) => ! $a->ok));
        foreach ($failed as $i => $a) {
            // The last failure before the fallback is logged as part of the fallback event.
            if ($result->source === 'model' || $i < count($failed) - 1) {
                $this->log('retry', ['turnType' => $turnType->value, 'attempt' => $a->attempt, 'failure' => $a->failure?->value, 'codes' => self::violationKeys($a)]);
            }
        }
        if ($result->source === 'fallback') {
            $this->log('fallback', [
                'turnType' => $turnType->value,
                'attempts' => count($result->attempts),
                'failures' => array_map(fn (AttemptRecord $a) => $a->failure?->value, $result->attempts),
                'codes' => array_merge([], ...array_map(fn (AttemptRecord $a) => self::violationKeys($a), $result->attempts)),
            ]);
        }
        $o = $result->output;
        $this->log('tutor_turn', [
            'turnType' => $turnType->value,
            'source' => $result->source,
            'attempts' => count($result->attempts),
            'recognized' => $o->recognized_clue_ids, // the model's claim, as returned
            'accepted' => $this->lastClueCheck['accepted'] ?? [],
            'rejected' => $this->lastClueCheck['rejected'] ?? [],
            'missing' => $o->missing_clue_ids,
            'focus' => $o->focus_clue_id,
        ]);
        if ($turnType === TurnType::Dialog) {
            if ($o->user_said_dont_know || $this->lastDontKnowHeuristic) {
                $this->log('dont_know');
            }
            if ($o->asked_for_answer || Text::isAnswerRequest($this->lastUserText())) {
                $this->log('asked_for_answer');
            }
            if ($o->off_topic) {
                $this->log('off_topic');
            }
            if ($o->minimal_reply || $this->lastMinimalHeuristic) {
                $this->log('minimal_reply');
            }
            if ($o->unsupported_claims !== []) {
                $this->log('unsupported_claims', ['claims' => $o->unsupported_claims]);
            }
        }
    }

    /**
     * Applies a dialog-turn result and decides whether the dialog closes.
     *
     * @return array{shown: ?string, phase: Phase} the message to show, if any
     */
    public function applyDialogResult(TurnResult $result): array
    {
        if ($this->phase !== Phase::AwaitingReply) {
            throw new LogicException("No dialog turn expected in phase {$this->phase->value}");
        }
        $o = $result->output;
        $this->acceptClaimedRecognized($o->recognized_clue_ids);
        if ($o->minimal_reply && ! $this->lastMinimalHeuristic && $this->userReplyCount > 1) {
            $this->consecutiveMinimal = $this->prevConsecutiveMinimal + 1;
        }
        $this->logTurnResult($result, TurnType::Dialog);

        $enough = $this->userReplyCount >= $this->config['minUserReplies'];
        $reason = null;
        if ($enough && $this->allMainRecognized()) {
            $reason = CloseReason::AllMainRecognized;
        } elseif ($enough && $this->consecutiveMinimal >= $this->config['minimalRepliesToClose']) {
            $reason = CloseReason::MinimalReplies;
        }

        if ($reason !== null) {
            $this->enterClosing($reason);
            // A question the user can no longer answer is never shown; a transition sentence is.
            if (Text::questionCount($o->message) > 0) {
                $this->log('dialog_message_suppressed', ['reason' => $reason->value]);

                return ['shown' => null, 'phase' => $this->phase];
            }
        }
        $this->appendTutorMessage($o->message);

        return ['shown' => $o->message, 'phase' => $this->phase];
    }

    /** Applies the closing-turn result: verdict and found/missed items come from the card (G2), message from the tutor. */
    public function applyClosingResult(TurnResult $result): ClosingView
    {
        if ($this->phase !== Phase::Closing) {
            throw new LogicException("No closing turn expected in phase {$this->phase->value}");
        }
        $this->acceptClaimedRecognized($result->output->recognized_clue_ids);
        $this->logTurnResult($result, TurnType::Closing);
        $items = Materials::cardItems($this->card);
        $this->closingView = new ClosingView(
            verdict: ! empty($this->card['is_phishing']) ? Classification::Phishing : Classification::Legitimate,
            found: array_values(array_filter($items, fn (array $i) => in_array($i['id'], $this->recognized, true))),
            missed: array_values(array_filter($items, fn (array $i) => ! in_array($i['id'], $this->recognized, true))),
            message: $result->output->message,
            source: $result->source,
        );
        $this->phase = Phase::Closed;
        $this->log('closed', [
            'reason' => $this->closeReason?->value,
            'firstClassification' => $this->firstClassification?->value,
            'finalClassification' => $this->classification?->value,
        ]);

        return $this->closingView;
    }

    /**
     * Labels of the main items missed on this email: input of `{{previous_missed_clues}}` for the next emails.
     *
     * @return list<string>
     */
    public function missedMainLabels(): array
    {
        $labels = [];
        foreach (Materials::cardItems($this->card) as $i) {
            if (! empty($i['main']) && ! in_array($i['id'], $this->recognized, true)) {
                $labels[] = $i['label'];
            }
        }

        return $labels;
    }

    /** @return array{shown: ?string, phase: Phase} */
    public function runDialogTurn(Tutor $engine): array
    {
        $result = $engine->requestTurn($this->promptContext(TurnType::Dialog), $this->validationContext(TurnType::Dialog), fn () => $this->fallbackDialogOutput());

        return $this->applyDialogResult($result);
    }

    public function runClosingTurn(Tutor $engine): ClosingView
    {
        $result = $engine->requestTurn($this->promptContext(TurnType::Closing), $this->validationContext(TurnType::Closing), fn () => $this->fallbackClosingOutput());

        return $this->applyClosingResult($result);
    }

    /**
     * Full step: user reply -> dialog turn and/or closing turn.
     *
     * @throws InputRejectedException if the input does not pass the gate (nothing saved, no model call)
     */
    public function step(Tutor $engine, string $text, Classification|string|null $classification): StepOutcome
    {
        $next = $this->submitUserReply($text, $classification);
        $shown = [];
        if ($next === TurnType::Dialog) {
            $r = $this->runDialogTurn($engine);
            if ($r['shown'] !== null) {
                $shown[] = $r['shown'];
            }
        }
        if ($this->phase === Phase::Closing) {
            $closing = $this->runClosingTurn($engine);
            $shown[] = $closing->message;

            return new StepOutcome($shown, $this->phase, $closing);
        }

        return new StepOutcome($shown, $this->phase);
    }

    // ---- persistence ------------------------------------------------------------------------------------------

    /**
     * JSON-plain snapshot of the whole state (for the DB between HTTP requests). The card is referenced by id only:
     * pass the same card to `fromArray()`.
     */
    public function toArray(): array
    {
        return [
            'v' => self::STATE_VERSION,
            'card_id' => $this->card['id'],
            'position' => $this->position,
            'help_level' => $this->helpLevel->value,
            'config' => $this->config,
            'module3_guidelines' => $this->module3Guidelines,
            'style_card' => $this->styleCard,
            'previous_missed_clues' => $this->previousMissedClues,
            'phase' => $this->phase->value,
            'user_reply_count' => $this->userReplyCount,
            'classification' => $this->classification?->value,
            'first_classification' => $this->firstClassification?->value,
            'close_reason' => $this->closeReason?->value,
            'closing_view' => $this->closingView?->toArray(),
            'recognized' => $this->recognized,
            'history' => array_map(fn (DialogTurn $t) => $t->toArray(), $this->history),
            'events' => array_map(fn (LogEvent $e) => $e->toArray(), $this->events),
            'consecutive_minimal' => $this->consecutiveMinimal,
            'prev_consecutive_minimal' => $this->prevConsecutiveMinimal,
            'last_minimal_heuristic' => $this->lastMinimalHeuristic,
            'last_dont_know_heuristic' => $this->lastDontKnowHeuristic,
            'used_hints' => $this->usedHints,
        ];
    }

    /**
     * Restores a session saved with `toArray()`. The help level is restored as saved (not recomputed), so a later
     * change of the level map does not alter a running dialog.
     *
     * @param array<string, mixed> $card the card with id `$data['card_id']`
     */
    public static function fromArray(array $data, array $card, ?callable $clock = null): self
    {
        if (($data['v'] ?? null) !== self::STATE_VERSION) {
            throw new InvalidArgumentException('Unsupported DialogSession state version');
        }
        if ($card['id'] !== $data['card_id']) {
            throw new InvalidArgumentException("Card {$card['id']} does not match saved card {$data['card_id']}");
        }
        $s = new self(
            card: $card,
            position: $data['position'],
            levelMap: [(string) $data['position'] => $data['help_level']],
            config: $data['config'],
            module3Guidelines: $data['module3_guidelines'],
            styleCard: $data['style_card'],
            previousMissedClues: $data['previous_missed_clues'],
            clock: $clock,
        );
        $s->phase = Phase::from($data['phase']);
        $s->userReplyCount = $data['user_reply_count'];
        $s->classification = Classification::tryFrom((string) $data['classification']);
        $s->firstClassification = Classification::tryFrom((string) $data['first_classification']);
        $s->closeReason = CloseReason::tryFrom((string) $data['close_reason']);
        $s->closingView = $data['closing_view'] !== null ? ClosingView::fromArray($data['closing_view'], $card) : null;
        $s->recognized = array_values($data['recognized']);
        $s->history = array_map(fn (array $t) => DialogTurn::fromArray($t), array_values($data['history']));
        $s->events = array_map(fn (array $e) => LogEvent::fromArray($e), array_values($data['events']));
        $s->consecutiveMinimal = $data['consecutive_minimal'];
        $s->prevConsecutiveMinimal = $data['prev_consecutive_minimal'];
        $s->lastMinimalHeuristic = $data['last_minimal_heuristic'];
        $s->lastDontKnowHeuristic = $data['last_dont_know_heuristic'];
        $s->usedHints = array_values($data['used_hints']);

        return $s;
    }
}
