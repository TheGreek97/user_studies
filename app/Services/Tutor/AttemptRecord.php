<?php

namespace App\Services\Tutor;

/** One provider attempt of a tutor turn, kept for the log and the harness (test T22). */
final class AttemptRecord
{
    /** @param list<Violation> $violations */
    public function __construct(
        public readonly int $attempt,
        public readonly bool $ok,
        public readonly ?FailureType $failure = null,
        public readonly ?string $error = null,
        public readonly array $violations = [],
        /** Raw reply; never shown to the user when invalid. */
        public readonly ?string $raw = null,
    ) {
    }
}
