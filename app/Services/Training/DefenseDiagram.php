<?php

namespace App\Services\Training;

use App\Models\Training;

/**
 * Module 3 diagram (interactive condition, generation-prompts-diff.md "Specifica dello schema M3"): a fixed SVG
 * template (640 wide, about 420 tall) with a thumbnail of the scenario email on the left and the defense guidelines on
 * the right; each guideline has a numbered circle on the zone of the email it refers to (`email_part`) and a line
 * to its text. `none` (or a part missing in the email, e.g. no attachment) goes to a "General" box. Guidelines are
 * numbered in the order of their zones from top to bottom, so the connectors never cross.
 * No icons, no animations. This class only computes geometry and plain strings; the Blade view escapes them.
 *
 * The numbered list of the guidelines is always produced as the text alternative (and the only output when the
 * scenario email is missing or invalid).
 */
final class DefenseDiagram
{
    public const WIDTH = 640;
    /** The viewBox is 640 wide and as tall as its content (about 420 for a full email with 5-6 guidelines). */
    public const MIN_HEIGHT = 200;

    public const PART_LABELS = [
        'sender' => 'Sender', 'subject' => 'Subject', 'tone' => 'Tone', 'link' => 'Link', 'attachment' => 'Attachment',
        'request' => 'Request', 'greeting' => 'Greeting', 'signature' => 'Signature', 'none' => 'General',
    ];

    private const CARD_X = 10;
    private const CARD_W = 300;
    private const ITEM_X = 340;
    private const TEXT_X = 362;
    private const CHARS_PER_LINE = 40;
    private const MAX_LINES = 5;
    private const LINE_H = 15;
    /** Thumbnail text stops before the numbered markers on the right edge of the card (x ~ 286). */
    private const BODY_CHARS = 40;

    private const TONE = '/\b(urgent|urgently|immediately|immediate|within|expire[sd]?|expiring|suspend(ed)?|deadline|today|now|hours?|final|warning|locked|asap|last chance|limited)\b/i';
    private const REQUEST = '/\b(password|credentials?|log ?in|sign ?in|verify|confirm|update|details|card|bank|pay(ment)?|click|download|open|reply|send|provide|enter)\b/i';
    private const GREETING = '/^(dear|hi|hello|hey|good (morning|afternoon|evening)|greetings)\b/i';

    /**
     * @return array{items: list<array{number: int, text: string, part: string, part_label: string}>, svg: ?array}|null
     *         null when the training has no valid guidelines
     */
    public static function forTraining(Training $training): ?array
    {
        $guidelines = $training->interactiveBlock(InteractiveBlocks::DEFENSE_GUIDELINES);
        if ($guidelines === null) {
            return null;
        }
        $email = $training->interactiveBlock(InteractiveBlocks::SCENARIO_EMAIL);
        $name = (string) $training->user?->name;

        return self::build($guidelines, $email === null ? null : new ScenarioEmail($email, $name));
    }

    /**
     * With the email, the guidelines are numbered top to bottom by the y of their zone (ties: array order; "General"
     * last), so the connectors to the stacked texts never cross; the list uses the same numbering. Without the email
     * they keep the array order.
     */
    public static function build(array $guidelines, ?ScenarioEmail $view): array
    {
        $items = [];
        foreach (array_values($guidelines) as $i => $guideline) {
            $items[] = [
                'number' => $i + 1,
                'text' => $guideline['text'],
                'part' => $guideline['email_part'],
                'part_label' => self::PART_LABELS[$guideline['email_part']] ?? 'General',
            ];
        }
        if ($view === null) {
            return ['items' => $items, 'svg' => null];
        }
        $svg = self::svg($items, $view);

        return ['items' => $svg['ordered'], 'svg' => array_diff_key($svg, ['ordered' => true])];
    }

