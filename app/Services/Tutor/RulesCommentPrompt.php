<?php

namespace App\Services\Tutor;

use RuntimeException;

/**
 * Prompt 5b (Module 5, cap4 4.3.5): the tutor's ONE comment on the personal rules written by the user.
 *
 * The template (resources/tutor/rules-comment-prompt.md) is the verbatim text of the spec. Its placeholders are
 * built here, server-side only:
 * - {{missed_clues}}: for every closed Module 4 email, the `main` items not in the server-accepted `recognized`
 *   list at close (= missing_clue_ids), with id, label and position (target) taken from the solution card, never
 *   from a model reply. Item ids are made unique across emails as "<email id>:<item id>" (cards reuse c1, c2...);
 * - {{module3_guidelines}}: interactive_data.defense_guidelines, formatted as for the M4 tutor;
 * - {{style_card}}: the style card (empty today: same neutral default as the M4 tutor);
 * - {{personal_rules}}: the user's rules as a JSON array of strings, so quotes, newlines and "#" headings typed by
 *   the user stay inside string literals (data, not prompt structure).
 * Substitution is single-pass (PromptBuilder::fillTemplate): a "{{...}}" typed by the user is never expanded.
 */
final class RulesCommentPrompt
{
    public const PROMPT = 'rules-comment-prompt.md';

    public const OUTPUT_SCHEMA = 'rules-comment-output-schema.json';

    public const PLACEHOLDERS = ['missed_clues', 'module3_guidelines', 'style_card', 'personal_rules'];

    /** User message: the whole context is in the system message, as for the M4 tutor. */
    public const INSTRUCTION = "Write the comment on the user's rules as ONE JSON object, following block 7.";

    /** Same value the M4 tutor uses for an empty style card (PromptBuilder::placeholderValues). */
    public const NEUTRAL_STYLE = '(neutral, plain and friendly)';

    /**
     * Main items missed in Module 4, per closed email in sequence order (emails with nothing missed are omitted).
     *
     * @param array{emails: list<array<string, mixed>>} $bank
     * @param list<array<string, mixed>> $closedStates tutor_sessions.closed_states (DialogSession::toArray())
     * @return list<array{exercise: int, email_id: string, email_type: string, service: ?string, missed_main_clues: list<array{id: string, label: string, position: array<string, mixed>}>}>
     */
    public static function missedClues(array $bank, array $closedStates): array
    {
        $exercises = [];
        foreach ($closedStates as $state) {
            if (! is_array($state) || ! isset($state['card_id'])) {
                continue;
            }
            $card = Materials::findCard($bank, (string) $state['card_id']);
            $recognized = is_array($state['recognized'] ?? null) ? $state['recognized'] : [];
            $missed = [];
            foreach (Materials::cardItems($card) as $item) {
                if (! empty($item['main']) && ! in_array($item['id'], $recognized, true)) {
                    $missed[] = [
                        'id' => self::clueId($card['id'], $item['id']),
                        'label' => (string) $item['label'],
                        'position' => (array) ($item['target'] ?? []),
                    ];
                }
            }
            if ($missed === []) {
                continue;
            }
            $exercises[] = [
                'exercise' => (int) ($state['position'] ?? count($exercises) + 1),
                'email_id' => (string) $card['id'],
                'email_type' => ! empty($card['is_phishing']) ? 'phishing' : 'legitimate',
                // Service the email imitates (phishing) or comes from (legitimate).
                'service' => $card['impersonates'] ?? $card['service'] ?? null,
                'missed_main_clues' => $missed,
            ];
        }
        usort($exercises, fn (array $a, array $b) => $a['exercise'] <=> $b['exercise']);

        return $exercises;
    }

    public static function clueId(string $emailId, string $itemId): string
    {
        return $emailId.':'.$itemId;
    }

    /** @return list<string> ids of every missed clue (the only ids the model may return, G3). */
    public static function missedIds(array $missedClues): array
    {
        $ids = [];
        foreach ($missedClues as $exercise) {
            foreach ($exercise['missed_main_clues'] as $clue) {
                $ids[] = $clue['id'];
            }
        }

        return $ids;
    }

    /** @return array<string, string> */
    public static function placeholderValues(array $missedClues, array $guidelines, array $styleCard, array $rules): array
    {
        return [
            'missed_clues' => $missedClues === [] ? '(none)' : PromptBuilder::jsonStringify($missedClues),
            'module3_guidelines' => PromptBuilder::formatGuidelines($guidelines),
            'style_card' => $styleCard !== [] ? implode("\n", $styleCard) : self::NEUTRAL_STYLE,
            'personal_rules' => PromptBuilder::jsonStringify(array_values($rules)),
        ];
    }

    /** @param list<string> $rules */
    public static function build(string $template, array $missedClues, array $guidelines, array $styleCard, array $rules): BuiltPrompt
    {
        $values = self::placeholderValues($missedClues, $guidelines, $styleCard, $rules);

        return new BuiltPrompt(PromptBuilder::fillTemplate($template, $values), self::INSTRUCTION);
    }

    /** Template text between the ---8<--- markers; falls back to the bundled copy if the materials dir lacks it. */
    public static function loadTemplate(?Materials $materials = null): string
    {
        return PromptBuilder::extractSystemTemplate(self::read(self::PROMPT, $materials));
    }

    /** @return array<string, mixed> */
    public static function loadSchema(?Materials $materials = null): array
    {
        return json_decode(self::read(self::OUTPUT_SCHEMA, $materials), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function read(string $name, ?Materials $materials): string
    {
        foreach (array_unique([($materials ?? new Materials())->path($name), dirname(__DIR__, 3).'/resources/tutor/'.$name]) as $path) {
            if (is_file($path)) {
                $text = file_get_contents($path);
                if ($text !== false) {
                    return $text;
                }
            }
        }
        throw new RuntimeException("Module 5 material not found: {$name}");
    }
}
