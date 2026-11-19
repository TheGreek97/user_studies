<?php

namespace App\Services\Tutor;

/** Result of the input gate; `reason` is missing_classification, empty or too_short when not ok. */
final class InputCheck
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $reason = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function pass(): self
    {
        return new self(true);
    }

    public static function fail(string $reason, string $message): self
    {
        return new self(false, $reason, $message);
    }
}
