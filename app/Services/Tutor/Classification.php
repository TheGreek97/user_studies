<?php

namespace App\Services\Tutor;

/** User classification of an email, and verdict of a card. */
enum Classification: string
{
    case Phishing = 'phishing';
    case Legitimate = 'legitimate';
}
