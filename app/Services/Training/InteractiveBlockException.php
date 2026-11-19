<?php

namespace App\Services\Training;

use App\Services\Llm\LlmException;

/**
 * The model answered, but a structured data block of the "interactive" condition is missing or
 * invalid. Retryable: a new generation usually fixes it, so the queue retries the job and
 * failed() marks the training as failed after the last attempt.
 */
class InteractiveBlockException extends LlmException
{
    public function __construct(string $message)
    {
        parent::__construct($message, true);
    }
}
