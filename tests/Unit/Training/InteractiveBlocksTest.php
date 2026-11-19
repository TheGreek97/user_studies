<?php

namespace Tests\Unit\Training;

use App\Services\Llm\LlmException;
use App\Services\Training\InteractiveBlockException;
use App\Services\Training\InteractiveBlocks;
use PHPUnit\Framework\TestCase;

class InteractiveBlocksTest extends TestCase
{
    public function test_extracts_and_strips_a_valid_block(): void
    {
        $html = "<div><p>Intro</p>\n" . InteractiveFixtures::block('scenario-header', InteractiveFixtures::header()) . "\n</div>";

        $result = InteractiveBlocks::extract($html, ['scenario-header']);

        $this->assertSame(InteractiveFixtures::header(), $result['blocks']['scenario-header']);
        $this->assertStringNotContainsString('<script', $result['html']);
        $this->assertStringNotContainsString('sender_address', $result['html']);
        $this->assertStringContainsString('<p>Intro</p>', $result['html']);
    }

    public function test_accepts_any_attribute_order_and_quotes(): void
    {
        $json = json_encode(InteractiveFixtures::guidelines());
        $html = "<div><SCRIPT data-role='defense-guidelines' type='application/json'>\n$json\n</SCRIPT ></div>";

        $result = InteractiveBlocks::extract($html, ['defense-guidelines']);

        $this->assertCount(4, $result['blocks']['defense-guidelines']);
        $this->assertSame('<div></div>', $result['html']);
    }

    public function test_validates_the_scenario_email(): void
    {
        $html = InteractiveFixtures::block('scenario-email', InteractiveFixtures::email());

        $email = InteractiveBlocks::extract($html, ['scenario-email'])['blocks']['scenario-email'];

        $this->assertSame('{{first_name}}', $email['recipient_name']);
        $this->assertNull($email['reply_to']);
        $this->assertCount(2, $email['clues']);
    }

    public function test_missing_block_is_a_retryable_failure(): void
    {
        try {
            InteractiveBlocks::extract('<div><p>No data</p></div>', ['scenario-header']);
            $this->fail('Expected InteractiveBlockException');
        } catch (InteractiveBlockException $e) {
            $this->assertInstanceOf(LlmException::class, $e);
            $this->assertTrue($e->retryable);
            $this->assertStringContainsString('Missing', $e->getMessage());
        }
    }

    public function test_malformed_json_is_rejected(): void
    {
        $html = '<div><script type="application/json" data-role="scenario-header">{"sender_name": "IT",}</script></div>';

        $this->expectException(InteractiveBlockException::class);
        $this->expectExceptionMessage('Malformed JSON');
        InteractiveBlocks::extract($html, ['scenario-header']);
    }

    public function test_duplicated_block_is_rejected(): void
    {
        $block = InteractiveFixtures::block('scenario-header', InteractiveFixtures::header());

        $this->expectException(InteractiveBlockException::class);
        $this->expectExceptionMessage('Duplicated');
        InteractiveBlocks::extract($block . $block, ['scenario-header']);
    }

    public function test_missing_required_field_is_rejected(): void
    {
        $header = InteractiveFixtures::header();
        unset($header['first_line']);

        $this->expectException(InteractiveBlockException::class);
        $this->expectExceptionMessage('first_line');
        InteractiveBlocks::extract(InteractiveFixtures::block('scenario-header', $header), ['scenario-header']);
    }

    /**
     * @dataProvider invalidEmails
     */
    public function test_invalid_scenario_email_is_rejected(callable $mutate, string $message): void
    {
        $email = InteractiveFixtures::email();
        $mutate($email);

        $this->expectException(InteractiveBlockException::class);
        $this->expectExceptionMessage($message);
        InteractiveBlocks::extract(InteractiveFixtures::block('scenario-email', $email), ['scenario-email']);
    }

    public static function invalidEmails(): array
    {
        return [
            'unknown NIST category' => [function (&$e) { $e['clues'][0]['nist_category'] = 'Urgency'; }, 'Urgency'],
            'unknown target field' => [function (&$e) { $e['clues'][0]['target']['field'] = 'footer'; }, 'footer'],
            'link clue without existing link id' => [function (&$e) { $e['clues'][1]['target']['link_id'] = 'l9'; }, 'l9'],
            'duplicated clue ids' => [function (&$e) { $e['clues'][1]['id'] = 'c1'; }, 'unique'],
            'no clues' => [function (&$e) { $e['clues'] = []; }, 'empty'],
            'order not an integer' => [function (&$e) { $e['clues'][0]['order'] = 'first'; }, 'order'],
            'invalid date' => [function (&$e) { $e['date'] = '31st of Smarch'; }, 'date'],
            'links not an array' => [function (&$e) { $e['links'] = 'l1'; }, 'links'],
        ];
    }

    public function test_invalid_email_part_is_rejected(): void
    {
        $guidelines = InteractiveFixtures::guidelines();
        $guidelines[0]['email_part'] = 'footer';

        $this->expectException(InteractiveBlockException::class);
        $this->expectExceptionMessage('footer');
        InteractiveBlocks::extract(InteractiveFixtures::block('defense-guidelines', $guidelines), ['defense-guidelines']);
    }

    public function test_other_scripts_are_left_untouched_and_unrequested_blocks_are_stripped(): void
    {
        $html = '<div><script>alert(1)</script>'
            . '<script type="application/json" data-role="something-else">{"a":1}</script></div>';

        $result = InteractiveBlocks::extract($html, []);

        $this->assertSame([], $result['blocks']);
        $this->assertSame('<div><script>alert(1)</script></div>', $result['html']);
    }
}
