<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\Classification;
use App\Services\Tutor\CloseReason;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\FakeLlmClient;
use App\Services\Tutor\HelpLevel;
use App\Services\Tutor\InputRejectedException;
use App\Services\Tutor\LogEvent;
use App\Services\Tutor\Materials;
use App\Services\Tutor\Phase;
use InvalidArgumentException;

/** Port of test/state-machine.test.ts. */
class StateMachineTest extends TutorTestCase
{
    private static function session(?array $c = null, int $position = 3): DialogSession
    {
        return new DialogSession($c ?? self::card('m4-p3-coworker'), $position, clock: self::fixedClock());
    }

    /** Scripted ideal reply (TS helper `q`). */
    private static function q(?array $c = null, string $msg = 'What are you basing that on?', array $rec = [], array $extra = []): array
    {
        return ['json' => self::makeOutput($c ?? self::card('m4-p3-coworker'), $msg, $rec, $extra)];
    }

    private static function hasEvent(DialogSession $s, string $type): bool
    {
        return $s->eventsOfType($type) !== [];
    }

    // ---- help levels and sequence (Tabella 13, 4.3.4)

    public function test_maps_position_to_level_and_raises_one_level_for_dont_know(): void
    {
        $map = self::$bank['_meta']['help_level_by_position'];
        $this->assertSame([HelpLevel::High, HelpLevel::Medium, HelpLevel::Low, HelpLevel::Low], array_map(fn ($p) => DialogSession::helpLevelForPosition($p, $map), [1, 2, 3, 4]));
        $this->assertSame([HelpLevel::Medium, HelpLevel::High, HelpLevel::High], array_map(fn ($l) => DialogSession::raiseLevel($l), [HelpLevel::Low, HelpLevel::Medium, HelpLevel::High]));
        $this->expectException(InvalidArgumentException::class);
        DialogSession::helpLevelForPosition(5);
    }

    public function test_inserts_the_legitimate_email_at_position_2_to_4(): void
    {
        $meta = self::$bank['_meta'];
        $legit = $meta['legitimate_main'];
        $this->assertSame(1, array_search($legit, DialogSession::composeSequence($meta, fn () => 0)));
        $this->assertSame(2, array_search($legit, DialogSession::composeSequence($meta, fn () => 0.5)));
        $this->assertSame(3, array_search($legit, DialogSession::composeSequence($meta, fn () => 0.999)));
        $this->assertCount(4, DialogSession::composeSequence($meta, fn () => 0.5));
    }

    // ---- input gate (T02, T03)

    public function test_requires_classification_and_a_4_word_first_explanation(): void
    {
        $this->assertSame('too_short', DialogSession::checkUserInput('looks fake', Classification::Phishing, true)->reason);
        $this->assertSame('empty', DialogSession::checkUserInput('   ', Classification::Phishing, true)->reason);
        $this->assertSame('missing_classification', DialogSession::checkUserInput('The sender is odd here', null, true)->reason);
        $this->assertTrue(DialogSession::checkUserInput('The sender is odd', Classification::Phishing, true)->ok);
        $this->assertTrue(DialogSession::checkUserInput('ok', Classification::Phishing, false)->ok);
        $c = DialogSession::checkUserInput(' ', Classification::Phishing, false);
        $this->assertFalse($c->ok);
        $this->assertSame('empty', $c->reason);
    }

    public function test_a_rejected_input_saves_no_turn_and_calls_no_model(): void
    {
        $s = self::session();
        $e = self::engine([], 1000, $client);
        try {
            $s->step($e, 'looks fake', Classification::Phishing);
            $this->fail('expected InputRejectedException');
        } catch (InputRejectedException) {
        }
        $this->assertCount(0, $s->history);
        $this->assertSame(0, $s->userReplyCount);
        $this->assertCount(0, $client->calls);
    }

    // ---- closing condition (4.4.2 step 5, 4.4.4)

