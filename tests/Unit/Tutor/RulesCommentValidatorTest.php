<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\RulesCommentPrompt;
use App\Services\Tutor\RulesCommentValidator;
use App\Services\Tutor\Violation;

/** Validator of the Module 5 comment (prompt 5b): schema, G5 length/questions, G3 ids, G9 internals. */
class RulesCommentValidatorTest extends TutorTestCase
{
    private const MISSED = ['m4-p1-icloud:c3', 'm4-p3-coworker:c1'];

    private static function check(array|string $reply): array
    {
        $raw = is_string($reply) ? $reply : json_encode($reply);

        return RulesCommentValidator::validate($raw, RulesCommentPrompt::loadSchema(self::$materials), self::MISSED);
    }

    private static function reply(array $overrides = []): array
    {
        return array_merge([
            'message' => 'Your rule about opening the app yourself covers the login link. In the email from your colleague you did not check where the invoice came from: which rule would help you next time?',
            'covered_clue_ids' => ['m4-p1-icloud:c3'],
            'focus_clue_id' => 'm4-p3-coworker:c1',
            'generic_rule' => false,
        ], $overrides);
    }

    private static function keys(array $result): array
    {
        return array_map(fn (Violation $v) => $v->key(), $result['violations']);
    }

    public function test_valid_reply_passes(): void
    {
        $r = self::check(self::reply());
        $this->assertTrue($r['ok'], json_encode(self::keys($r)));
        $this->assertSame(['m4-p1-icloud:c3'], $r['output']['covered_clue_ids']);
        $this->assertSame('m4-p3-coworker:c1', $r['output']['focus_clue_id']);
        $this->assertFalse($r['output']['generic_rule']);

        // Everything covered: no question, focus null. Fenced JSON is tolerated.
        $r = self::check("```json\n".json_encode(self::reply(['message' => 'Your rules already cover what you missed.', 'covered_clue_ids' => self::MISSED, 'focus_clue_id' => null]))."\n```");
        $this->assertTrue($r['ok'], json_encode(self::keys($r)));
    }

    public function test_not_json_and_schema_errors_are_k1(): void
    {
        $this->assertSame(['K1:not_json'], self::keys(self::check('Sure! Here is my comment.')));
        $this->assertSame(['K1:schema'], self::keys(self::check(self::reply(['generic_rule' => 'no']))));
        $missing = self::reply();
        unset($missing['focus_clue_id']);
        $this->assertSame(['K1:schema'], self::keys(self::check($missing)));
        $this->assertSame(['K1:schema'], self::keys(self::check(self::reply(['extra' => 1]))));
        $this->assertSame(['K1:schema'], self::keys(self::check(self::reply(['message' => '']))));
    }

    public function test_g5_at_most_three_sentences_and_one_question(): void
    {
        $this->assertSame(['G5:too_many_sentences'], self::keys(self::check(self::reply(['message' => 'One. Two. Three. Four.']))));
        $this->assertSame(['G5:too_many_questions'], self::keys(self::check(self::reply(['message' => 'What would you check? And when?']))));
    }

    public function test_g3_ids_must_come_from_the_missed_clues(): void
    {
        $this->assertSame(['G3:covered_not_missed'], self::keys(self::check(self::reply(['covered_clue_ids' => ['m4-p1-icloud:c1']]))));
        $this->assertSame(['G3:focus_not_missed'], self::keys(self::check(self::reply(['focus_clue_id' => 'c3']))));
        $this->assertSame(['G3:focus_covered'], self::keys(self::check(self::reply(['covered_clue_ids' => self::MISSED]))));
        // No missed cue at all: any id is invented.
        $r = RulesCommentValidator::validate(json_encode(self::reply()), RulesCommentPrompt::loadSchema(self::$materials), []);
        $this->assertSame(['G3:covered_not_missed', 'G3:focus_not_missed'], self::keys($r));
    }

    public function test_g9_no_internals_in_the_message(): void
    {
        $this->assertSame(['G9:internals'], self::keys(self::check(self::reply(['message' => 'Your rules cover c3 well.']))));
        $this->assertSame(['G9:internals'], self::keys(self::check(self::reply(['message' => 'Think about m4-p3-coworker again.']))));
        $this->assertSame(['G9:internals'], self::keys(self::check(self::reply(['message' => 'Look at the sender_address field.']))));
    }
}
