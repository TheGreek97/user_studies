<?php

namespace App\Services\Tutor;

use JsonException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator as SchemaValidator;

/**
 * Validator of the tutor output (cap4 par. 4.4.5-4.4.6; port of `src/validator.ts`).
 *
 * 1. JSON parse + full JSON Schema (opis/json-schema, draft 2020-12) -> K1. The provider may receive a reduced
 *    schema (strict structured output drops pattern, uniqueItems, min/maxLength...): the full one is re-checked here.
 * 2. Code checks of the verifiable G rules:
 *    - G1  verdict formulas in dialog turns (regexes of the prompt notes)               -> K2
 *    - G2  closing message that states the opposite of the card verdict
 *    - G5  max 3 sentences / 1 question (dialog), max 5 sentences / 0 questions (closing) -> K3
 *    - G3  ids subset of the card, recognized + missing = all ids, disjoint             -> K4
 *    - G9  no card internals (ids, field names) in the message
 *    - G8  no disclosure of an unnamed item (its label, the suspicious address/domain, the real link domain)
 *    - G6  at medium (normal move) and low help: no zone hint and no quote of an unnamed item's text
 * Every check is heuristic where the rule is about wording; manual criteria K5-K7 remain in the harness.
 */
final class Validator
{
    public const LIMITS = [
        'dialogMaxSentences' => 3,
        'dialogMaxQuestions' => 1,
        'closingMaxSentences' => 5,
        'closingMaxQuestions' => 0,
    ];

    /** Regexes of the prompt notes (G1), plus the explicit forbidden examples of rule G1 in the prompt. */
    public const VERDICT_PATTERNS = [
        // Judgment on the email/sender only: "X is safe" said of an action (e.g. "hovering is safe", G10) must pass.
        "/\\b(it|this|that|they|(the|this|that)\\s+(e-?mail|message|sender|site|link|page|attachment))\\s*('s|\\s+(is|are|looks?|seems?))\\s+(like\\s+)?(a\\s+)?(phishing|scam|fake|legit(imate)?|genuine|safe|real)\\b(?!\\s+to\\b)/i",
        '/\b(correct|right|wrong)\s+(classification|answer)\b/i',
        '/\bnot\s+(a\s+)?(phishing|scam)\b/i',
        "/\\byou('re|\\s+are)\\s+(right|wrong|correct)\\s+to\\s+(classify|call|mark|flag|say|think)\\b/i",
        '/\byour\s+(answer|classification|choice)\s+is\s+(right|wrong|correct|incorrect)\b/i',
        '/\bchange\s+your\s+(answer|classification|choice|mind)\b/i',
        // Italian (not in the TS core): the tutor must answer in English, but a model that switches language must
        // not slip a verdict past G1. "è/sembra (una) truffa|phishing|legittima|autentica" and any "truffa".
        "/(?<![\\p{L}\\p{N}_])(non\\s+)?(è|e'|sembra|pare)\\s+(un'|una\\s+|un\\s+)?(phishing|truffa|legittim[aoie]|autentic[aoie]|genuin[aoie])(?![\\p{L}\\p{N}_])/i",
        '/(?<![\p{L}\p{N}_])truff[ae](?![\p{L}\p{N}_])/i',
    ];

    /** Closing turn (G2): the email/it stated with the polarity opposite to the card. */
    private const SAYS_PHISHING = "/\\b(it|this\\s+(email|message)|the\\s+(email|message))\\s+(is|was|'s)\\s+(a\\s+)?(phishing|scam|fake)\\b/i";

    private const SAYS_LEGIT =
        "/\\b(it|this\\s+(email|message)|the\\s+(email|message))\\s+(is|was|'s)\\s+(a\\s+)?(legit(imate)?|genuine|safe|real)\\b|\\b(is|was)\\s+not\\s+(a\\s+)?(phishing|scam)\\b/i";

