<?php

namespace App\Services\Training;

/**
 * The {{first_name}} placeholder of the "interactive" condition: the model and the email bank
 * write it instead of the participant's name, which never reaches the LLM provider (cap4 4.2.4).
 * It is replaced only when the page is rendered.
 */
final class FirstName
{
    public const PLACEHOLDER = '{{first_name}}';

    /**
     * Replaces the placeholder in HTML with the HTML-escaped name. Must run on the final HTML
     * (after any entity decoding), otherwise the escaping would be undone.
     */
    public static function fillHtml(string $html, string $name): string
    {
        return str_replace(self::PLACEHOLDER, e($name), $html);
    }

    /** Replaces the placeholder in plain text; the caller escapes the result when printing it. */
    public static function fillText(string $text, string $name): string
    {
        return str_replace(self::PLACEHOLDER, $name, $text);
    }
}
