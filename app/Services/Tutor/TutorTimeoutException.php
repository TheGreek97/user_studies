<?php

namespace App\Services\Tutor;

use RuntimeException;
use Throwable;

/** The provider did not reply within the turn timeout (failure type `timeout`, test T22). */
class TutorTimeoutException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