    /** Card internals that must never reach the user (G9; test T10). */
    private const INTERNALS =
        '/\b(is_phishing|legit_signals|recognized_clue_ids|missing_clue_ids|focus_clue_id|fallback_hints|closing_rule|static_feedback|suspicious_looking_features|typical_errors|attacker_reason|nist_category)\b|\b[cs]\d{1,2}\b/';

    /** Zone-pointing phrases (Tabella 13: a zone is allowed only at high help or for the medium "don't know" turn). */
    public const ZONE_FAMILIES = [
        'sender' => "/after the @|\\bthe @|sender'?s?\\s+(e-?mail\\s+)?(address|domain)|\\bfrom\\s+address\\b|address\\s+of\\s+the\\s+sender/i",
        'hover' => '/\bhover|\bmouse\s+over|real\s+address|where\s+(the|this|that)\s+(link|button)\s+(leads|goes|points|takes)/i',
        'reply_to' => '/reply-?\s?to/i',
        'attachment' => '/\battach(ed|ment|ments)?\b|\.html\b/i',
        'link' => '/\b(link|button|url)s?\b/i',
    ];

    /** @var array<string, Schema> compiled schemas by content hash (TS caches by object identity) */
    private static array $compiled = [];

    private static ?SchemaValidator $schemaValidator = null;

    private static function schemaValidator(): SchemaValidator
    {
        if (self::$schemaValidator === null) {
            self::$schemaValidator = new SchemaValidator();
            self::$schemaValidator->setMaxErrors(50); // ajv allErrors
        }

        return self::$schemaValidator;
    }

    /** opis rejects a relative root `$id`; it is resolved against a placeholder base (no `$ref` in the schema). */
    private static function compile(array $schema): Schema
    {
        $key = md5(json_encode($schema, JSON_THROW_ON_ERROR));
        if (! isset(self::$compiled[$key])) {
            if (isset($schema['$id']) && is_string($schema['$id']) && ! preg_match('/^[a-z][a-z0-9+.-]*:/i', $schema['$id'])) {
                $schema['$id'] = 'https://tutor.invalid/'.ltrim($schema['$id'], '/');
            }
            self::$compiled[$key] = self::schemaValidator()->loader()->loadObjectSchema(Helper::toJSON($schema));
        }

        return self::$compiled[$key];
    }

    /**
     * Tolerates one surrounding ``` fence; anything else that is not JSON fails K1.
     * Objects decode to stdClass, so `{}` and `[]` stay distinct for the schema.
     *
     * @throws JsonException
     */
    public static function parseModelJson(string $raw): mixed
    {
        $trimmed = Text::trim($raw);
        $fenced = preg_match('/^```(?:json)?\s*\n([\s\S]*?)\n```\z/', $trimmed, $m) === 1;

        return json_decode($fenced ? $m[1] : $trimmed, false, 512, JSON_THROW_ON_ERROR);
    }

    /** WHATWG `new URL(url).hostname`, or null when the URL has no host. */
    private static function hostOf(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }

    /**
     * Header content that, typed by the user, shows they already looked at a zone (G6). Link and hover zones
     * take no content evidence: a link host can equal the sender domain, and naming the domain is not hovering.
     *
     * @return array<string, list<string>>
     */
    private static function zoneEvidence(array $card): array
    {
        $address = fn (?string $a) => $a === null || $a === '' ? [] : [$a, self::domainOf($a)];

        return [
            'sender' => $address($card['sender_address'] ?? null),
            'reply_to' => $address($card['reply_to'] ?? null),
            'attachment' => array_values(array_filter(array_map(fn (array $a) => $a['filename'] ?? '', $card['attachments'] ?? []))),
        ];
    }

    /** Domain part of an e-mail address (`a@b.com` -> `b.com`), or the text itself. */
    private static function domainOf(string $text): string
    {
        $at = strrpos($text, '@');

        return $at !== false ? substr($text, $at + 1) : $text;
    }

