<?php

namespace App\Services\Tutor;

/**
 * Structured reply of the tutor (`tutor-output-schema.json`). Built only from data that passed the schema.
 */
final class TutorOutput
{
    /**
     * @param list<string> $recognized_clue_ids
     * @param list<string> $missing_clue_ids
     * @param list<string> $unsupported_claims
     */
    public function __construct(
        public readonly string $message,
        public readonly array $recognized_clue_ids,
        public readonly array $missing_clue_ids,
        public readonly bool $asked_for_answer,
        public readonly bool $propose_close,
        public readonly ?string $focus_clue_id,
        public readonly bool $user_said_dont_know,
        public readonly bool $minimal_reply,
        public readonly array $unsupported_claims,
        public readonly bool $off_topic,
    ) {
    }

    /** @param array<string, mixed>|object $data schema-valid reply (decoded object or array) */
    public static function fromData(array|object $data): self
    {
        $d = json_decode(json_encode($data, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return new self(
            $d['message'],
            array_values($d['recognized_clue_ids']),
            array_values($d['missing_clue_ids']),
            $d['asked_for_answer'],
            $d['propose_close'],
            $d['focus_clue_id'],
            $d['user_said_dont_know'],
            $d['minimal_reply'],
            array_values($d['unsupported_claims']),
            $d['off_topic'],
        );
    }

    /** @return array<string, mixed> in schema order */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /** Copy with some fields replaced (TS `{ ...out, propose_close: true }`). */
    public function with(array $changes): self
    {
        return self::fromData(array_merge($this->toArray(), $changes));
    }
}
