<?php

namespace Tests\Unit\Training;

use App\Console\Demo\DemoTrainingAnswers;
use App\Services\Training\DefenseDiagram;
use App\Services\Training\ScenarioEmail;
use PHPUnit\Framework\TestCase;

class DefenseDiagramTest extends TestCase
{
    private static function email(): ScenarioEmail
    {
        return new ScenarioEmail(array_replace(InteractiveFixtures::email(), [
            'body_html' => '<p>Dear {{first_name}},</p><p>Your mailbox password expires in 2 hours.</p>'
                . '<p>Please confirm your password with the link below.</p>'
                . '<p><a data-link-id="l1" href="https://company-helpdesk.net/reset">Keep my password</a></p><p>IT Service Desk</p>',
        ]), 'Alice');
    }

    public function test_each_guideline_gets_a_marker_on_its_zone_and_a_numbered_text(): void
    {
        $guidelines = array_merge(InteractiveFixtures::guidelines(), [
            ['id' => 'g5', 'text' => 'Never send your password by email or web form.', 'email_part' => 'request'],
            ['id' => 'g6', 'text' => 'Do not open unexpected attachments.', 'email_part' => 'attachment'],  // none in the email
        ]);
        $diagram = DefenseDiagram::build($guidelines, self::email());
        $svg = $diagram['svg'];

        // Numbered top to bottom by the zone in the email (sender, tone, request, link), "General" last; the list
        // (items) uses the same numbering as the circles.
        $this->assertSame([1, 2, 3, 4, 5, 6], array_column($diagram['items'], 'number'));
        $this->assertSame(['Sender', 'Tone', 'Request', 'Link', 'General', 'Attachment'], array_column($diagram['items'], 'part_label'));
        $this->assertSame('Never send your password by email or web form.', $diagram['items'][2]['text']);
        $this->assertSame([1, 2, 3, 4, 5, 6], array_column($svg['items'], 'number'));
        $this->assertSame(640, $svg['width']);
        $this->assertGreaterThanOrEqual(DefenseDiagram::MIN_HEIGHT, $svg['height']);
        $this->assertCount(6, $svg['items']);
        $this->assertNotNull($svg['general']);  // g4 "none" and g6 (no attachment in the email)

        $rows = array_column($svg['rows'], 'text');
        $this->assertContains('I', $rows);  // avatar initial of "IT Service Desk"
        $this->assertContains('Dear Alice,', $rows);  // greeting, placeholder filled
        $this->assertContains('IT Service Desk', $rows);  // signature
        $this->assertContains('Keep my password', $rows);  // link row

        $y = fn (int $n) => $svg['items'][$n - 1]['marker']['y'];
        // Body rows are truncated to the card width: looked up by prefix.
        $rowY = fn (string $prefix) => collect($svg['rows'])->first(fn ($r) => str_starts_with($r['text'], $prefix))['y'];
        $this->assertSame($rowY('Your mailbox password expires') - 4, $y(2));  // tone -> line with a deadline
        $this->assertSame($rowY('Please confirm your password') - 4, $y(3));  // request
        $this->assertSame($y(5), $y(6));  // both in the General box
        $this->assertNotSame($svg['items'][4]['marker']['x'], $svg['items'][5]['marker']['x']);  // side by side
        self::assertConnectorsDoNotCross($svg['items']);

        // Texts on the right, stacked without overlap, all inside the viewBox.
        $previousBottom = 0;
        foreach ($svg['items'] as $item) {
            $this->assertGreaterThan($previousBottom, $item['badge']['y'] - 10);
            $previousBottom = end($item['lines'])['y'];
            $this->assertLessThanOrEqual($svg['height'], $previousBottom);
            foreach ($item['lines'] as $line) {
                $this->assertLessThanOrEqual(40, mb_strlen($line['text']));
            }
        }
    }

