<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\Classification;
use App\Services\Tutor\DialogTurn;
use App\Services\Tutor\HelpLevel;
use App\Services\Tutor\PromptBuilder;
use App\Services\Tutor\PromptContext;
use App\Services\Tutor\TurnType;
use InvalidArgumentException;

/** Port of test/prompt-builder.test.ts. */
class PromptBuilderTest extends TutorTestCase
{
    private static function ctx(): PromptContext
    {
        return new PromptContext(
            card: self::card('m4-p3-coworker'),
            exerciseNumber: 3,
            exerciseTotal: 4,
            turnType: TurnType::Dialog,
            userReplyCount: 1,
            maxUserReplies: 4,
            helpLevel: HelpLevel::Low,
            module3Guidelines: [['id' => 'g1', 'text' => 'Check the domain after the @ in the sender address.', 'email_part' => 'sender']],
            styleCard: ['Direct, short sentences.'],
            history: [new DialogTurn('user', 'Laura always sends invoices like this {{help_level}}.', Classification::Legitimate, 1)],
            previousMissedClues: ['Button hides the real address'],
        );
    }

    public function test_extracts_the_text_between_the_markers_without_the_fence(): void
    {
        $t = self::$template;
        $this->assertStringStartsWith('# 1. ROLE AND CONTEXT', $t);
        $this->assertStringContainsString('# 9. OUTPUT FORMAT', $t);
        $this->assertStringNotContainsString('---8<---', $t);
        $this->assertStringNotContainsString('```', $t);
        foreach (PromptBuilder::PLACEHOLDERS as $p) {
            $this->assertStringContainsString('{{'.$p.'}}', $t);
        }
    }

    public function test_fails_loudly_when_the_markers_are_missing(): void
    {
        $this->assertSame(self::$template, PromptBuilder::extractSystemTemplate(self::$materials->loadPromptMarkdown()));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/---8<---/');
        PromptBuilder::extractSystemTemplate('no markers here');
    }

    public function test_fills_every_placeholder_and_leaves_only_first_name(): void
    {
        $p = PromptBuilder::buildPrompt(self::$template, self::ctx());
        preg_match_all('/\{\{\w+\}\}/', $p->system, $m);
        $left = array_values(array_diff(array_unique($m[0]), ['{{first_name}}', '{{help_level}}']));
        $this->assertSame([], $left);
        $this->assertStringContainsString('This is exercise 3 of 4.', $p->system);
        $this->assertStringContainsString('Current help level: low', $p->system);
        $this->assertStringContainsString('User replies so far on this email (first explanation included): 1 of max 4', $p->system);
        $this->assertStringContainsString('- Check the domain after the @ in the sender address.', $p->system);
        $this->assertStringContainsString('- Button hides the real address', $p->system);
        $this->assertStringContainsString('"id": "m4-p3-coworker"', $p->system);
        $this->assertMatchesRegularExpression('/JSON/', $p->user);
    }

    public function test_never_sends_static_feedback_and_serialises_history_as_json(): void
    {
        $c = self::card('m4-p3-coworker');
        $this->assertArrayNotHasKey('static_feedback', PromptBuilder::cardForModel($c));
        $system = PromptBuilder::buildPrompt(self::$template, self::ctx())->system;
        $this->assertStringNotContainsString($c['static_feedback'], $system);
        // The user wrote "{{help_level}}": it stays literal inside the JSON history.
        $this->assertStringContainsString('"text": "Laura always sends invoices like this {{help_level}}."', $system);
    }

    public function test_single_pass_substitution_does_not_re_expand_inserted_values(): void
    {
        $this->assertSame('{{b}} B {{c}}', PromptBuilder::fillTemplate('{{a}} {{b}} {{c}}', ['a' => '{{b}}', 'b' => 'B']));
    }
}
