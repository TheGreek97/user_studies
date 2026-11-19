<?php

namespace App\Services\Tutor;

use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmResponse;
use Exception;

/**
 * Decorator that records model, token usage and latency of every call, so the platform can log them per tutor turn
 * (tutor_turns) without touching the Tutor core, which only returns the text.
 */
class MeteredLlmClient implements LlmClient
{
    /** @var list<array{model: ?string, input_tokens: ?int, output_tokens: ?int, latency_ms: int, error: ?string}> */
    private array $records = [];

    public function __construct(private readonly LlmClient $inner)
    {
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $start = hrtime(true);
        try {
            $response = $this->inner->complete($system, $messages, $options);
        } catch (Exception $e) {
            $this->records[] = ['model' => null, 'input_tokens' => null, 'output_tokens' => null, 'latency_ms' => self::since($start), 'error' => $e->getMessage()];
            throw $e;
        }
        $this->records[] = [
            'model' => $response->model,
            'input_tokens' => $response->usage['input_tokens'] ?? null,
            'output_tokens' => $response->usage['output_tokens'] ?? null,
            'latency_ms' => self::since($start),
            'error' => null,
        ];

        return $response;
    }

    /**
     * Returns the calls recorded since the last take() and resets the list.
     *
     * @return list<array{model: ?string, input_tokens: ?int, output_tokens: ?int, latency_ms: int, error: ?string}>
     */
    public function take(): array
    {
        $records = $this->records;
        $this->records = [];

        return $records;
    }

    private static function since(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1e6);
    }
}
