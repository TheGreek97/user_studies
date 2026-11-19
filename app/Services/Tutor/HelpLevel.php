<?php

namespace App\Services\Tutor;

/** Help level of the tutor (cap4 Tabella 13), fixed by the position of the email. */
enum HelpLevel: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
