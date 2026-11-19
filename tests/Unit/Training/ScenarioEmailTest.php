<?php

namespace Tests\Unit\Training;

use App\Services\Training\EmailBodySanitizer;
use App\Services\Training\ScenarioEmail;
use App\Services\Training\ScenarioSection;
use PHPUnit\Framework\TestCase;

/** Module 2 helpers: body sanitizer, annotated email, split of the generated section at the decision point. */
class ScenarioEmailTest extends TestCase
{
    private const LINKS = [['id' => 'l1', 'text' => 'Keep my password', 'href_real' => 'https://evil.example/reset']];

    public function test_sanitizer_keeps_formatting_and_strips_active_content(): void
    {
        $html = '<p style="color:red" onclick="steal()" class="x" id="y">Hi <b>{{first_name}}</b><script>alert(1)</script></p>'
            . '<style>p{}</style><iframe src="https://x"></iframe><img src=x onerror=alert(1)><svg><a xlink:href="javascript:alert(1)"/></svg>'
            . '<form action="https://x"><input name="pw"><button>Go</button>Text in form</form>'
            . '<custom-tag data-x="1">kept text</custom-tag><!-- comment -->'
            . '<a href="javascript:alert(1)" target="_blank" data-link-id="l1" onmouseover="x()">Keep my password</a>'
            . '<a href="https://plain.example" data-link-id="bad id\"">Other</a>'
            . '<table><tr><td colspan="2" width="9">cell</td></tr></table>';
        $out = EmailBodySanitizer::sanitize($html, self::LINKS, '<i>Al&ice</i>');

        foreach (['<script', 'alert(1)', '<style', '<iframe', '<img', '<svg', 'onerror', 'onclick', 'onmouseover', 'style=', 'class=',
                     'id="y"', '<form', '<input', '<button', 'custom-tag', 'comment', ' href=', 'target=', 'javascript:', 'width='] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $out, $forbidden);
        }
        $this->assertStringContainsString('<p>Hi <b>&lt;i&gt;Al&amp;ice&lt;/i&gt;</b></p>', $out);
        $this->assertStringContainsString('Text in form', $out);
        $this->assertStringContainsString('kept text', $out);
        // Links never navigate: real destination from links[] (not the model href), focusable, shown as a tooltip.
        $this->assertStringContainsString('<a data-link-id="l1" role="link" tabindex="0" data-href="https://evil.example/reset" title="https://evil.example/reset">Keep my password</a>', $out);
        $this->assertStringContainsString('<a role="link" tabindex="0" data-href="https://plain.example" title="https://plain.example">Other</a>', $out);
        // No destination at all: no empty tooltip.
        $this->assertSame('<a role="link" tabindex="0" data-href="">Nowhere</a>', EmailBodySanitizer::sanitize('<a>Nowhere</a>', [], 'Al'));
        $this->assertStringContainsString('<td colspan="2">cell</td>', $out);
    }

    private static function email(array $overrides = []): array
    {
        return array_replace(InteractiveFixtures::email(), [
            'subject' => 'Urgent: {{first_name}}, verify now',
            'body_html' => '<p>Dear {{first_name}},</p><p>Your account will be <b>suspended</b> today.</p>'
                . '<p><a data-link-id="l1" href="https://company-helpdesk.net/reset">Keep my password</a></p>',
            'attachments' => [['filename' => 'invoice.pdf.exe']],
            'clues' => [
                ['id' => 'c3', 'nist_category' => 'Technical indicator', 'target' => ['field' => 'link', 'text' => 'Keep my password', 'link_id' => 'l1'],
                    'label' => 'Link', 'explanation' => 'Goes elsewhere.', 'order' => 3],
                ['id' => 'c1', 'nist_category' => 'Technical indicator', 'target' => ['field' => 'sender_address', 'text' => 'company-helpdesk.net'],
                    'label' => 'Domain', 'explanation' => 'Not the company <domain>.', 'order' => 1],
                ['id' => 'c2', 'nist_category' => 'Language and content', 'target' => ['field' => 'body', 'text' => 'will be suspended today'],
                    'label' => 'Threat', 'explanation' => 'Urgency.', 'order' => 2],
                ['id' => 'c4', 'nist_category' => 'Common tactic', 'target' => ['field' => 'subject', 'text' => 'not in the subject'],
                    'label' => 'Missing', 'explanation' => 'Shown without highlight.', 'order' => 4],
                ['id' => 'c5', 'nist_category' => 'Technical indicator', 'target' => ['field' => 'attachment', 'text' => '.pdf.exe'],
                    'label' => 'Attachment', 'explanation' => 'Double extension.', 'order' => 5],
            ],
        ], $overrides);
    }

    public function test_clues_are_sorted_by_order_and_unlocated_targets_are_reported(): void
    {
        $email = new ScenarioEmail(self::email(), 'Alice');

        $this->assertSame(['c1', 'c2', 'c3', 'c4', 'c5'], array_column($email->clues(), 'id'));
        $this->assertSame([1, 2, 3, 4, 5], array_column($email->clues(), 'number'));
        $this->assertSame([true, true, true, false, true], array_column($email->clues(), 'located'));
        $this->assertSame(['c4'], $email->unlocatedIds());
    }

    public function test_only_reached_clues_are_highlighted(): void
    {
        $email = new ScenarioEmail(self::email(), 'Alice');

        $none = $email->render([]);
        $this->assertStringNotContainsString('<mark', $none['sender_address'] . $none['body'] . $none['subject']);
        $this->assertStringNotContainsString('data-clue', (string) $none['body']);
        $this->assertSame('Urgent: Alice, verify now', (string) $none['subject']);
        $this->assertSame('Alice', $none['recipient']);

        $fields = $email->render(['c1' => 'opened', 'c2' => 'current']);
        $this->assertSame('it-support@<mark data-clue="1" data-clue-state="opened" data-clue-number="1">company-helpdesk.net</mark>', (string) $fields['sender_address']);
        // Body clue crossing a <b>: one <mark> per text node, focus/number only on the last one.
        $this->assertStringContainsString(
            'Your account <mark data-clue="2" data-clue-state="current" data-clue-current="">will be </mark><b><mark data-clue="2" data-clue-state="current" data-clue-current="">suspended</mark></b>'
            . '<mark data-clue="2" data-clue-state="current" data-clue-current="" role="button" tabindex="0" aria-label="Suspicious element 2: show the explanation"> today</mark>.</p>',
            (string) $fields['body']
        );
        $this->assertStringNotContainsString('data-clue="3"', (string) $fields['body']);

        $fields = $email->render(['c1' => 'opened', 'c2' => 'opened', 'c3' => 'current', 'c5' => 'opened']);
        $this->assertStringContainsString('<a data-link-id="l1" role="button" tabindex="0" data-href="https://company-helpdesk.net/reset" title="https://company-helpdesk.net/reset" data-clue="3" data-clue-state="current" data-clue-current="" aria-label="Suspicious element 3: show the explanation">', (string) $fields['body']);
        $this->assertSame('invoice<mark data-clue="5" data-clue-state="opened" data-clue-number="5">.pdf.exe</mark>', (string) $fields['attachments'][0]);
    }

    public function test_model_strings_are_escaped_in_header_fields(): void
    {
        $email = new ScenarioEmail(self::email([
            'sender_name' => '<script>x()</script> Desk',
            'clues' => [['id' => 'c1', 'nist_category' => 'Error', 'target' => ['field' => 'sender_name', 'text' => 'Desk'],
                'label' => 'L', 'explanation' => 'E', 'order' => 1]],
        ]), 'Alice');

        $this->assertSame('&lt;script&gt;x()&lt;/script&gt; <mark data-clue="1" data-clue-state="opened" data-clue-number="1">Desk</mark>',
            (string) $email->render(['c1' => 'opened'])['sender_name']);
    }

    public function test_section_is_split_at_the_decision_point_with_balanced_parts(): void
    {
        $html = '<html><body><div id="wrap" class="mb-10"><p>Intro</p>'
            . '<div data-role="decision-point"><p>What would you do?</p>'
            . '<label><input type="radio" name="d" value="click"> Click the link</label>'
            . '<input type="radio" id="r2" name="d" value="report"><label for="r2">Report it</label>'
            . '<input type="radio" name="d"> Delete it<br><button>Submit</button></div>'
            . '<div id="feedback"></div><script>var a = 1;</script></div><p>Tail</p></body></html>';

        $parts = ScenarioSection::split($html);

        $this->assertSame('<div id="wrap" class="mb-10"><p>Intro</p></div>', $parts['before']);
        $this->assertStringStartsWith('<div data-role="decision-point"><p>What would you do?</p>', $parts['decision']);
        $this->assertSame('<div class="mb-10"><div id="feedback"></div><script>var a = 1;</script></div><p>Tail</p>', $parts['after']);
        $this->assertSame([
            ['value' => 'click', 'label' => 'Click the link'],
            ['value' => 'report', 'label' => 'Report it'],
            ['value' => 'on', 'label' => 'Delete it'],
        ], $parts['options']);
    }

    public function test_section_without_decision_point_is_kept_whole(): void
    {
        $parts = ScenarioSection::split('<div><p>Only text</p></div>');

        $this->assertSame(['before' => '<div><p>Only text</p></div>', 'decision' => null, 'after' => '', 'options' => []], $parts);
    }
}
