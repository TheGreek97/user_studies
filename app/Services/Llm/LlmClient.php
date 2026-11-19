<?php

namespace App\Services\Llm;

/**
 * Provider-neutral chat completion client.
 *
 * Used by the training generation job (ProcessTraining) and by the Module 4 tutor.
 * Implementations: AnthropicClient (Claude Messages API) and OpenAiClient (the original
 * o3-mini setup of the paper, kept so the paper's conditions can still be reproduced).
 */
interface LlmClient
{
    /**
     * Send one request and return the model's text.
     *
     * @param string $system   System / developer instructions.
     * @param array<int, array{role: 'user'|'assistant', content: string}> $messages Conversation so far, oldest first.
     * @param array{
     *     max_tokens?: int,
     *     effort?: 'low'|'medium'|'high',
     *     json_schema?: array<string, mixed>,
     *     timeout?: int,
     * } $options  json_schema asks for a JSON object matching the schema (structured output).
     *
     * @throws LlmException on transport errors, non-2xx responses, refusals or empty output.
     */
    public function complete(string $system, array $messages, array $options = []): LlmResponse;
}
