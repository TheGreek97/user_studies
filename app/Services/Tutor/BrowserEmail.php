<?php

namespace App\Services\Tutor;

use App\Services\Training\FirstName;

/**
 * Projection of an email card onto `_meta.browser_safe_fields` (cap4 par. 4.2.3): the only data of a card that may
 * reach the browser while its dialog is open. Supports top-level fields and `list[].field` paths.
 */
final class BrowserEmail
{
    /**
     * @param array<string, mixed> $card
     * @param list<string> $safeFields
     * @return array<string, mixed>
     */
    public static function project(array $card, array $safeFields): array
    {
        $out = [];
        foreach ($safeFields as $path) {
            if (preg_match('/^(\w+)\[\]\.(\w+)$/', $path, $m) === 1) {
                [, $list, $field] = $m;
                foreach (array_values($card[$list] ?? []) as $i => $item) {
                    if (is_array($item) && array_key_exists($field, $item)) {
                        $out[$list][$i][$field] = $item[$field];
                    }
                }

                continue;
            }
            if (array_key_exists($path, $card)) {
                $out[$path] = $card[$path];
            }
        }

        return $out;
    }

    /**
     * Replaces `{{first_name}}` (never sent to the LLM, cap4 par. 4.2.4) in every string of the projection.
     * `body_html` is rendered unescaped, so the name is HTML-escaped there; the other fields are escaped by Blade.
     */
    public static function personalise(array $email, string $firstName): array
    {
        array_walk_recursive($email, function (&$value, $key) use ($firstName) {
            if (is_string($value)) {
                $value = $key === 'body_html' ? FirstName::fillHtml($value, $firstName) : FirstName::fillText($value, $firstName);
            }
        });

        return $email;
    }
}
