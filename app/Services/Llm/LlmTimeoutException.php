<?php

namespace App\Services\Llm;

use GuzzleHttp\Exception\ConnectException;
use Throwable;

/**
 * Raised when an LLM call exceeds its HTTP timeout (options['timeout'], seconds) or the
 * connection times out. Always retryable. The message always contains "timed out".
 */
class LlmTimeoutException extends LlmException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, true, $previous);
    }

    /**
     * True if $e (or one of its previous exceptions) is a transport timeout:
     * cURL errno 28 (CURLE_OPERATION_TIMEDOUT) or a "timed out" transport message.
     */
    public static function isTimeout(?Throwable $e): bool
    {
        for (; $e !== null; $e = $e->getPrevious()) {
            if ($e instanceof ConnectException && ($e->getHandlerContext()['errno'] ?? null) === 28) {
                return true;
            }
            if (preg_match('/timed?[\s-]?out|cURL error 28/i', $e->getMessage())) {
                return true;
            }
        }

        return false;
    }
}
