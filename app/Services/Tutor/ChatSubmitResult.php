<?php

namespace App\Services\Tutor;

/** Outcome of TutorConversation::submit() for the UI. */
final class ChatSubmitResult
{
    public const OK = 'ok';
    public const REJECTED = 'rejected';         // input gate failed (no turn saved, no model call)
    public const RATE_LIMITED = 'rate_limited'; // too many messages (no model call)
    public const BUSY = 'busy';                 // a previous message of the same user is still being processed
    public const NOT_ACCEPTING = 'not_accepting'; // dialog closed or module completed

    public function __construct(
        public readonly string $status,
        /** Friendly message for the user (null when OK). */
        public readonly ?string $notice = null,
    ) {
    }

    public function ok(): bool
    {
        return $this->status === self::OK;
    }
}
