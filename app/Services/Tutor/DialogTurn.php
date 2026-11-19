<?php

namespace App\Services\Tutor;

use JsonSerializable;

/** One entry of `{{dialog_history}}` (tutor-system-prompt.md, placeholder table). */
final class DialogTurn implements JsonSerializable
{
    public function __construct(
        public readonly string $author, // 'user' | 'tutor'
        public readonly string $text,
        /** User's current classification for user turns; null for tutor turns. */
        public readonly ?Classification $classification,
        /** 1-based position in the dialog on the current email. */
        public readonly int $turn,
    ) {
    }

    /** @return array{author: string, text: string, classification: ?string, turn: int} */
    public function toArray(): array
    {
        return ['author' => $this->author, 'text' => $this->text, 'classification' => $this->classification?->value, 'turn' => $this->turn];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function fromArray(array $a): self
    {
        return new self($a['author'], $a['text'], isset($a['classification']) ? Classification::from($a['classification']) : null, (int) $a['turn']);
    }
}
