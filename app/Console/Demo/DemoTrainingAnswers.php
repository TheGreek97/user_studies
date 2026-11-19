<?php

namespace App\Console\Demo;

/**
 * Hand-written model answers for the 5 sections of an "interactive" training, used only by `demo:interactive`
 * to show the platform locally without calling a provider. They follow the generation prompts of
 * ProcessTraining (data blocks, {{first_name}} placeholder, decision point, thanks) and go through the real
 * job pipeline (InteractiveBlocks parsing + Module 4 sequence draw), so the stored training is shaped
 * exactly like a generated one.
 */
final class DemoTrainingAnswers
{
    public static function header(): array
    {
        return [
            'scenario_context' => 'It is Tuesday evening and you are on the sofa checking your phone, waiting for a parcel you ordered online last week.',
            'sender_name' => 'ParcelGo Delivery',
            'sender_address' => 'notifications@parcelgo-delivery-it.com',
            'subject' => 'Your parcel is on hold: confirm the delivery fee',
            'first_line' => 'We tried to deliver your parcel today but a customs fee of €1.99 is still unpaid.',
        ];
    }

    public static function email(): array
    {
        $href = 'https://parcelgo-delivery-it.com/pay?ref=IT4471';

        return [
            'sender_name' => 'ParcelGo Delivery',
            'sender_address' => 'notifications@parcelgo-delivery-it.com',
            'reply_to' => 'support@parcel-help-center.net',
            'recipient_name' => '{{first_name}}',
            'subject' => 'Your parcel is on hold: confirm the delivery fee',
            'date' => '2026-11-10T19:42:00+01:00',
            'body_html' => '<p>Dear customer,</p>'
                . '<p>We tried to deliver your parcel today but a customs fee of €1.99 is still unpaid.</p>'
                . '<p>If the fee is not paid within 12 hours, your parcel will be returned to the sender.</p>'
                . '<p><a data-link-id="l1" href="' . $href . '">Pay and reschedule delivery</a></p>'
                . '<p>To complete the payment you will need your card number, expiry date and CVV.</p>'
                . '<p>ParcelGo Delivery Customer Service</p>',
            'links' => [['id' => 'l1', 'text' => 'Pay and reschedule delivery', 'href_real' => $href]],
            'attachments' => [],
            'clues' => [
                [
                    'id' => 'c1', 'nist_category' => 'Technical indicator',
                    'target' => ['field' => 'sender_address', 'text' => 'notifications@parcelgo-delivery-it.com'],
                    'label' => 'Look-alike sender domain',
                    'explanation' => 'The name says ParcelGo, but the domain after the @ is not the official one: anyone can register a name that looks similar.',
                    'order' => 1,
                ],
                [
                    'id' => 'c2', 'nist_category' => 'Common tactic',
                    'target' => ['field' => 'body', 'text' => 'your parcel will be returned to the sender'],
                    'label' => 'Short deadline with a threat',
                    'explanation' => 'A 12-hour deadline is meant to make you act before you think the message through.',
                    'order' => 2,
                ],
                [
                    'id' => 'c3', 'nist_category' => 'Technical indicator',
                    'target' => ['field' => 'link', 'text' => 'Pay and reschedule delivery', 'link_id' => 'l1'],
                    'label' => 'Payment link to unknown site',
                    'explanation' => 'Hovering over the button shows a site that does not belong to the courier.',
                    'order' => 3,
                ],
                [
                    'id' => 'c4', 'nist_category' => 'Language and content',
                    'target' => ['field' => 'body', 'text' => 'your card number, expiry date and CVV'],
                    'label' => 'Request for full card data',
                    'explanation' => 'A tiny fee is the bait: the real goal is your full card data, CVV included.',
                    'order' => 4,
                ],
            ],
        ];
    }

    public static function guidelines(): array
    {
        return [
            ['id' => 'g1', 'text' => 'Read the domain after the @ in the sender address, not only the display name.', 'email_part' => 'sender'],
            ['id' => 'g2', 'text' => 'Hover over every link or button and read the real address before clicking.', 'email_part' => 'link'],
            ['id' => 'g3', 'text' => 'Slow down when a message threatens a loss within a few hours.', 'email_part' => 'tone'],
            ['id' => 'g4', 'text' => 'Never type card data or passwords on a page reached from an email.', 'email_part' => 'request'],
            ['id' => 'g5', 'text' => 'Recognize a genuine email by its official domain and by checking the service from its own app.', 'email_part' => 'none'],
        ];
    }

    private static function block(string $role, array $data): string
    {
        return '<script type="application/json" data-role="' . $role . '">'
            . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    /** Model answers for the 5 sections, in ProcessTraining::SECTIONS order. */
    public static function answers(): array
    {
        return [
            '<div><h2>Welcome, {{first_name}}</h2>'
            . '<p>Phishing emails pretend to come from someone you trust to make you click a link, open a file or share data. '
            . 'A single careless click can cost money, accounts or access to your workplace.</p>'
            . '<p>Your profile suggests that you trust familiar brands and react quickly to urgent requests: attackers count on exactly these habits. '
            . 'In this training you will see a realistic scenario, learn practical defense strategies, practise on a few emails with a tutor and finish with a short recap.</p>'
            . self::block('scenario-header', self::header()) . '</div>',

            '<div><h2>A parcel on hold</h2>'
            . '<p>{{first_name}}, you ordered a gift online last week and the tracking page still says "in transit". '
            . 'This evening your phone shows a new email from what looks like the courier.</p>'
            . self::block('scenario-email', self::email())
            . '<div data-role="decision-point"><p><strong>What would you do now?</strong></p>'
            . '<label><input type="radio" name="decision" value="pay"> Pay the €1.99 fee from the button</label><br>'
            . '<label><input type="radio" name="decision" value="reply"> Reply to ask for more details</label><br>'
            . '<label><input type="radio" name="decision" value="check"> Open the courier app or website myself and check the tracking</label></div>'
            . '<p>Paying from the button hands your card data to the attacker, and replying confirms that your address is active. '
            . 'Checking the tracking from the official app, typed or opened by you, is the only safe path.</p></div>',

            '<div><h2>Defense strategies</h2>'
            . '<p>Each strategy below is linked to the part of the email where it applies. The display name of a sender is free text: anyone can write "ParcelGo" there, '
            . 'while the domain after the @ has to be registered and cannot be the real one. Links work the same way: the text you read and the address you reach are two different things, '
            . 'and only hovering reveals the second.</p>'
            . '<p>Urgency is the engine of most scams. A deadline of a few hours leaves no time to check, which is exactly the goal. '
            . 'When you feel pressure, take it as a signal to slow down. Finally, a small fee is a classic bait: the amount is irrelevant, the full card data is the prize.</p>'
            . self::block('defense-guidelines', self::guidelines()) . '</div>',

            '<div><h2>Practice with the tutor</h2>'
            . '<p>Now you will see some emails, one at a time. For each one choose "Phishing" or "Legitimate" and write, in your own words, why. '
            . 'A tutor will ask you a few questions about your reasoning instead of giving you the answer; the correct answer appears at the end of each conversation. '
            . 'The tutor\'s help becomes lighter from one email to the next. Please do not write personal data in your answers.</p></div>',

            '<div><h2>Recap</h2>'
            . '<p>Phishing works because it imitates the brands and the urgency of everyday life. You saw how to read the sender domain, hover over links, '
            . 'slow down when a message threatens a loss, never type card data on a page opened from an email and verify a service from its own app.</p>'
            . '<div data-role="thanks"><p>Thank you for attending this training, {{first_name}}!</p></div></div>',
        ];
    }
}
