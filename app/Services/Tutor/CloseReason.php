<?php

namespace App\Services\Tutor;

/** Why the dialog on one email was closed (cap4 4.4.4). */
enum CloseReason: string
{
    case AllMainRecognized = 'all_main_recognized';
    case MaxReplies = 'max_replies';
    case MinimalReplies = 'minimal_replies';
}
