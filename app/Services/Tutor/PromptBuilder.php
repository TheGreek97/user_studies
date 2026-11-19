<?php

namespace App\Services\Tutor;

use InvalidArgumentException;

/**
 * Prompt builder (cap4 par. 4.4.7; port of `src/prompt-builder.ts`): fills the system prompt of
 * `tutor-system-prompt.md` with the email card, the Module 3 guidelines, the help level, the style card and
 * the dialog history.
 *
 * Injection hardening (prompt notes, "Escape"): card and history are serialised as JSON, and placeholders are
 * replaced in a single pass over the template, so `{{...}}` written by the user or inside the email is never expanded.
 */
final class PromptBuilder
{
    public const PLACEHOLDERS = [
        'exercise_number',
        'exercise_total',
        'turn_type',
        'user_reply_count',
        'max_user_replies',
        'email_json',
        'module3_guidelines',
        'help_level',
        'user_profile_style',
        'dialog_history',
        'previous_missed_clues',
    ];

    /** Instruction sent as the user message: the whole context lives in the system message, as the prompt file states. */
    public const TURN_INSTRUCTION = 'Write the tutor reply for this turn as ONE JSON object, following block 9.';

    /**
     * Extracts the text to send to the model: the part between the two `---8<---` lines,
     * without the surrounding ```text fence.
     */
    public static function extractSystemTemplate(string $markdown): string
    {
        $parts = preg_split('/^---8<---\s*$/m', $markdown);
        if (count($parts) < 3) {
            throw new InvalidArgumentException('tutor-system-prompt.md: the ---8<--- markers were not found');
        }
        $section = $parts[1];
        $fenced = preg_match('/```[a-z]*\n([\s\S]*?)\n```/', $section, $m) === 1;

        return Text::trim($fenced ? $m[1] : $section);
    }

    /** Card as sent to the model: everything except `static_feedback` (placeholder table of the prompt). */
    public static function cardForModel(array $card): array
    {
        unset($card['static_feedback']);

        return $card;
    }

    /** @param list<array{text: string}> $guidelines */
    public static function formatGuidelines(array $guidelines): string
    {
        if ($guidelines === []) {
            return '(none)';
        }

        return implode("\n", array_map(fn (array $g) => '- '.$g['text'], $guidelines));
    }

    /** @param list<string> $items */
    public static function formatList(array $items): string
    {
        return $items === [] ? '(none)' : implode("\n", array_map(fn (string $i) => '- '.$i, $items));
    }

    /**
     * `JSON.stringify(value, null, 2)`: 2-space indent, no escaping of "/" or of non-ASCII characters.
     * PHP pretty-prints with 4 spaces; leading indentation is halved (strings never contain a raw newline).
     */
    public static function jsonStringify(mixed $value): string
    {
        $json = json_encode(
            $value,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR,
        );

        return preg_replace_callback('/^(?: {4})+/m', fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json);
    }

    /** @return array<string, string> */
    public static function placeholderValues(PromptContext $ctx): array
    {
        return [
            'exercise_number' => (string) $ctx->exerciseNumber,
            'exercise_total' => (string) $ctx->exerciseTotal,
            'turn_type' => $ctx->turnType->value,
            'user_reply_count' => (string) $ctx->userReplyCount,
            'max_user_replies' => (string) $ctx->maxUserReplies,
            'email_json' => self::jsonStringify(self::cardForModel($ctx->card)),
            'module3_guidelines' => self::formatGuidelines($ctx->module3Guidelines),
            'help_level' => $ctx->helpLevel->value,
            'user_profile_style' => $ctx->styleCard !== [] ? implode("\n", $ctx->styleCard) : '(neutral, plain and friendly)',
            'dialog_history' => self::jsonStringify(array_values($ctx->history)),
            'previous_missed_clues' => self::formatList($ctx->previousMissedClues),
        ];
    }

    /** Single-pass substitution: unknown placeholders (e.g. `{{first_name}}`) are left untouched on purpose. */
    public static function fillTemplate(string $template, array $values): string
    {
        return preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            fn (array $m) => array_key_exists($m[1], $values) ? (string) $values[$m[1]] : $m[0],
            $template,
        );
    }

    public static function buildPrompt(string $template, PromptContext $ctx): BuiltPrompt
    {
        return new BuiltPrompt(self::fillTemplate($template, self::placeholderValues($ctx)), self::TURN_INSTRUCTION);
    }
}