    public function test_never_closes_before_2_replies_even_if_all_main_items_are_named(): void
    {
        $cw = self::card('m4-p3-coworker');
        $main = Materials::mainIds($cw);
        $s = self::session();
        $e = self::engine([self::q($cw, 'You covered a lot. What would the attacker gain from your work login?', $main, ['propose_close' => true])]);
        $out = $s->step($e, 'The address is a different domain, the link goes there and wants my login', 'phishing');
        $this->assertSame(Phase::AwaitingReply, $out->phase);
        $this->assertCount(1, $out->shown);
        // Second reply: all main already recognized -> closing; the question of the dialog turn is suppressed.
        $e2 = self::engine([
            self::q($cw, 'Thanks. Which other element would you compare?', $main),
            self::q($cw, 'You found the domain, the link and the login request. Next time, call Laura.', $main, ['propose_close' => true]),
        ]);
        $out2 = $s->step($e2, 'They want my password to get into company systems', 'phishing');
        $this->assertSame(Phase::Closed, $out2->phase);
        $this->assertSame(CloseReason::AllMainRecognized, $s->closeReason);
        $this->assertSame(['You found the domain, the link and the login request. Next time, call Laura.'], $out2->shown);
        $this->assertTrue(self::hasEvent($s, 'dialog_message_suppressed'));
        $this->assertSame(Classification::Phishing, $out2->closing?->verdict);
        foreach ($out2->closing->missed as $i) {
            $this->assertFalse($i['main']);
        }
    }

    public function test_closes_at_the_4th_reply_with_no_dialog_call(): void
    {
        $lc = self::card('m4-l2-apple');
        $s = self::session($lc, 2);
        $e = self::engine([self::q($lc, 'What makes you say so?'), self::q($lc, 'What else would you check?'), self::q($lc, 'Which element shows that?'), self::q($lc, 'You reconsidered the receipt. Next time, check the sender domain.', [], ['propose_close' => true])], 1000, $client);
        $s->step($e, "It's just a receipt for my storage.", 'legitimate');
        $s->step($e, 'Can I skip this one?', 'legitimate');
        $s->step($e, 'I think it is fine overall', 'legitimate');
        $this->assertSame(Phase::AwaitingReply, $s->phase);
        $last = $s->step($e, 'The address ends with apple.com', 'legitimate');
        $this->assertSame(Phase::Closed, $last->phase);
        $this->assertSame(CloseReason::MaxReplies, $s->closeReason);
        $this->assertCount(4, $client->calls); // 3 dialog + 1 closing, none for the 4th dialog turn
        $this->assertStringContainsString('Current turn type: closing', $client->calls[3]['system']);
        $this->assertSame(Classification::Legitimate, $last->closing?->verdict);
    }

    public function test_closes_after_two_consecutive_minimal_replies(): void
    {
        $cw = self::card('m4-p3-coworker');
        $s = self::session();
        $e = self::engine([
            self::q($cw, 'You say invoices like this are normal. What in the email shows that it really comes from Laura?'),
            self::q($cw, 'Fine. Can you give me one example from the email that supports your view?', [], ['minimal_reply' => true]),
            self::q($cw, "Let's look at the email together now.", [], ['minimal_reply' => true, 'propose_close' => true]),
            self::q($cw, 'You trusted the name. Next time, compare the sender domain with yours and call Laura.', [], ['propose_close' => true]),
        ]);
        $s->step($e, 'Laura always sends invoices like this.', 'legitimate');
        $a = $s->step($e, 'ok', 'legitimate');
        $this->assertSame(Phase::AwaitingReply, $a->phase);
        $this->assertSame(1, $s->consecutiveMinimalReplies());
        $b = $s->step($e, 'yes', 'legitimate');
        $this->assertSame(Phase::Closed, $b->phase);
        $this->assertSame(CloseReason::MinimalReplies, $s->closeReason);
        $this->assertSame(["Let's look at the email together now.", 'You trusted the name. Next time, compare the sender domain with yours and call Laura.'], $b->shown);
    }

    public function test_a_non_minimal_reply_resets_the_minimal_streak_and_the_model_flag_counts_too(): void
    {
        $cw = self::card('m4-p3-coworker');
        $s = self::session();
        $e = self::engine([self::q(), self::q($cw, 'What makes you think so?', [], ['minimal_reply' => true]), self::q(), self::q($cw, 'Next time, check.', [], ['propose_close' => true])]);
        $s->step($e, 'Laura always sends invoices like this.', 'legitimate');
        $s->step($e, 'that is what you said', 'legitimate'); // repeats the tutor: only the model can tell
        $this->assertSame(1, $s->consecutiveMinimalReplies());
        $s->step($e, 'The domain after the @ is not brightwave', 'phishing');
        $this->assertSame(0, $s->consecutiveMinimalReplies());
        $this->assertCount(1, $s->eventsOfType('classification_changed'));
    }

