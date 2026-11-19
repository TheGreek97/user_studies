<?php

namespace App\Services\Tutor;

/**
 * Turns the source of a JS regex literal WITHOUT the `u` flag into an equivalent PCRE pattern.
 *
 * PHP needs `/u` to treat the subject as UTF-8, but `/u` also enables PCRE2_UCP, which makes `\b \w \d \s`
 * Unicode-aware. In JS (no `u` flag) `\b \w \d` are ASCII-only while `\s` matches Unicode whitespace
 * (e.g. NBSP). The shorthand classes are therefore rewritten with the exact JS sets before compiling with `/u`.
 * JS regexes that already had the `u` flag are written directly with `/u` and do not go through here.
 */
final class JsRegex
{
    /** JS `\s` (WhiteSpace + LineTerminator), as members of a character class. */
    private const WS = '\t\n\x{0B}\f\r \x{A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}';

    private const W = 'A-Za-z0-9_';

    private const ASCII_B = '(?:(?<=[A-Za-z0-9_])(?![A-Za-z0-9_])|(?<![A-Za-z0-9_])(?=[A-Za-z0-9_]))';

    /** @var array<string, string> */
    private static array $cache = [];

    /**
     * @param string $literal JS regex literal `/source/flags` (flags among g, i, m, s; `g` is dropped:
     *                        preg_match finds the first match, like `String.match` without `g`)
     */
    public static function compile(string $literal): string
    {
        if (isset(self::$cache[$literal])) {
            return self::$cache[$literal];
        }
        $end = strrpos($literal, '/');
        $source = substr($literal, 1, $end - 1);
        $flags = str_replace(['g', 'u'], '', substr($literal, $end + 1));

        return self::$cache[$literal] = '/'.self::translate($source).'/u'.$flags;
    }

    /** `re.test(text)` */
    public static function test(string $literal, string $text): bool
    {
        return preg_match(self::compile($literal), self::utf8($text)) === 1;
    }

    /** `text.match(re)?.[0]` */
    public static function first(string $literal, string $text): ?string
    {
        return preg_match(self::compile($literal), self::utf8($text), $m) === 1 ? $m[0] : null;
    }

    /** `/u` fails on invalid UTF-8; a JS string is always well formed. */
    private static function utf8(string $text): string
    {
        return mb_check_encoding($text, 'UTF-8') ? $text : mb_scrub($text, 'UTF-8');
    }

    private static function translate(string $src): string
    {
        $out = '';
        $inClass = false;
        $n = strlen($src);
        for ($i = 0; $i < $n; $i++) {
            $c = $src[$i];
            if ($c === '\\' && $i + 1 < $n) {
                $e = $src[++$i];
                $out .= match (true) {
                    $e === 's' => $inClass ? self::WS : '['.self::WS.']',
                    $e === 'S' && ! $inClass => '[^'.self::WS.']',
                    $e === 'w' => $inClass ? self::W : '['.self::W.']',
                    $e === 'W' && ! $inClass => '[^'.self::W.']',
                    $e === 'd' => $inClass ? '0-9' : '[0-9]',
                    $e === 'D' && ! $inClass => '[^0-9]',
                    $e === 'b' && ! $inClass => self::ASCII_B,
                    $e === 'B' && ! $inClass => '(?!'.self::ASCII_B.')',
                    default => '\\'.$e,
                };

                continue;
            }
            if ($c === '[' && ! $inClass) {
                $inClass = true;
            } elseif ($c === ']' && $inClass) {
                $inClass = false;
            }
            $out .= $c;
        }

        return $out;
    }
}
