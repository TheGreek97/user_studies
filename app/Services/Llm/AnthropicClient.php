<?php

namespace App\Services\Llm;

use Anthropic\Beta\Messages\BetaMessage;
use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Closure;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;

/**
 * LlmClient backed by the Claude Messages API (official SDK anthropic-ai/sdk).
 *
 * - Uses the beta Messages endpoint with server-side refusal fallback
 *   (`fallbacks: 'default'`): if the model declines for policy reasons, the API retries
 *   on its default fallback model and the response reports the model actually used.
 * - No `thinking` parameter (Opus 5.5 is always adaptive; `disabled` is a 400) and no
 *   sampling parameters; reasoning depth is controlled with `output_config.effort`.
 * - Transient errors (429, 529/5xx, timeouts, connection errors) are retried by the SDK
 *   (`maxRetries`); if they persist they surface as a retryable LlmException.
 * - The SDK does not enforce timeouts itself: they are set on the PSR-18 transporter,
 *   built by `$transporterFactory(int $timeoutSeconds)` (Guzzle by default). Tests inject
 *   a factory returning a Guzzle client with a MockHandler, so no network is used.
 */
final class AnthropicClient implements LlmClient
{
    public const FALLBACK_BETA = 'server-side-fallback-2026-07-01';

    private Client $client;

    /** @var Closure(int): ClientInterface */
    private Closure $transporterFactory;

    /**
     * @param Closure(int): ClientInterface|null $transporterFactory builds the HTTP client for a given timeout (seconds)
     */
    public function __construct(
        string $apiKey,
        private readonly string $model,
        private readonly string $effort = 'medium',
        private readonly int $timeout = 180,
        private readonly int $maxTokens = 16000,
        int $maxRetries = 2,
        ?Closure $transporterFactory = null,
        ?string $baseUrl = null,
    ) {
        $this->transporterFactory = $transporterFactory
            ?? static fn (int $seconds): ClientInterface => new GuzzleClient([
                'timeout' => $seconds,
                'connect_timeout' => 15,
            ]);

        $this->client = new Client(
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            requestOptions: [
                'transporter' => ($this->transporterFactory)($this->timeout),
                'maxRetries' => $maxRetries,
            ],
        );
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $outputConfig = ['effort' => $options['effort'] ?? $this->effort];
        if (isset($options['json_schema'])) {
            $outputConfig['format'] = [
                'type' => 'json_schema',
                'schema' => JsonSchemaSanitizer::sanitize($options['json_schema']),
            ];
        }

        // A per-call timeout needs its own transporter, since the SDK does not enforce timeouts.
        $requestOptions = null;
        if (isset($options['timeout']) && (int) $options['timeout'] !== $this->timeout) {
            $requestOptions = ['transporter' => ($this->transporterFactory)((int) $options['timeout'])];
        }

        try {
            $message = $this->client->beta->messages->create(
                maxTokens: $options['max_tokens'] ?? $this->maxTokens,
                messages: array_map(
                    static fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']],
                    $messages
                ),
                model: $this->model,
                fallbacks: 'default',
                outputConfig: $outputConfig,
                system: $system !== '' ? $system : null,
                betas: [self::FALLBACK_BETA],
                requestOptions: $requestOptions,
            );
        } catch (APIStatusException $e) {
            $status = (int) $e->status;
            $retryable = in_array($status, [408, 409, 429], true) || $status >= 500;
            $detail = $e->getAPIMessage() ?? class_basename($e);
            throw new LlmException("Anthropic API error (HTTP $status): $detail", $retryable, $e);
        } catch (APIConnectionException $e) {
            if (LlmTimeoutException::isTimeout($e)) {
                throw new LlmTimeoutException('Anthropic API request timed out: ' . $e->getMessage(), $e);
            }
            throw new LlmException('Anthropic API connection error: ' . $e->getMessage(), true, $e);
        } catch (AnthropicException $e) {
            throw new LlmException('Anthropic SDK error: ' . $e->getMessage(), false, $e);
        }

        return $this->toResponse($message);
    }

    private function toResponse(BetaMessage $message): LlmResponse
    {
        $stopReason = $message->stopReason;

        if ($stopReason === 'refusal') {
            throw new LlmException("Model {$message->model} refused the request (stop_reason=refusal).", false);
        }
        if ($stopReason === 'max_tokens' || $stopReason === 'model_context_window_exceeded') {
            throw new LlmException("Output truncated (stop_reason=$stopReason); increase max_tokens.", false);
        }

        // Only text blocks are the answer; thinking/other blocks are skipped.
        $text = '';
        foreach ($message->content as $block) {
            if (($block->type ?? null) === 'text') {
                $text .= $block->text;
            }
        }

        if (trim($text) === '') {
            throw new LlmException("Empty response from {$message->model} (stop_reason=$stopReason).", false);
        }

        return new LlmResponse(
            text: $text,
            model: $message->model,
            stopReason: $stopReason,
            usage: [
                'input_tokens' => $message->usage->inputTokens,
                'output_tokens' => $message->usage->outputTokens,
            ],
        );
    }
}
