<?php

namespace App\Services\Training;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Illuminate\Support\HtmlString;

/**
 * Annotated scenario email of Module 2 (interactive condition, generation-prompts-diff.md section 2).
 *
 * Built from the validated `scenario-email` block: sanitizes the body, sorts the clues by `order`, checks that
 * every `target.text` exists in its field (cap4 4.2.5) and renders the email fields with the clues that are
 * already reachable highlighted. Clues not yet reached are never written in the markup (nothing to peek at).
 *
 * Highlights are elements with `data-clue-state="current|opened"` (a <mark>, or the <a> itself for link clues);
 * the current one also has `data-clue-current`, role="button" and tabindex="0". All model strings are escaped:
 * text fields with e(), the body through the DOM after EmailBodySanitizer.
 */
final class ScenarioEmail
{
    public const STATE_CURRENT = 'current';
    public const STATE_OPENED = 'opened';

    public const FIELD_LABELS = [
        'sender_name' => 'Sender name',
        'sender_address' => 'Sender address',
        'reply_to' => 'Reply-to',
        'subject' => 'Subject',
        'body' => 'Body',
        'link' => 'Link',
        'attachment' => 'Attachment',
    ];

    private string $body;

    /** @var list<array{id: string, number: int, label: string, explanation: string, field: string, text: string, link_id: ?string, nist_category: string}> */
    private array $clues;

    /** @var array<string, array{field: string, start?: int, length?: int, link_id?: string, attachment?: int}|null> */
    private array $locations;

    /**
     * @param array<string, mixed> $email validated `scenario-email` block (InteractiveBlocks::validate)
     */
    public function __construct(private array $email, private readonly string $firstName)
    {
        // The placeholder may appear in any text field (e.g. the subject): filled before matching the clues.
        foreach (['sender_name', 'sender_address', 'subject', 'reply_to', 'recipient_name'] as $field) {
            if (is_string($this->email[$field] ?? null)) {
                $this->email[$field] = FirstName::fillText($this->email[$field], $firstName);
            }
        }
        foreach ($this->email['attachments'] ?? [] as $i => $attachment) {
            $this->email['attachments'][$i]['filename'] = FirstName::fillText($attachment['filename'], $firstName);
        }
        $this->body = EmailBodySanitizer::sanitize($email['body_html'], $email['links'] ?? [], $firstName);

        $clues = [];
        foreach ($email['clues'] as $index => $clue) {
            $clues[] = ['_order' => (int) $clue['order'], '_index' => $index, 'clue' => $clue];
        }
        usort($clues, fn ($a, $b) => [$a['_order'], $a['_index']] <=> [$b['_order'], $b['_index']]);

        $this->clues = [];
        foreach ($clues as $i => $entry) {
            $clue = $entry['clue'];
            $this->clues[] = [
                'id' => $clue['id'],
                'number' => $i + 1,
                'label' => $clue['label'],
                'explanation' => $clue['explanation'],
                'nist_category' => $clue['nist_category'],
                'field' => $clue['target']['field'],
                'text' => FirstName::fillText($clue['target']['text'], $firstName),
                'link_id' => $clue['target']['link_id'] ?? null,
            ];
        }

        $this->locations = [];
        foreach ($this->clues as $clue) {
            $this->locations[$clue['id']] = $this->locate($clue);
        }
    }

    /** Clues in the fixed order of activation, each with its 1-based number and whether it was found. */
    public function clues(): array
    {
        return array_map(fn (array $c) => $c + ['located' => $this->locations[$c['id']] !== null], $this->clues);
    }

    /** @return list<string> ids of the clues whose target.text is not in the indicated field */
    public function unlocatedIds(): array
    {
        return array_keys(array_filter($this->locations, fn ($location) => $location === null));
    }

    /** The scenario email with the {{first_name}} placeholder filled in its text fields (body excluded). */
    public function email(): array
    {
        return $this->email;
    }

    /** Body after sanitization, without highlights (also used by the Module 3 diagram). */
    public function sanitizedBody(): string
    {
        return $this->body;
    }