    private static function svg(array $items, ScenarioEmail $view): array
    {
        $email = $view->email();  // placeholder already filled
        // Lines that are only a link are drawn as the link row, not as body text.
        $linkTexts = array_map(fn ($l) => mb_strtolower(trim($l['text'])), $email['links'] ?? []);
        $lines = array_values(array_filter(self::bodyLines($view->sanitizedBody()), fn ($l) => ! in_array(mb_strtolower($l), $linkTexts, true)));
        $greeting = isset($lines[0]) && preg_match(self::GREETING, $lines[0]) === 1 ? array_shift($lines) : null;
        $signature = count($lines) > 1 ? array_pop($lines) : null;
        $body = array_slice($lines, 0, $greeting === null ? 4 : 3);

        // Thumbnail rows, top to bottom; zones record the y of each email part present.
        $rows = [];
        $zones = [];
        $y = 22;
        $rows[] = ['kind' => 'avatar', 'x' => self::CARD_X + 24, 'y' => $y + 16, 'text' => mb_strtoupper(mb_substr(trim($email['sender_name']), 0, 1)) ?: '?'];
        $rows[] = ['kind' => 'strong', 'x' => self::CARD_X + 46, 'y' => $y + 12, 'text' => self::cut($email['sender_name'], 30)];
        $rows[] = ['kind' => 'muted', 'x' => self::CARD_X + 46, 'y' => $y + 28, 'text' => self::cut($email['sender_address'], 36)];
        $zones['sender'] = $y + 16;
        $y += 48;
        $rows[] = ['kind' => 'strong', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut('Subject: ' . $email['subject'], 34)];
        $zones['subject'] = $y;
        $y += 16;
        $rows[] = ['kind' => 'rule', 'x' => self::CARD_X + 10, 'y' => $y, 'text' => ''];
        $y += 22;
        if ($greeting !== null) {
            $rows[] = ['kind' => 'body', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut($greeting, self::BODY_CHARS)];
            $zones['greeting'] = $y;
            $y += 20;
        }
        $bodyY = [];
        foreach ($body as $line) {
            $rows[] = ['kind' => 'body', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut($line, self::BODY_CHARS)];
            $bodyY[] = $y;
            $y += 20;
        }
        if ($bodyY !== []) {
            $zones['tone'] = $bodyY[self::strongestLine($body, self::TONE) ?? 0];
            $zones['request'] = $bodyY[self::matchLine($body, self::REQUEST, true) ?? count($bodyY) - 1];
        }
        if (! empty($email['links'])) {
            $y += 4;
            $rows[] = ['kind' => 'link', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut($email['links'][0]['text'], 34)];
            $zones['link'] = $y;
            $y += 26;
        }
        if (! empty($email['attachments'])) {
            $rows[] = ['kind' => 'attachment', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut('Attachment: ' . $email['attachments'][0]['filename'], self::BODY_CHARS)];
            $zones['attachment'] = $y;
            $y += 22;
        }
        if ($signature !== null) {
            $rows[] = ['kind' => 'muted', 'x' => self::CARD_X + 14, 'y' => $y + 4, 'text' => self::cut($signature, self::BODY_CHARS)];
            $zones['signature'] = $y;
            $y += 20;
        }
        $card = ['x' => self::CARD_X, 'y' => 10, 'width' => self::CARD_W, 'height' => $y - 4];

        // "General" box under the card, only when some guideline has no zone.
        $general = null;
        foreach ($items as $item) {
            if (! isset($zones[$item['part']])) {
                $general = ['x' => self::CARD_X, 'y' => $y + 16, 'width' => self::CARD_W, 'height' => 44, 'label' => 'General'];
                $zones['_general'] = $y + 46;
                break;
            }
        }

        // Numbering: top to bottom by zone y (stable sort keeps the array order on ties), "General" last.
        foreach ($items as $i => $item) {
            $items[$i]['zone'] = isset($zones[$item['part']]) ? $item['part'] : '_general';
            $items[$i]['index'] = $i;
        }
        usort($items, fn ($a, $b) => [$a['zone'] === '_general', $zones[$a['zone']], $a['index']]
            <=> [$b['zone'] === '_general', $zones[$b['zone']], $b['index']]);
        foreach ($items as $i => $item) {
            $items[$i]['number'] = $i + 1;
        }

        // Guideline texts on the right, stacked in that order, each with a badge.
        $placed = [];
        $itemY = 26;
        foreach ($items as $item) {
            $wrapped = self::wrap($item['text']);
            $placed[] = [
                'number' => $item['number'],
                'zone' => $item['zone'],
                'badge' => ['x' => self::ITEM_X + 10, 'y' => $itemY],
                'lines' => array_map(fn ($text, $i) => ['x' => self::TEXT_X, 'y' => $itemY + 4 + $i * self::LINE_H, 'text' => $text], $wrapped, array_keys($wrapped)),
            ];
            $itemY += max(1, count($wrapped)) * self::LINE_H + 22;
        }

        // Numbered circles on the zones, at the right edge of the card (of the General box), several per zone side by
        // side leftwards. In a zone the badge farthest from it (above or below) gets the leftmost circle: two
        // connectors leaving the same row then never cross.
        $byZone = [];
        foreach ($placed as $k => $item) {
            $byZone[$item['zone']][] = $k;
        }
        foreach ($byZone as $zone => $keys) {
            $y = $zones[$zone];
            usort($keys, fn ($a, $b) => [abs($placed[$b]['badge']['y'] - $y), $a] <=> [abs($placed[$a]['badge']['y'] - $y), $b]);
            $n = count($keys);
            foreach ($keys as $slot => $k) {
                $placed[$k]['marker'] = ['x' => self::CARD_X + self::CARD_W - 14 - 24 * ($n - 1 - $slot), 'y' => $y];
            }
        }
        $placed = array_map(fn ($p) => ['number' => $p['number'], 'marker' => $p['marker'], 'badge' => $p['badge'], 'lines' => $p['lines']], $placed);
        $ordered = array_map(fn ($i) => array_intersect_key($i, ['number' => 0, 'text' => 0, 'part' => 0, 'part_label' => 0]), $items);

        $height = (int) max(self::MIN_HEIGHT, $itemY, ($general['y'] ?? 0) + ($general['height'] ?? 0) + 10, $card['height'] + 20);

        return ['width' => self::WIDTH, 'height' => $height, 'card' => $card, 'rows' => $rows, 'general' => $general, 'items' => $placed, 'ordered' => $ordered];
    }

