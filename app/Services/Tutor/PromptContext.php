<?php

namespace App\Services\Tutor;

final class PromptContext
{
    /**
     * @param array<string, mixed> $card
     * @param list<array{id: string, text: string, email_part: string}> $module3Guidelines
     * @param list<string> $styleCard up to 3 "Communication Style" lines (cap4 par. 4.5.3)
     * @param list<DialogTurn> $history
     * @param list<string> $previousMissedClues labels (never ids) of main items missed in the previous emails
     */
    public function __construct(
        public readonly array $card,
        public readonly int $exerciseNumber,
        public readonly int $exerciseTotal,
        public readonly TurnType $turnType,
        public readonly int $userReplyCount,
        public readonly int $maxUserReplies,
        public readonly HelpLevel $helpLevel,
        public readonly array $module3Guidelines,
        public readonly array $styleCard,
        public readonly array $history,
        public readonly array $previousMissedClues,
    ) {
    }
}
