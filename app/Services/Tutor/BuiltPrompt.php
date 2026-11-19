<?php

namespace App\Services\Tutor;

final class BuiltPrompt
{
    public function __construct(
        public readonly string $system,
        public readonly string $user,
    ) {
    }
}
