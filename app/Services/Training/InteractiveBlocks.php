<?php

namespace App\Services\Training;

use DateTimeImmutable;
use Throwable;

/**
 * Structured data returned by the model for the "interactive" condition
 * (prototipo/materiali/generation-prompts-diff.md, "Convenzione per i dati strutturati").
 *
 * The model writes the data inside the section HTML as
 *     <script type="application/json" data-role="NAME">{ ... }</script>
 * extract() removes every such block from the HTML (they must never reach the browser as markup),
 * decodes the ones required for the section and validates their required fields.
 * A missing, duplicated, malformed or incomplete required block raises InteractiveBlockException,
 * which the job treats as a retryable generation failure.
 *
 * Only structural requirements are enforced (fields, types, enumerations, unique ids); soft
 * content constraints of the prompts (word limits, number of guidelines) are not, so that a
 * slightly long answer does not cost a whole new generation.
 */
class InteractiveBlocks
{
    public const SCENARIO_HEADER = 'scenario-header';
    public const SCENARIO_EMAIL = 'scenario-email';
    public const DEFENSE_GUIDELINES = 'defense-guidelines';

    /** Required block roles per generated section; sections not listed carry no data. */
    public const REQUIRED_BY_SECTION = [
        'introduction' => [self::SCENARIO_HEADER],
        'scenario' => [self::SCENARIO_EMAIL],
        'defense_strategies' => [self::DEFENSE_GUIDELINES],
    ];

    public const NIST_CATEGORIES = [
        'Error', 'Technical indicator', 'Visual presentation indicator', 'Language and content', 'Common tactic',
    ];

    public const TARGET_FIELDS = [
        'sender_name', 'sender_address', 'reply_to', 'subject', 'body', 'link', 'attachment',
    ];

    public const EMAIL_PARTS = [
        'sender', 'subject', 'tone', 'link', 'attachment', 'request', 'greeting', 'signature', 'none',
    ];

    /** Opening <script ...> tag, its content and the closing tag. */
    private const SCRIPT_PATTERN = '#<script\b([^>]*)>(.*?)</script\s*>#is';

