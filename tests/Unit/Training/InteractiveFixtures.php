<?php

namespace Tests\Unit\Training;

/**
 * Valid model answers for the "interactive" condition, shared by the parser and job tests.
 */
final class InteractiveFixtures
{
    public static function header(): array
    {
        return [
            'scenario_context' => 'You are at your desk on Monday morning, checking the inbox before a meeting.',
            'sender_name' => 'IT Service Desk',
            'sender_address' => 'it-support@company-helpdesk.net',
            'subject' => 'Action required: password expires today',
            'first_line' => 'Your mailbox password expires in 2 hours.',
        ];
    }

    public static function email(): array
    {
        return [
            'sender_name' => 'IT Service Desk',
            'sender_address' => 'it-support@company-helpdesk.net',
            'reply_to' => null,
            'recipient_name' => '{{first_name}}',
            'subject' => 'Action required: password expires today',
            'date' => '2026-11-10T09:12:00+01:00',
            'body_html' => '<p>Your mailbox password expires in 2 hours.</p><p><a data-link-id="l1" href="https://company-helpdesk.net/reset">Keep my password</a></p>',
            'links' => [['id' => 'l1', 'text' => 'Keep my password', 'href_real' => 'https://company-helpdesk.net/reset']],
            'attachments' => [],
            'clues' => [
                [
                    'id' => 'c1', 'nist_category' => 'Technical indicator',
                    'target' => ['field' => 'sender_address', 'text' => 'company-helpdesk.net'],
                    'label' => 'External sender domain', 'explanation' => 'The domain is not the company one.', 'order' => 1,
                ],
                [
                    'id' => 'c2', 'nist_category' => 'Technical indicator',
                    'target' => ['field' => 'link', 'text' => 'Keep my password', 'link_id' => 'l1'],
                    'label' => 'Link to unknown site', 'explanation' => 'The link points outside the company.', 'order' => 2,
                ],
            ],
        ];
    }

    public static function guidelines(): array
    {
        return [
            ['id' => 'g1', 'text' => 'Check the domain after the @ in the sender address.', 'email_part' => 'sender'],
            ['id' => 'g2', 'text' => 'Hover over links and read the real address.', 'email_part' => 'link'],
            ['id' => 'g3', 'text' => 'Distrust deadlines that push you to act now.', 'email_part' => 'tone'],
            ['id' => 'g4', 'text' => 'Recognize a genuine email by its official domain.', 'email_part' => 'none'],
        ];
    }

    public static function block(string $role, mixed $data): string
    {
        return '<script type="application/json" data-role="' . $role . '">'
            . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    /** Model answers for the 5 sections, in ProcessTraining::SECTIONS order. */
    public static function answers(): array
    {
        return [
            '<div><p>Hello {{first_name}}, welcome.</p>' . self::block('scenario-header', self::header()) . '</div>',
            '<div><p>Imagine the scene.</p>' . self::block('scenario-email', self::email())
                . '<div data-role="decision-point"><input type="radio" name="d"> Click</div></div>',
            '<div><p>Why the strategies work.</p>' . self::block('defense-guidelines', self::guidelines()) . '</div>',
            '<div><p>Now some exercises.</p></div>',
            '<div><p>Recap.</p><div data-role="thanks">Thank you, {{first_name}}.</div></div>',
        ];
    }
}