    /**
     * Email fields ready to print (HtmlString already escaped, highlights included).
     *
     * @param array<string, string> $states clue id => STATE_CURRENT | STATE_OPENED; other clues are not highlighted
     * @return array{sender_name: HtmlString, sender_address: HtmlString, reply_to: ?HtmlString, recipient: string, date: ?string, subject: HtmlString, body: HtmlString, attachments: list<HtmlString>}
     */
    public function render(array $states): array
    {
        $byField = [];
        foreach ($this->clues as $clue) {
            $location = $this->locations[$clue['id']];
            $state = $states[$clue['id']] ?? null;
            if ($location === null || ! in_array($state, [self::STATE_CURRENT, self::STATE_OPENED], true)) {
                continue;
            }
            $byField[$location['field']][] = ['clue' => $clue, 'state' => $state, 'location' => $location];
        }

        $attachments = [];
        foreach (array_values($this->email['attachments'] ?? []) as $i => $attachment) {
            $marks = array_values(array_filter($byField['attachment'] ?? [], fn ($m) => $m['location']['attachment'] === $i));
            $attachments[] = self::markText($attachment['filename'], $marks);
        }

        return [
            'sender_name' => self::markText($this->email['sender_name'], $byField['sender_name'] ?? []),
            'sender_address' => self::markText($this->email['sender_address'], $byField['sender_address'] ?? []),
            'reply_to' => $this->email['reply_to'] === null ? null : self::markText($this->email['reply_to'], $byField['reply_to'] ?? []),
            'recipient' => $this->email['recipient_name'],
            'date' => self::formatDate($this->email['date']),
            'subject' => self::markText($this->email['subject'], $byField['subject'] ?? []),
            'body' => new HtmlString($this->markBody(array_merge($byField['body'] ?? [], $byField['link'] ?? []))),
            'attachments' => $attachments,
        ];
    }

    /** Where the clue target is, or null when its text is not in the indicated field. */
    private function locate(array $clue): ?array
    {
        $text = $clue['text'];
        switch ($clue['field']) {
            case 'sender_name':
            case 'sender_address':
            case 'subject':
            case 'reply_to':
                $value = $this->email[$clue['field']] ?? null;
                $range = is_string($value) ? self::find($value, $text) : null;

                return $range === null ? null : ['field' => $clue['field']] + $range;

            case 'attachment':
                foreach (array_values($this->email['attachments'] ?? []) as $i => $attachment) {
                    $range = self::find($attachment['filename'], $text);
                    if ($range !== null) {
                        return ['field' => 'attachment', 'attachment' => $i] + $range;
                    }
                }

                return null;

            case 'link':
                [$doc, $body] = HtmlFragment::load($this->body);
                if ($this->anchor($doc, $clue) !== null) {
                    return ['field' => 'link', 'link_id' => $clue['link_id']];
                }
                // Link not tagged with its id: highlight its text in the body if present.
                $range = self::find(self::bodyText($body), $text);

                return $range === null ? null : ['field' => 'body'] + $range;

            case 'body':
                [, $body] = HtmlFragment::load($this->body);
                $range = self::find(self::bodyText($body), $text);

                return $range === null ? null : ['field' => 'body'] + $range;
        }

        return null;
    }

    /** Anchor of a link clue: data-link-id first, then an anchor whose text contains target.text. */
    private function anchor(DOMDocument $doc, array $clue): ?DOMElement
    {
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//a') as $a) {
            if ($clue['link_id'] !== null && $a->getAttribute('data-link-id') === $clue['link_id']) {
                return $a;
            }
        }
        foreach ($xpath->query('//a') as $a) {
            if (self::find($a->textContent, $clue['text']) !== null) {
                return $a;
            }
        }

