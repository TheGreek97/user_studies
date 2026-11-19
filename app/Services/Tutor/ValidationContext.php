<?php

namespace App\Services\Tutor;

final class ValidationContext
{
    /**
     * @param array<string, mixed> $card
     * @param list<string> $userTexts all user messages on the current email: what the user wrote may be echoed back (G4)
     * @param list<string> $recognizedBefore ids already recognized in previous turns (union kept by the platform)
     */
    public function __construct(
        public readonly array $card,
        public readonly TurnType $turnType,
        public readonly HelpLevel $helpLevel,
        /** True if the last user message is a "don't know" (platform heuristic OR model flag). */
        public readonly bool $dontKnow,
        public readonly array $userTexts,
        public readonly array $recognizedBefore,
    ) {
    }
}
