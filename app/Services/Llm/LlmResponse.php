<?php

namespace App\Services\Llm;

/**
 * Text returned by an LlmClient, with the metadata needed for logging and cost estimates.
 */
final class LlmResponse
{
    /**
     * @param array{input_tokens?: int, output_tokens?: int} $usage
     */
    public function __construct(
        public readonly string $text,
        public readonly string $model,
        public readonly ?string $stopReason = null,
        public readonly array $usage = [],
    ) {
    }
}
