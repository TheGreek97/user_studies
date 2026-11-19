<?php

namespace App\Services\Tutor;

/** One failed check of the validator; `rule` is K1, G1, G2, G3, G5, G6, G8 or G9. */
final class Violation
{
    public function __construct(
        public readonly string $rule,
        public readonly string $code,
        public readonly string $detail,
    ) {
    }

    /** `RULE:code`, the form used in the log and in the tests. */
    public function key(): string
    {
        return "{$this->rule}:{$this->code}";
    }
}