    public function test_several_guidelines_per_zone_keep_connectors_uncrossed(): void
    {
        $guidelines = [
            ['id' => 'a', 'text' => 'General advice one.', 'email_part' => 'none'],
            ['id' => 'b', 'text' => 'Look at the link destination before anything else, then think twice about it.', 'email_part' => 'link'],
            ['id' => 'c', 'text' => 'Sender one.', 'email_part' => 'sender'],
            ['id' => 'd', 'text' => 'Sender two, a longer text that wraps on more than one line of the diagram.', 'email_part' => 'sender'],
            ['id' => 'e', 'text' => 'General advice two.', 'email_part' => 'none'],
            ['id' => 'f', 'text' => 'Sender three.', 'email_part' => 'sender'],
            ['id' => 'g', 'text' => 'Signature check.', 'email_part' => 'signature'],
            ['id' => 'h', 'text' => 'Second link advice.', 'email_part' => 'link'],
        ];
        $diagram = DefenseDiagram::build($guidelines, self::email());

        // Ties keep the array order: c, d, f (sender), b, h (link), g (signature), then a, e (General).
        $this->assertSame(['Sender one.', 'Sender two, a longer text that wraps on more than one line of the diagram.', 'Sender three.',
            'Look at the link destination before anything else, then think twice about it.', 'Second link advice.', 'Signature check.',
            'General advice one.', 'General advice two.'], array_column($diagram['items'], 'text'));
        self::assertConnectorsDoNotCross($diagram['svg']['items']);
    }

    public function test_demo_training_diagram_has_uncrossed_connectors(): void
    {
        // Browser QA case (demo data at 1440px): connectors 2, 3 and 4 crossed before the guidelines were renumbered.
        $diagram = DefenseDiagram::build(DemoTrainingAnswers::guidelines(), new ScenarioEmail(DemoTrainingAnswers::email(), 'Alice'));

        $this->assertSame(['Sender', 'Tone', 'Request', 'Link', 'General'], array_column($diagram['items'], 'part_label'));
        self::assertConnectorsDoNotCross($diagram['svg']['items']);

        // Tone: the line with the strongest urgency ("within 12 hours"), not the first line mentioning "today".
        $svg = $diagram['svg'];
        $rowY = fn (string $prefix) => collect($svg['rows'])->first(fn ($r) => str_starts_with($r['text'], $prefix))['y'];
        $this->assertSame($rowY('If the fee is not paid within') - 4, $svg['items'][1]['marker']['y']);
    }

    /** Circles on the email in number order are top to bottom, as the texts, and no two connectors intersect. */
    private static function assertConnectorsDoNotCross(array $items): void
    {
        foreach ($items as $i => $item) {
            if ($i > 0) {
                self::assertGreaterThanOrEqual($items[$i - 1]['marker']['y'], $item['marker']['y'], "marker {$item['number']}");
                self::assertGreaterThan($items[$i - 1]['badge']['y'], $item['badge']['y'], "badge {$item['number']}");
            }
            foreach (array_slice($items, $i + 1) as $other) {
                self::assertFalse(self::intersect($item, $other), "connectors {$item['number']} and {$other['number']} cross");
            }
        }
    }

    private static function intersect(array $a, array $b): bool
    {
        $p = [$a['marker'], $a['badge']];
        $q = [$b['marker'], $b['badge']];
        $cross = fn ($o, $s, $t) => ($s['x'] - $o['x']) * ($t['y'] - $o['y']) - ($s['y'] - $o['y']) * ($t['x'] - $o['x']);
        $d1 = $cross($q[0], $q[1], $p[0]);
        $d2 = $cross($q[0], $q[1], $p[1]);
        $d3 = $cross($p[0], $p[1], $q[0]);
        $d4 = $cross($p[0], $p[1], $q[1]);

        return $d1 * $d2 < 0 && $d3 * $d4 < 0;  // proper intersection (distinct endpoints)
    }

    public function test_without_the_email_only_the_list_is_produced(): void
    {
        $diagram = DefenseDiagram::build(InteractiveFixtures::guidelines(), null);

        $this->assertNull($diagram['svg']);
        $this->assertCount(4, $diagram['items']);
    }
}
