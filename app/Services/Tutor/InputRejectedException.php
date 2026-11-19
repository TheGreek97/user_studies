<?php

namespace App\Services\Tutor;

use RuntimeException;

/** The user input did not pass the gate: no turn saved, no model call (tests T02, T03). */
class InputRejectedException extends RuntimeException
{
    public function __construct(public readonly InputCheck $check)
    {
        parent::__construct((string) $check->message);
    }
}
