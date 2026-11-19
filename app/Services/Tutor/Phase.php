<?php

namespace App\Services\Tutor;

/** Phase of the dialog state machine (cap4 4.4.2). */
enum Phase: string
{
    case AwaitingExplanation = 'awaiting_explanation';
    case AwaitingReply = 'awaiting_reply';
    case Closing = 'closing';
    case Closed = 'closed';
}
