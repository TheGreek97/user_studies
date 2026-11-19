<?php

namespace App\Services\Llm;

use RuntimeException;

/**
 * Raised by LlmClient implementations. `retryable` is true for rate limits, overload,
 * timeouts and 5xx errors, false for invalid requests and refusals.
 */
class LlmException extends RuntimeException
{
    public function __construct(string $message, public readonly bool $retryable = false, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
