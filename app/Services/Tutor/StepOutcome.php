<?php

namespace App\Services\Tutor;

final class StepOutcome
{
    /** @param list<string> $shown messages shown to the user in this step, in order */
    public function __construct(
        public readonly array $shown,
        public readonly Phase $phase,
        public readonly ?ClosingView $closing = null,
    ) {
    }
}
