<?php

namespace App\Services\Tutor;

final class TurnResult
{
    /** @param list<AttemptRecord> $attempts */
    public function __construct(
        public readonly TutorOutput $output,
        public readonly string $source, // 'model' | 'fallback'
        public readonly array $attempts,
    ) {
    }
}