    /**
     * @param string        $html  section HTML as returned by the model
     * @param list<string>  $roles required roles for this section
     * @return array{html: string, blocks: array<string, mixed>} HTML without data blocks + decoded blocks by role
     * @throws InteractiveBlockException
     */
    public static function extract(string $html, array $roles): array
    {
        $found = [];
        $stripped = preg_replace_callback(self::SCRIPT_PATTERN, function (array $m) use (&$found) {
            $role = self::dataRole($m[1]);
            if ($role === null) {
                return $m[0];  // not a data block: left untouched
            }
            $found[$role][] = $m[2];
            return '';
        }, $html);

        $blocks = [];
        foreach ($roles as $role) {
            $raw = $found[$role] ?? [];
            if (count($raw) === 0) {
                throw new InteractiveBlockException("Missing JSON block data-role=\"$role\"");
            }
            if (count($raw) > 1) {
                throw new InteractiveBlockException("Duplicated JSON block data-role=\"$role\"");
            }
            try {
                $data = json_decode(trim($raw[0]), true, 64, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                throw new InteractiveBlockException("Malformed JSON in block data-role=\"$role\": " . $e->getMessage());
            }
            $blocks[$role] = self::validate($role, $data);
        }

        return ['html' => trim($stripped), 'blocks' => $blocks];
    }

    /**
     * data-role of a `<script type="application/json">` tag, null for any other script.
     */
    private static function dataRole(string $attributes): ?string
    {
        if (!preg_match('/\btype\s*=\s*(["\'])\s*application\/json\s*\1/i', $attributes)) {
            return null;
        }
        if (!preg_match('/\bdata-role\s*=\s*(["\'])([^"\']+)\1/i', $attributes, $m)) {
            return null;
        }
        return trim($m[2]);
    }

    /**
     * @throws InteractiveBlockException
     */
    public static function validate(string $role, mixed $data): array
    {
        return match ($role) {
            self::SCENARIO_HEADER => self::validateHeader($data),
            self::SCENARIO_EMAIL => self::validateEmail($data),
            self::DEFENSE_GUIDELINES => self::validateGuidelines($data),
            default => throw new InteractiveBlockException("Unknown JSON block data-role=\"$role\""),
        };
    }

    private static function validateHeader(mixed $data): array
    {
        $where = self::SCENARIO_HEADER;
        self::requireObject($data, $where);
        foreach (['scenario_context', 'sender_name', 'sender_address', 'subject', 'first_line'] as $field) {
            self::requireString($data, $field, $where);
        }
        return $data;
    }

    private static function validateEmail(mixed $data): array
    {
        $where = self::SCENARIO_EMAIL;
        self::requireObject($data, $where);
        foreach (['sender_name', 'sender_address', 'recipient_name', 'subject', 'date', 'body_html'] as $field) {
            self::requireString($data, $field, $where);
        }
        $data['reply_to'] = $data['reply_to'] ?? null;
        if ($data['reply_to'] !== null && !is_string($data['reply_to'])) {
            self::fail($where, '"reply_to" must be a string or null');
        }
        try {
            new DateTimeImmutable($data['date']);
        } catch (Throwable) {
            self::fail($where, '"date" is not a valid date');
        }

        $linkIds = [];
        self::requireList($data, 'links', $where);
        foreach ($data['links'] as $i => $link) {
            self::requireObject($link, "$where.links[$i]");
            foreach (['id', 'text', 'href_real'] as $field) {
                self::requireString($link, $field, "$where.links[$i]");
            }
            $linkIds[] = $link['id'];
        }
        self::requireUnique($linkIds, "$where.links");

        $data['attachments'] = $data['attachments'] ?? [];
        self::requireList($data, 'attachments', $where);
        foreach ($data['attachments'] as $i => $attachment) {
            self::requireObject($attachment, "$where.attachments[$i]");
            self::requireString($attachment, 'filename', "$where.attachments[$i]");
        }

        self::requireList($data, 'clues', $where);
        if (count($data['clues']) === 0) {
            self::fail($where, '"clues" is empty');
        }
        $clueIds = [];
        foreach ($data['clues'] as $i => $clue) {
            $at = "$where.clues[$i]";
            self::requireObject($clue, $at);
            foreach (['id', 'nist_category', 'label', 'explanation'] as $field) {
                self::requireString($clue, $field, $at);
            }
            self::requireEnum($clue['nist_category'], self::NIST_CATEGORIES, "$at.nist_category");
            if (!isset($clue['order']) || filter_var($clue['order'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                self::fail($at, '"order" must be a positive integer');
            }
            self::requireObject($clue['target'] ?? null, "$at.target");
            self::requireString($clue['target'], 'field', "$at.target");
            self::requireEnum($clue['target']['field'], self::TARGET_FIELDS, "$at.target.field");
            self::requireString($clue['target'], 'text', "$at.target");
            if ($clue['target']['field'] === 'link') {
                self::requireString($clue['target'], 'link_id', "$at.target");
                self::requireEnum($clue['target']['link_id'], $linkIds, "$at.target.link_id");
            }
            $clueIds[] = $clue['id'];
        }
        self::requireUnique($clueIds, "$where.clues");

        return $data;
    }

    private static function validateGuidelines(mixed $data): array
    {
        $where = self::DEFENSE_GUIDELINES;
        if (!is_array($data) || !array_is_list($data) || count($data) === 0) {
            self::fail($where, 'must be a non-empty array');
        }
        $ids = [];
        foreach ($data as $i => $item) {
            $at = "{$where}[$i]";
            self::requireObject($item, $at);
            foreach (['id', 'text', 'email_part'] as $field) {
                self::requireString($item, $field, $at);
            }
            self::requireEnum($item['email_part'], self::EMAIL_PARTS, "$at.email_part");
            $ids[] = $item['id'];
        }
        self::requireUnique($ids, $where);
        return $data;
    }

    private static function requireObject(mixed $value, string $where): void
    {
        if (!is_array($value) || (count($value) > 0 && array_is_list($value))) {
            self::fail($where, 'must be a JSON object');
        }
    }

    private static function requireString(array $data, string $field, string $where): void
    {
        if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
            self::fail($where, "\"$field\" must be a non-empty string");
        }
    }

    private static function requireList(array $data, string $field, string $where): void
    {
        if (!isset($data[$field]) || !is_array($data[$field]) || !array_is_list($data[$field])) {
            self::fail($where, "\"$field\" must be an array");
        }
    }

    private static function requireEnum(string $value, array $allowed, string $where): void
    {
        if (!in_array($value, $allowed, true)) {
            self::fail($where, "unexpected value \"$value\"");
        }
    }

    private static function requireUnique(array $ids, string $where): void
    {
        if (count($ids) !== count(array_unique($ids))) {
            self::fail($where, 'ids must be unique');
        }
    }

    private static function fail(string $where, string $message): never
    {
        throw new InteractiveBlockException("Invalid JSON block $where: $message");
    }
}
