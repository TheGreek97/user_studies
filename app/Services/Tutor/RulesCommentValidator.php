<?php

namespace App\Services\Tutor;

use JsonException;

/**
 * Validator of the Module 5 comment (prompt 5b). Same layering as the M4 Validator:
 * 1. JSON parse (one ``` fence tolerated) + full JSON Schema (rules-comment-output-schema.json)  -> K1
 * 2. Code checks of the verifiable rules:
 *    - G5  `message` at most 3 sentences and at most 1 question                                    -> K3
 *    - G3  covered_clue_ids subset of the missed ids; focus_clue_id one of them (and not covered) or null
 *    - G9  no internals in the message (item/email ids, JSON field names)
 * "Never write a rule for the user" (G8) is about wording: it stays a manual criterion of the log review.
 */
final class RulesCommentValidator
{
    public const MAX_SENTENCES = 3;

    public const MAX_QUESTIONS = 1;

    private const INTERNALS =
        '/\bm4-[a-z0-9-]+|\b[cs]\d{1,2}\b|\b(covered_clue_ids|focus_clue_id|generic_rule|missed_clues|missed_main_clues|personal_rules|email_id|email_type|sender_address|sender_name|reply_to|body_html|is_phishing|nist_category|nist_cue)\b/';

    /**
     * @param list<string> $missedIds ids of {{missed_clues}} (RulesCommentPrompt::missedIds)
     * @return array{ok: bool, output: ?array{message: string, covered_clue_ids: list<string>, focus_clue_id: ?string, generic_rule: bool}, violations: list<Violation>}
     */
    public static function validate(string $raw, array $schema, array $missedIds): array
    {
        try {
            $data = Validator::parseModelJson($raw);
        } catch (JsonException $e) {
            return self::fail([new Violation('K1', 'not_json', $e->getMessage())]);
        }
        $errors = Validator::schemaErrors($data, $schema);
        if ($errors !== []) {
            return self::fail([new Violation('K1', 'schema', implode('; ', $errors))]);
        }
        $output = [
            'message' => (string) $data->message,
            'covered_clue_ids' => array_values(array_map('strval', $data->covered_clue_ids)),
            'focus_clue_id' => $data->focus_clue_id,
            'generic_rule' => (bool) $data->generic_rule,
        ];
        $violations = self::checkRules($output, $missedIds);

        return ['ok' => $violations === [], 'output' => $output, 'violations' => $violations];
    }

    /** @return list<Violation> */
    public static function checkRules(array $output, array $missedIds): array
    {
        $v = [];
        $msg = $output['message'];
        $sentences = Text::sentenceCount($msg);
        if ($sentences > self::MAX_SENTENCES) {
            $v[] = new Violation('G5', 'too_many_sentences', "{$sentences} sentences > ".self::MAX_SENTENCES);
        }
        $questions = Text::questionCount($msg);
        if ($questions > self::MAX_QUESTIONS) {
            $v[] = new Violation('G5', 'too_many_questions', "{$questions} question marks > ".self::MAX_QUESTIONS);
        }
        $unknown = array_values(array_diff($output['covered_clue_ids'], $missedIds));
        if ($unknown !== []) {
            $v[] = new Violation('G3', 'covered_not_missed', implode(', ', $unknown));
        }
        $focus = $output['focus_clue_id'];
        if ($focus !== null && ! in_array($focus, $missedIds, true)) {
            $v[] = new Violation('G3', 'focus_not_missed', $focus);
        } elseif ($focus !== null && in_array($focus, $output['covered_clue_ids'], true)) {
            $v[] = new Violation('G3', 'focus_covered', $focus);
        }
        if (preg_match(self::INTERNALS, $msg, $m) === 1) {
            $v[] = new Violation('G9', 'internals', $m[0]);
        }

        return $v;
    }

    private static function fail(array $violations): array
    {
        return ['ok' => false, 'output' => null, 'violations' => $violations];
    }
}
