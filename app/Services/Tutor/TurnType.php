<?php

namespace App\Services\Tutor;

/** Kind of tutor turn (prompt placeholder {{turn_type}}). */
enum TurnType: string
{
    case Dialog = 'dialog';
    case Closing = 'closing';
}
