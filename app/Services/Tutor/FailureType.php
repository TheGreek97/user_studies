<?php

namespace App\Services\Tutor;

/** Failure of one tutor attempt (cap4 4.4.5, test T22). */
enum FailureType: string
{
    case ProviderError = 'provider_error';
    case Timeout = 'timeout';
    case InvalidOutput = 'invalid_output';
}
