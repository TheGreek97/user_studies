<?php

namespace App\Services\Tutor;

use App\Services\Llm\LlmClient;
use Closure;
use Exception;

/**
 * One tutor call with the reserve intervention of cap4 par. 4.4.5 (port of `src/tutor.ts`, `TutorEngine`):
 * call -> validate; on error, timeout or violation retry ONCE; if it fails again use the deterministic fallback.
 * Every attempt is returned so that the caller can log type of failure and number of attempts (test T22).
 *
 * PHP cannot abort a running call: the timeout is passed to the LlmClient (`timeout`, whole seconds, rounded up)
 * and a reply that arrives after `timeoutMs` is discarded as a timeout, as `Promise.race` does in the TS core.
 */
class Tutor
{
    public const DEFAULT_TIMEOUT_MS = 20_000;

    /** Effort for chat turns: latency matters more than depth (platform choice, not in the TS core). */
    public const DEFAULT_EFFORT = 'low';

    /** Model name of the last successful call, for reports (null with a client that reports none). */
    public ?string $lastModel = null;

    /**
     * @param array<string, mixed> $schema full output schema (tutor-output-schema.json)
     * @param string $template system template extracted from tutor-system-prompt.md
     * @param int $maxAttempts total attempts before the fallback (cap4: one retry -> 2)
     */
    public function __construct(
        public readonly LlmClient $client,
        public readonly array $schema,
        public readonly string $template,
        public readonly int $timeoutMs = self::DEFAULT_TIMEOUT_MS,
        public readonly int $maxAttempts = 2,
        public readonly ?string $effort = self::DEFAULT_EFFORT,
        public readonly ?int $maxTokens = null,
    ) {
    }

    /** Engine built from the materials folder (prompt template + schema). */
    public static function fromMaterials(LlmClient $client, Materials $materials, int $timeoutMs = self::DEFAULT_TIMEOUT_MS): self
    {
        return new self(
            $client,
            $materials->loadOutputSchema(),
            PromptBuilder::extractSystemTemplate($materials->loadPromptMarkdown()),
            $timeoutMs,
        );
    }

    /** Options of the LlmClient request: full schema (the client strips what its provider does not support). */
    public function requestOptions(?int $timeoutMs = null): array
    {
        $options = [
            'json_schema' => $this->schema,
            'timeout' => max(1, (int) ceil(($timeoutMs ?? $this->timeoutMs) / 1000)),
        ];
        if ($this->effort !== null) {
            $options['effort'] = $this->effort;
        }
        if ($this->maxTokens !== null) {
            $options['max_tokens'] = $this->maxTokens;
        }

        return $options;
    }

    /**
     * Single provider call bounded by a timeout.
     *
     * @throws TutorTimeoutException when the client timed out or replied too late
     * @throws Exception any provider error (LlmException in the platform clients)
     */
    public function callWithTimeout(string $system, string $user, ?int $ms = null): string
    {
        $ms ??= $this->timeoutMs;
        $start = hrtime(true);
        try {
            $response = $this->client->complete($system, [['role' => 'user', 'content' => $user]], $this->requestOptions($ms));
        } catch (Exception $e) {
            if (self::looksLikeTimeout($e) || self::elapsedMs($start) >= $ms) {
                throw new TutorTimeoutException("no reply within {$ms} ms ({$e->getMessage()})", $e);
            }
            throw $e;
        }
        if (self::elapsedMs($start) > $ms) {
            throw new TutorTimeoutException("no reply within {$ms} ms");
        }
        $this->lastModel = $response->model;

        return $response->text;
    }

    private static function elapsedMs(int $start): float
    {
        return (hrtime(true) - $start) / 1e6;
    }

    /** Real clients raise LlmTimeoutException; the message check is kept for the fake client's simulated hang. */
    public static function looksLikeTimeout(Exception $e): bool
    {
        return $e instanceof TutorTimeoutException
            || $e instanceof \App\Services\Llm\LlmTimeoutException
            || preg_match('/\btimed?[\s-]?out\b/i', $e->getMessage()) === 1;
    }

    /** @param Closure(): TutorOutput $fallback */
    public function requestTurn(PromptContext $promptCtx, ValidationContext $validationCtx, Closure $fallback): TurnResult
    {
        $prompt = PromptBuilder::buildPrompt($this->template, $promptCtx);
        $attempts = [];
        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            try {
                $raw = $this->callWithTimeout($prompt->system, $prompt->user);
            } catch (Exception $e) {
                $failure = $e instanceof TutorTimeoutException ? FailureType::Timeout : FailureType::ProviderError;
                $attempts[] = new AttemptRecord($attempt, false, $failure, $e->getMessage());

                continue;
            }
            $res = Validator::validateTutorReply($raw, $this->schema, $validationCtx);
            if ($res->ok && $res->output !== null) {
                $attempts[] = new AttemptRecord($attempt, true, raw: $raw);

                return new TurnResult($res->output, 'model', $attempts);
            }
            $attempts[] = new AttemptRecord($attempt, false, FailureType::InvalidOutput, null, $res->violations, $raw);
        }

        return new TurnResult($fallback(), 'fallback', $attempts);
    }
}
