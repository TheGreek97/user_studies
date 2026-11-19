<?php

namespace App\Services\Tutor;

use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmException;
use App\Services\Llm\LlmResponse;

/**
 * Scripted LlmClient (port of the TS MockProvider): returns the scripted replies in order. Used by the tests and
 * by `tutor:harness --provider=fake`; never calls the network.
 *
 * A step is raw text, `['json' => value]` (serialised), `['error' => message]` (LlmException), or
 * `['hang' => true]`: a call that never answers, reported as the client timeout PHP can observe
 * (LlmException "timed out").
 */
class FakeLlmClient implements LlmClient
{
    /** @var list<array{system: string, messages: array, options: array}> */
    public array $calls = [];

    /** @var list<string|array> */
    private array $script;

    public function __construct(array $script)
    {
        $this->script = array_values($script);
    }

    /** Remaining scripted steps (to assert that no extra call was made). */
    public function remaining(): int
    {
        return count($this->script);
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $this->calls[] = ['system' => $system, 'messages' => $messages, 'options' => $options];
        if ($this->script === []) {
            throw new LlmException('FakeLlmClient: script exhausted');
        }
        $step = array_shift($this->script);
        if (is_string($step)) {
            return new LlmResponse($step, 'fake');
        }
        if (array_key_exists('json', $step)) {
            return new LlmResponse(json_encode($step['json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'fake');
        }
        if (array_key_exists('error', $step)) {
            throw new LlmException($step['error']);
        }
        $seconds = $options['timeout'] ?? null;
        throw new LlmException('Request timed out'.($seconds !== null ? " after {$seconds} s" : '').' (simulated hang)', true);
    }
}
