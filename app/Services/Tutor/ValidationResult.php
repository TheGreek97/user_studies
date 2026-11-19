<?php

namespace App\Services\Tutor;

final class ValidationResult
{
    /** @param list<Violation> $violations */
    public function __construct(
        public readonly bool $ok,
        public readonly ?TutorOutput $output,
        public readonly array $violations,
    ) {
    }

    /** @return list<string> `RULE:code` of every violation */
    public function keys(): array
    {
        return array_map(fn (Violation $v) => $v->key(), $this->violations);
    }
}