    /** Paragraph-level lines of the body text (block boundaries become line breaks). */
    private static function bodyLines(string $html): array
    {
        $text = preg_replace('#<(br|/p|/div|/li|/tr|/h[1-6]|/blockquote|hr)\b[^>]*>#i', "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_map(fn ($l) => trim(preg_replace('/\s+/u', ' ', $l)), explode("\n", $text));

        return array_values(array_filter($lines, fn ($l) => $l !== ''));
    }

    private static function matchLine(array $lines, string $pattern, bool $last): ?int
    {
        $found = null;
        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line) === 1) {
                if (! $last) {
                    return $i;
                }
                $found = $i;
            }
        }

        return $found;
    }

    /**
     * Index of the line with the most matches of $pattern (ties: the first), or null without matches. Used for the
     * tone zone: a deadline ("within 12 hours") outweighs a passing "today" in an earlier line.
     */
    private static function strongestLine(array $lines, string $pattern): ?int
    {
        $best = null;
        $bestCount = 0;
        foreach ($lines as $i => $line) {
            $count = preg_match_all($pattern, $line);
            if ($count > $bestCount) {
                $best = $i;
                $bestCount = $count;
            }
        }

        return $best;
    }

    private static function cut(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return mb_strlen($text) > $max ? rtrim(mb_substr($text, 0, $max - 1)) . '…' : $text;
    }

    /** @return list<string> word-wrapped lines, at most MAX_LINES (the last one ellipsised) */
    private static function wrap(string $text): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $word = self::cut($word, self::CHARS_PER_LINE);
            if ($line !== '' && mb_strlen($line . ' ' . $word) > self::CHARS_PER_LINE) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $line === '' ? $word : $line . ' ' . $word;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        if (count($lines) > self::MAX_LINES) {
            $lines = array_slice($lines, 0, self::MAX_LINES);
            $lines[self::MAX_LINES - 1] = self::cut($lines[self::MAX_LINES - 1] . ' …', self::CHARS_PER_LINE);
        }

        return $lines;
    }
}