        return null;
    }

    /** Exact match first, then case-insensitive; character offsets. */
    private static function find(string $haystack, string $needle): ?array
    {
        $needle = trim($needle);
        if ($needle === '') {
            return null;
        }
        $start = mb_strpos($haystack, $needle);
        if ($start === false) {
            $start = mb_stripos($haystack, $needle);
        }

        return $start === false ? null : ['start' => $start, 'length' => mb_strlen($needle)];
    }

    /** @return list<DOMText> */
    private static function textNodes(DOMNode $node): array
    {
        $nodes = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $nodes[] = $child;
            } elseif ($child->hasChildNodes()) {
                array_push($nodes, ...self::textNodes($child));
            }
        }

        return $nodes;
    }

    private static function bodyText(DOMNode $body): string
    {
        return implode('', array_map(fn (DOMText $t) => $t->data, self::textNodes($body)));
    }

    /** @param list<array{clue: array, state: string, location: array}> $marks */
    private static function markText(string $value, array $marks): HtmlString
    {
        // Overlapping ranges in a header field: keep the earliest clue only.
        usort($marks, fn ($a, $b) => $a['location']['start'] <=> $b['location']['start']);
        $html = '';
        $cursor = 0;
        foreach ($marks as $mark) {
            $start = $mark['location']['start'];
            if ($start < $cursor) {
                continue;
            }
            $html .= e(mb_substr($value, $cursor, $start - $cursor));
            $html .= self::openMark($mark) . e(mb_substr($value, $start, $mark['location']['length'])) . '</mark>';
            $cursor = $start + $mark['location']['length'];
        }

        return new HtmlString($html . e(mb_substr($value, $cursor)));
    }

    /** Opening <mark> tag: attributes built only from integers and fixed strings. */
    private static function openMark(array $mark): string
    {
        $attributes = '';
        foreach (self::markAttributes($mark) as $name => $value) {
            $attributes .= ' ' . $name . '="' . e($value) . '"';
        }

        return '<mark' . $attributes . '>';
    }

    /** @return array<string, string> */
    private static function markAttributes(array $mark): array
    {
        $number = (string) $mark['clue']['number'];
        $attributes = ['data-clue' => $number, 'data-clue-state' => $mark['state']];
        if ($mark['state'] === self::STATE_CURRENT) {
            $attributes += [
                'data-clue-current' => '',
                'role' => 'button',
                'tabindex' => '0',
                'aria-label' => "Suspicious element {$number}: show the explanation",
            ];
        } else {
            $attributes['data-clue-number'] = $number;
        }

        return $attributes;
    }

    /** Body HTML with the reachable body/link clues highlighted. */
    private function markBody(array $marks): string
    {
        [$doc, $body] = HtmlFragment::load($this->body);
        foreach ($marks as $mark) {
            if ($mark['location']['field'] === 'link') {
                $a = $this->anchor($doc, $mark['clue']);
                if ($a !== null) {
                    foreach (self::markAttributes($mark) as $name => $value) {
                        $a->setAttribute($name, $value);
                    }
                    // The anchor keeps role="link" for the opened state; the current one acts as a button.
                }
                continue;
            }
            self::wrapRange($doc, $body, $mark['location']['start'], $mark['location']['length'], self::markAttributes($mark));
        }

        return HtmlFragment::innerHtml($body);
    }

    /** Wraps the characters [start, start+length) of the body text in <mark>, one per text node crossed. */
    private static function wrapRange(DOMDocument $doc, DOMNode $body, int $start, int $length, array $attributes): void
    {
        $end = $start + $length;
        $offset = 0;
        $segments = [];
        foreach (self::textNodes($body) as $node) {
            $nodeLength = mb_strlen($node->data);
            $from = max($start, $offset);
            $to = min($end, $offset + $nodeLength);
            if ($from < $to) {
                $segments[] = [$node, $from - $offset, $to - $offset];
            }
            $offset += $nodeLength;
        }

        $last = count($segments) - 1;
        foreach ($segments as $i => [$node, $from, $to]) {
            if ($to < mb_strlen($node->data)) {
                $node->splitText($to);
            }
            if ($from > 0) {
                $node = $node->splitText($from);
            }
            $mark = $doc->createElement('mark');
            foreach ($attributes as $name => $value) {
                // Number badge only once, after the last segment; one focusable target per clue.
                if ($i !== $last && in_array($name, ['data-clue-number', 'role', 'tabindex', 'aria-label'], true)) {
                    continue;
                }
                $mark->setAttribute($name, $value);
            }
            $node->parentNode->replaceChild($mark, $node);
            $mark->appendChild($node);
        }
    }

    private static function formatDate(string $date): ?string
    {
        try {
            return Carbon::parse($date)->format('D, j M Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