    /** Patterns above are JS regex literals (no `u` flag): compiled by JsRegex. */
    private static function firstMatch(string $re, string $text): ?string
    {
        return JsRegex::first($re, $text);
    }

    /** @return list<Violation> */
    public static function checkRules(TutorOutput $out, ValidationContext $ctx): array
    {
        $v = [];
        $msg = $out->message;
        $card = $ctx->card;
        $ids = Materials::cardIds($card);
        $dialog = $ctx->turnType === TurnType::Dialog;

        // G3 / K4: ids exist in the card, partition of all ids.
        $allOut = [...$out->recognized_clue_ids, ...$out->missing_clue_ids];
        foreach ([...$allOut, ...($out->focus_clue_id !== null ? [$out->focus_clue_id] : [])] as $id) {
            if (! in_array($id, $ids, true)) {
                $v[] = new Violation('G3', 'unknown_id', "id \"{$id}\" is not in card {$card['id']}");
            }
        }
        $overlap = array_values(array_filter($out->recognized_clue_ids, fn ($id) => in_array($id, $out->missing_clue_ids, true)));
        if ($overlap !== []) {
            $v[] = new Violation('G3', 'ids_overlap', 'both recognized and missing: '.implode(', ', $overlap));
        }
        $notInPartition = array_values(array_filter(array_unique($ids), fn ($id) => ! in_array($id, $allOut, true)));
        if ($notInPartition !== []) {
            $v[] = new Violation('G3', 'ids_incomplete', 'recognized + missing lacks: '.implode(', ', $notInPartition));
        }

        // G5 / K3: length and questions.
        $turn = $ctx->turnType->value;
        $maxS = $dialog ? self::LIMITS['dialogMaxSentences'] : self::LIMITS['closingMaxSentences'];
        $maxQ = $dialog ? self::LIMITS['dialogMaxQuestions'] : self::LIMITS['closingMaxQuestions'];
        $s = Text::sentenceCount($msg);
        $q = Text::questionCount($msg);
        if ($s > $maxS) {
            $v[] = new Violation('G5', 'too_many_sentences', "{$s} sentences > {$maxS} ({$turn})");
        }
        if ($q > $maxQ) {
            $v[] = new Violation('G5', 'too_many_questions', "{$q} question marks > {$maxQ} ({$turn})");
        }

        // G1 / K2 (dialog) and G2 (closing).
        if ($dialog) {
            foreach (self::VERDICT_PATTERNS as $re) {
                $m = self::firstMatch($re, $msg);
                if ($m !== null) {
                    $v[] = new Violation('G1', 'verdict_wording', "verdict formula before closing: \"{$m}\"");
                }
            }
        } else {
            $opposite = ! empty($card['is_phishing']) ? self::SAYS_LEGIT : self::SAYS_PHISHING;
            $m = self::firstMatch($opposite, $msg);
            if ($m !== null) {
                $v[] = new Violation('G2', 'verdict_contradicts_card', "closing states the opposite verdict: \"{$m}\"");
            }
        }

        // G9: no internals.
        $internal = self::firstMatch(self::INTERNALS, $msg);
        if ($internal !== null) {
            $v[] = new Violation('G9', 'card_internals', "message exposes card internals: \"{$internal}\"");
        }

        if (! $dialog) {
            return $v;
        }

        // G8: disclosure of an item the user has not named (dialog only; the closing turn is meant to disclose).
        // Only claims supported by the user's messages count (ClueEvidence): a prompt-injected "all recognized"
        // must not unlock the disclosure of items the user never named.
        $recognized = [...$ctx->recognizedBefore, ...ClueEvidence::filter($card, $out->recognized_clue_ids, $ctx->userTexts)['accepted']];
        $userSaid = fn (string $t) => array_filter($ctx->userTexts, fn (string $u) => Text::includesCi($u, $t)) !== [];
        $unnamed = array_values(array_filter(Materials::cardItems($card), fn (array $i) => ! in_array($i['id'], $recognized, true)));
        foreach ($unnamed as $item) {
            $target = $item['target'];
            $secrets = [$item['label']];
            if (in_array($target['field'], ['sender_address', 'reply_to'], true)) {
                array_push($secrets, $target['text'], self::domainOf($target['text']));
            }
            if ($target['field'] === 'link') {
                $link = null;
                foreach ($card['links'] ?? [] as $l) {
                    if ($l['id'] === ($target['link_id'] ?? null) || $l['text'] === $target['text']) {
                        $link = $l;
                        break;
                    }
                }
                $host = $link !== null ? self::hostOf($link['href_real']) : null;
                if ($host !== null) {
                    $secrets[] = $host;
                }
            }
            foreach ($secrets as $secret) {
                if (mb_strlen($secret, 'UTF-8') >= 6 && Text::includesCi($msg, $secret) && ! $userSaid($secret)) {
                    $v[] = new Violation('G8', 'discloses_unnamed_item', "reveals {$item['id']} (\"{$secret}\") before the user named it");
                    break;
                }
            }
        }

        // G6: zone hints only at high help, or at medium for the one-turn "don't know" move (Tabella 13).
        $dontKnow = $ctx->dontKnow || $out->user_said_dont_know;
        $zoneAllowed = $ctx->helpLevel === HelpLevel::High || ($ctx->helpLevel === HelpLevel::Medium && $dontKnow);
        if (! $zoneAllowed) {
            $level = $ctx->helpLevel->value;
            $evidence = self::zoneEvidence($card);
            foreach (self::ZONE_FAMILIES as $family => $re) {
                $m = self::firstMatch($re, $msg);
                // The user named the zone either with zone words or by typing its content (e.g. the sender domain):
                // in both cases the tutor only echoes what the user already looked at.
                $userMentioned = array_filter($ctx->userTexts, fn (string $u) => JsRegex::test($re, $u)
                    || array_filter($evidence[$family] ?? [], fn (string $e) => Text::includesCi($u, $e)) !== []) !== [];
                if ($m !== null && ! $userMentioned) {
                    $v[] = new Violation('G6', 'zone_hint', "zone \"{$family}\" (\"{$m}\") at {$level} help, not mentioned by the user");
                }
            }
            foreach ($unnamed as $item) {
                $t = $item['target']['text'];
                if (mb_strlen($t, 'UTF-8') >= 6 && Text::includesCi($msg, $t) && ! $userSaid($t)) {
                    $v[] = new Violation('G6', 'quotes_unnamed_target', "quotes the text of {$item['id']} at {$level} help");
                }
            }
        }

        return $v;
    }

    /**
     * JSON Schema errors of already-decoded data (empty list = valid). Reused by RulesCommentValidator (Module 5).
     *
     * @return list<string>
     */
    public static function schemaErrors(mixed $data, array $schema): array
    {
        $result = self::schemaValidator()->validate($data, self::compile($schema));

        return $result->isValid() ? [] : array_values((new ErrorFormatter())->formatFlat($result->error()));
    }

    /** Full validation of a raw model reply. */
    public static function validateTutorReply(string $raw, array $schema, ValidationContext $ctx): ValidationResult
    {
        try {
            $data = self::parseModelJson($raw);
        } catch (JsonException $e) {
            return new ValidationResult(false, null, [new Violation('K1', 'not_json', $e->getMessage())]);
        }
        $result = self::schemaValidator()->validate($data, self::compile($schema));
        if (! $result->isValid()) {
            $errors = (new ErrorFormatter())->formatFlat($result->error());

            return new ValidationResult(false, null, [new Violation('K1', 'schema', implode('; ', $errors))]);
        }
        $output = TutorOutput::fromData($data);
        $violations = self::checkRules($output, $ctx);

        return new ValidationResult($violations === [], $output, $violations);
    }
}
