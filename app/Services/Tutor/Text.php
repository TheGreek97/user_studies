<?php

namespace App\Services\Tutor;

/**
 * Deterministic text heuristics used by the platform, not by the model (port of `src/text.ts`).
 * They are intentionally coarse, as cap4 par. 4.4.6 says of the verdict check: they block evident cases only.
 *
 * Regex porting rule (see README): JS regexes WITH the `u` flag are written with `/u`; regexes WITHOUT it keep
 * their JS literal and go through JsRegex, which keeps `\b \w \d` ASCII and `\s` Unicode as in JS.
 */
final class Text
{
    private const MINIMAL_REPLIES = [
        'ok', 'okay', 'k', 'yes', 'no', 'yeah', 'yep', 'nope', 'sure', 'fine', 'right', 'agreed', 'i agree',
        'ok thanks', 'yes it is', 'no it is not', 'maybe', 'si', 'sì', 'va bene',
    ];

    public const DONT_KNOW =
        "/\\b(i\\s+)?(don'?t|do\\s+not|dont)\\s+know\\b|\\bidk\\b|\\bno\\s+idea\\b|\\bnot\\s+sure\\b|\\bi'?m\\s+stuck\\b|\\bdunno\\b|\\bnon\\s+(lo\\s+)?so\\b/i";

    public const ANSWER_REQUEST =
        '/\bjust\s+tell\s+me\b|\btell\s+me\s+(the\s+answer|if|whether)\b|\bis\s+it\s+(phishing|legit|legitimate|a\s+scam|fake|safe)\b|\b(can|may|could)\s+i\s+skip\b|\bskip\s+(this|it)\b|\bgive\s+me\s+the\s+answer\b|\blet\s+me\s+go\b/i';

    /** Valid UTF-8 for the `/u` regexes (a JS string can never be invalid UTF-8). */
    private static function utf8(string $text): string
    {
        return mb_check_encoding($text, 'UTF-8') ? $text : mb_scrub($text, 'UTF-8');
    }

    /** `String.prototype.trim`: Unicode whitespace and BOM, not only ASCII as PHP `trim()`. */
    public static function trim(string $text): string
    {
        return preg_replace('/^[\s\x{FEFF}]+|[\s\x{FEFF}]+$/u', '', self::utf8($text));
    }

    /** `toLowerCase()` + `includes()`. */
    public static function includesCi(string $haystack, string $needle): bool
    {
        return str_contains(mb_strtolower($haystack, 'UTF-8'), mb_strtolower($needle, 'UTF-8'));
    }

    private static function hasLetterOrDigit(string $text): bool
    {
        return preg_match('/[\p{L}\p{N}]/u', $text) === 1;
    }

    public static function wordCount(string $text): int
    {
        $words = preg_split('/\s+/u', self::trim($text));

        return count(array_filter($words, fn (string $w) => self::hasLetterOrDigit($w)));
    }

    /**
     * Sentence count: a sentence ends with . ! ? or an ellipsis followed by whitespace and an upper-case letter,
     * a digit or a quote, or by the end of the text. "e.g. the link" and "airbnb.com" are therefore not split.
     */
    public static function sentenceCount(string $text): int
    {
        $parts = preg_split('/(?<=[.!?…])["\'”’)\]]*\s+(?=["\'“‘(]?[\p{Lu}\p{N}])/u', self::trim($text));

        return count(array_filter($parts, fn (string $p) => self::hasLetterOrDigit($p)));
    }

    public static function questionCount(string $text): int
    {
        return substr_count($text, '?');
    }

    private static function normalise(string $text): string
    {
        $t = mb_strtolower(self::utf8($text), 'UTF-8');
        $t = preg_replace("/[^\\p{L}\\p{N}\\s']/u", ' ', $t);
        $t = preg_replace('/\s+/u', ' ', $t);

        return self::trim($t);
    }

    /** "I don't know" and similar (Tabella 13, column "non so"). */
    public static function isDontKnow(string $text): bool
    {
        return JsRegex::test(self::DONT_KNOW, $text);
    }

    /**
     * Minimal reply (cap4 par. 4.4.4): yes/no/ok or a single word. "I don't know" is not minimal: it has its own move.
     * Repeating the tutor's words is left to the model (`minimal_reply`), which the platform ORs with this heuristic.
     */
    public static function isMinimalReply(string $text): bool
    {
        if (self::isDontKnow($text)) {
            return false;
        }
        $n = self::normalise($text);
        if ($n === '') {
            return true;
        }

        return in_array($n, self::MINIMAL_REPLIES, true) || self::wordCount($n) <= 1;
    }

    /** Request for the verdict or to skip (cap4 par. 4.4.5); the platform ORs it with the model flag in the log. */
    public static function isAnswerRequest(string $text): bool
    {
        return JsRegex::test(self::ANSWER_REQUEST, $text);
    }
}
