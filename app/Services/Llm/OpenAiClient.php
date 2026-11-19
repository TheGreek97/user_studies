<?php

namespace App\Services\Llm;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * LlmClient reproducing the paper's original generation setup: OpenAI Chat Completions
 * with o3-mini, reasoning_effort "medium", system instructions sent with role "developer".
 * Kept as the default provider so that the paper's conditions remain reproducible.
 *
 * Note: `effort` is mapped to `reasoning_effort`. No automatic retries here: the queue
 * job retries the whole generation (ProcessTraining::$tries / $backoff).
 */
final class OpenAiClient implements LlmClient
{
    public const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'o3-mini',
        private readonly string $reasoningEffort = 'medium',
        private readonly int $timeout = 180,
    ) {
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $payload = [
            'model' => $this->model,
            'reasoning_effort' => $options['effort'] ?? $this->reasoningEffort,
            'messages' => array_merge(
                [['role' => 'developer', 'content' => $system]],
                array_map(static fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']], $messages)
            ),
        ];
        if (isset($options['max_tokens'])) {
            $payload['max_completion_tokens'] = $options['max_tokens'];
        }
        if (isset($options['json_schema'])) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => ['name' => 'response', 'schema' => $options['json_schema'], 'strict' => false],
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout((int) ($options['timeout'] ?? $this->timeout))
                ->post(self::ENDPOINT, $payload);
        } catch (ConnectionException $e) {
            if (LlmTimeoutException::isTimeout($e)) {
                throw new LlmTimeoutException('OpenAI API request timed out: ' . $e->getMessage(), $e);
            }
            throw new LlmException('OpenAI API connection error: ' . $e->getMessage(), true, $e);
        }

        if ($response->failed()) {
            $status = $response->status();
            $retryable = in_array($status, [408, 409, 429], true) || $status >= 500;
            $detail = $response->json('error.message') ?? 'no error message';
            throw new LlmException("OpenAI API error (HTTP $status): $detail", $retryable);
        }

        $choice = $response->json('choices.0') ?? [];
        $finishReason = $choice['finish_reason'] ?? null;
        $text = $choice['message']['content'] ?? null;

        if (!empty($choice['message']['refusal'])) {
            throw new LlmException('Model refused the request: ' . $choice['message']['refusal'], false);
        }
        if ($finishReason === 'length') {
            throw new LlmException('Output truncated (finish_reason=length).', false);
        }
        if (!is_string($text) || trim($text) === '') {
            throw new LlmException("Empty response (finish_reason=$finishReason).", false);
        }

        return new LlmResponse(
            text: $text,
            model: (string) ($response->json('model') ?? $this->model),
            stopReason: $finishReason,
            usage: [
                'input_tokens' => (int) $response->json('usage.prompt_tokens', 0),
                'output_tokens' => (int) $response->json('usage.completion_tokens', 0),
            ],
        );
    }
}
