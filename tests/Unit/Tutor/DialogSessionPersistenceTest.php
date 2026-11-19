<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\DialogSession;
use App\Services\Tutor\Phase;
use InvalidArgumentException;

/** Beyond the TS core: the session must survive between HTTP requests (Livewire stores it in the DB). */
class DialogSessionPersistenceTest extends TutorTestCase
{
    private static function viaJson(DialogSession $s): array
    {
        return json_decode(json_encode($s->toArray(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function q(string $msg, array $rec = [], array $extra = []): array
    {
        return ['json' => self::makeOutput(self::card('m4-p3-coworker'), $msg, $rec, $extra)];
    }

    public function test_round_trip_mid_dialog_and_identical_continuation(): void
    {
        $card = self::card('m4-p3-coworker');
        $opts = ['module3Guidelines' => [['id' => 'g1', 'text' => 'Check the domain.', 'email_part' => 'sender']], 'styleCard' => ['Short sentences.'], 'previousMissedClues' => ['Button hides the real address']];
        $s = new DialogSession($card, 3, ...$opts, clock: self::fixedClock());
        // Turn 1 falls back (2 invalid replies), turn 2 is a minimal reply flagged by the model.
        $s->step(self::engine(['x', 'x']), 'Laura always sends invoices like this.', 'legitimate');
        $s->step(self::engine([self::q('What makes you think so?', ['c5'], ['minimal_reply' => true])]), 'that is what you said', 'legitimate');

        $saved = self::viaJson($s);
        $restored = DialogSession::fromArray($saved, $card, self::fixedClock());
        $this->assertSame($s->toArray(), $restored->toArray());
        $this->assertSame(Phase::AwaitingReply, $restored->phase);
        $this->assertSame(1, $restored->consecutiveMinimalReplies());

        // Same script on both: same outcome, same state (closing on 2 minimal replies, then closing fallback).
        $script = fn () => [self::q("Let's look at the email together now.", [], ['minimal_reply' => true]), 'x', 'x'];
        $a = $s->step(self::engine($script()), 'ok', 'legitimate');
        $b = $restored->step(self::engine($script()), 'ok', 'legitimate');
        $this->assertEquals($a, $b);
        $this->assertSame(Phase::Closed, $b->phase);
        $this->assertSame($s->toArray(), $restored->toArray());

        // A closed session round-trips too, closing view included.
        $closed = DialogSession::fromArray(self::viaJson($restored), $card);
        $this->assertEquals($restored->closingView, $closed->closingView);
        $this->assertSame($restored->missedMainLabels(), $closed->missedMainLabels());
    }

    public function test_used_fallback_hints_survive_the_round_trip(): void
    {
        $card = self::card('m4-p1-icloud'); // position 1 = high help: 3 fallback hints
        $s = new DialogSession($card, 1, clock: self::fixedClock());
        $s->step(self::engine(['x', 'x']), 'It rushes me to save my photos', 'phishing');
        $r = DialogSession::fromArray(self::viaJson($s), $card, self::fixedClock());
        $out = $r->step(self::engine(['x', 'x']), 'The sender looks odd too', 'phishing');
        $this->assertSame([$card['fallback_hints']['high'][1]], $out->shown);
    }

    public function test_state_stores_no_solution_card_and_rejects_another_card(): void
    {
        $card = self::card('m4-p3-coworker');
        $s = new DialogSession($card, 3, clock: self::fixedClock());
        $json = json_encode($s->toArray());
        $this->assertStringNotContainsString($card['static_feedback'], $json);
        $this->assertStringNotContainsString($card['clues'][0]['explanation'], $json);
        $this->expectException(InvalidArgumentException::class);
        DialogSession::fromArray($s->toArray(), self::card('m4-p1-icloud'));
    }

    public function test_sequence_draw_is_reproducible_from_the_logged_seed(): void
    {
        $meta = self::$bank['_meta'];
        $positions = [];
        foreach (range(1, 60) as $seed) {
            $d = DialogSession::drawSequence($meta, $seed);
            $this->assertSame($d, DialogSession::drawSequence($meta, $seed));
            $this->assertSame($seed, $d['seed']);
            $this->assertSame($meta['legitimate_main'], $d['sequence'][$d['legitimate_position'] - 1]);
            $positions[$d['legitimate_position']] = true;
        }
        ksort($positions);
        $this->assertSame([2, 3, 4], array_keys($positions));
        $this->assertIsInt(DialogSession::drawSequence($meta)['seed']);
    }
}