    // ---- retry and fallback (4.4.5, T22)

    public function test_retries_once_and_uses_the_model_reply_if_the_retry_is_valid(): void
    {
        $s = self::session();
        $e = self::engine(['not json', self::q()], 1000, $client);
        $out = $s->step($e, 'Laura always sends invoices like this.', 'legitimate');
        $this->assertSame(['What are you basing that on?'], $out->shown);
        $this->assertCount(2, $client->calls);
        $this->assertCount(1, $s->eventsOfType('retry'));
        $this->assertFalse(self::hasEvent($s, 'fallback'));
    }

    public static function fallbackScripts(): array
    {
        $cw = self::card('m4-p3-coworker');
        $verdict = self::q($cw, 'Yes, this is phishing: look at the sender.');

        return [
            'not JSON' => [['not json', 'still not json'], 'invalid_output'],
            'verdict wording' => [[$verdict, $verdict], 'invalid_output'],
            'unknown id' => [[self::q($cw, 'What do you notice?', ['c42']), self::q($cw, 'What do you notice?', ['c42'])], 'invalid_output'],
            'provider error' => [[['error' => 'HTTP 500'], ['error' => 'HTTP 500']], 'provider_error'],
            'timeout' => [[['hang' => true], ['hang' => true]], 'timeout'],
        ];
    }

    /** @dataProvider fallbackScripts */
    public function test_falls_back_to_the_first_unused_card_hint_after_two_failures(array $script, string $failure): void
    {
        $cw = self::card('m4-p3-coworker');
        $s = self::session();
        $e = self::engine($script, 30, $client);
        $out = $s->step($e, 'Laura always sends invoices like this.', 'legitimate');
        $this->assertSame([$cw['fallback_hints']['low'][0]], $out->shown);
        $this->assertDoesNotMatchRegularExpression('/this is phishing/i', implode(' ', $out->shown));
        $fb = $s->eventsOfType('fallback')[0] ?? null;
        $this->assertInstanceOf(LogEvent::class, $fb);
        $this->assertSame(2, $fb->detail['attempts']);
        $this->assertSame([$failure, $failure], $fb->detail['failures']);
        $this->assertSame(0, $client->remaining());
    }

    /** Real-API run 18-11: after the user named the sender domain, the fallback pointed back at the sender. */
    public function test_fallback_skips_hints_about_a_zone_the_user_already_recognized(): void
    {
        $icloud = self::card('m4-p1-icloud'); // position 1 = high help: sender, hover, request hints
        $s = self::session($icloud, 1);
        $e = self::engine([self::q($icloud, 'Good. What else do you notice?', ['c1']), 'x', 'x']);
        $s->step($e, 'The sender is icloud-storage-alerts.com, not apple.com at all', 'phishing');
        $this->assertContains('c1', $s->recognized);
        $out = $s->step($e, 'The button probably goes to another site', 'phishing');
        $this->assertSame([$icloud['fallback_hints']['high'][1]], $out->shown);
    }

    public function test_after_dont_know_the_fallback_uses_the_hints_of_the_level_above_once(): void
    {
        $airbnb = self::card('m4-p2-airbnb');
        $s = self::session($airbnb, 2);
        $e = self::engine(['x', 'x', 'x', 'x']);
        $s->step($e, 'The host cancelled and they want my card', 'phishing');
        $dk = $s->step($e, "I don't know.", 'phishing');
        $this->assertSame([$airbnb['fallback_hints']['high'][0]], $dk->shown);
        $this->assertTrue(self::hasEvent($s, 'dont_know'));
    }

    public function test_closing_fallback_is_the_static_feedback_and_the_verdict_always_from_the_card(): void
    {
        $cw = self::card('m4-p3-coworker');
        $s = self::session();
        $e = self::engine([self::q(), self::q(), self::q(), 'x', 'x']);
        $s->step($e, 'Laura always sends invoices like this.', 'legitimate');
        $s->step($e, 'It mentions a real invoice number', 'legitimate');
        $s->step($e, 'The signature is Laura, as always', 'legitimate');
        $last = $s->step($e, 'I still think it is fine', 'legitimate');
        $this->assertSame(Classification::Phishing, $last->closing->verdict);
        $this->assertSame('fallback', $last->closing->source);
        $this->assertSame($cw['static_feedback'], $last->closing->message);
        $this->assertCount(count(Materials::mainIds($cw)), $s->missedMainLabels());
    }
}
