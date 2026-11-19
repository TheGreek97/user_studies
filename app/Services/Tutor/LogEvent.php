<?php

namespace App\Services\Tutor;

/**
 * Event of the interaction log (cap4 Tabella 10). `detail` holds only JSON-plain values, so events survive
 * a JSON round trip unchanged.
 *
 * Types: user_reply, tutor_turn, classification_changed, dont_know, asked_for_answer, off_topic, minimal_reply,
 * unsupported_claims, retry, fallback, dialog_message_suppressed, closed.
 */
final class LogEvent
{
    public function __construct(
        public readonly string $type,
        /** ISO 8601 UTC with milliseconds, like JS `toISOString()`. */
        public readonly string $at,
        /** User reply count when the event happened. */
        public readonly int $reply,
        public readonly HelpLevel $helpLevel,
        public readonly ?array $detail = null,
    ) {
    }

    public function toArray(): array
    {
        $a = ['type' => $this->type, 'at' => $this->at, 'reply' => $this->reply, 'helpLevel' => $this->helpLevel->value];
        if ($this->detail !== null) {
            $a['detail'] = $this->detail;
        }

        return $a;
    }

    public static function fromArray(array $a): self
    {
        return new self($a['type'], $a['at'], (int) $a['reply'], HelpLevel::from($a['helpLevel']), $a['detail'] ?? null);
    }
}
