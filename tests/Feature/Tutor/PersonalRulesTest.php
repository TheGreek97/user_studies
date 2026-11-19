<?php

namespace Tests\Feature\Tutor;

use App\Http\Controllers\TrainingController;
use App\Http\Livewire\PersonalRulesForm;
use App\Models\RulesComment;
use App\Models\Training;
use App\Models\TrainingResponse;
use App\Models\TutorSession;
use App\Models\User;
use App\Services\Tutor\ChatSubmitResult;
use App\Services\Tutor\FakeLlmClient;
use App\Services\Tutor\PersonalRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\Feature\Llm\ProcessTrainingTest;
use Tests\TestCase;

/**
 * Module 5 personal rules (PersonalRules + PersonalRulesForm, prompt 5b). Never calls a real provider:
 * tutor.llm is a FakeLlmClient.
 */
class PersonalRulesTest extends TestCase
{
    use RefreshDatabase;

    private const GUIDELINES = [
        ['id' => 'g1', 'text' => 'Check the domain after the @ in the sender address', 'email_part' => 'sender'],
    ];

    private const CONCLUSIONS = '<div><p>Recap: stop, check the sender, hover the links.</p>'
        .'<div data-role="thanks"><p>Thank you for attending, {{first_name}}!</p></div></div>';

    private const RULES = [
        'When an email asks me to log in, I open the app myself instead of the link.',
        'When an email is urgent, I stop and read the sender address again.',
        '',
    ];

    /**
     * Interactive participant with a generated training and a Module 4 tutor session, completed by default.
     * Exercise 1 missed c3 and c7 (main); exercise 4 missed c1, c3, c4.
     */
    private function participant(string $name = 'Alice', bool $tutorCompleted = true, string $personalization = 'interactive'): User
    {
        $user = ProcessTrainingTest::participant($personalization);
        $user->forceFill(['name' => $name])->save();
        $training = Training::create([
            'user_id' => $user->id,
            'generated' => true,
            'introduction' => '<p>Intro</p>',
            'scenario' => '<p>Scenario</p>',
            'defense_strategies' => '<p>Strategies</p>',
            'exercises' => '<p>Exercises</p>',
            'conclusions' => self::CONCLUSIONS,
            'interactive_data' => $personalization === 'interactive' ? ['defense_guidelines' => self::GUIDELINES] : null,
        ]);
        TutorSession::create([
            'user_id' => $user->id,
            'training_id' => $training->id,
            'seed' => 1,
            'sequence' => ['m4-p1-icloud', 'm4-p2-airbnb', 'm4-l1-airbnb', 'm4-p3-coworker'],
            'legitimate_position' => 3,
            'position' => 4,
            'state' => null,
            'closed_states' => [
                ['card_id' => 'm4-p1-icloud', 'position' => 1, 'recognized' => ['c1']],
                ['card_id' => 'm4-p2-airbnb', 'position' => 2, 'recognized' => ['c1', 'c4', 'c5']],
                ['card_id' => 'm4-l1-airbnb', 'position' => 3, 'recognized' => ['s1', 's2', 's5']],
                ['card_id' => 'm4-p3-coworker', 'position' => 4, 'recognized' => []],
            ],
            'missed_clues' => [],
            'completed_at' => $tutorCompleted ? now() : null,
        ]);

        return $user->fresh();
    }

    private static function comment(array $overrides = []): array
    {
        return ['json' => array_merge([
            'message' => 'Your rule about opening the app yourself would have stopped the iCloud button. In the email from your colleague you did not check who really sent it: which rule would help you next time?',
            'covered_clue_ids' => ['m4-p1-icloud:c3'],
            'focus_clue_id' => 'm4-p3-coworker:c1',
            'generic_rule' => false,
        ], $overrides)];
    }

    private function fake(array $script): FakeLlmClient
    {
        $fake = new FakeLlmClient($script);
        $this->app->instance('tutor.llm', $fake);

        return $fake;
    }

    private function submit($component, array $rules)
    {
        foreach (array_values($rules) as $i => $rule) {
            $component->set("rules.{$i}", $rule);
        }

        return $component->call('submit');
    }

    private static function response(User $user, string $key): ?TrainingResponse
    {
        return TrainingResponse::where('user_id', $user->id)->where('module', 5)->where('item_key', $key)->first();
    }

    public function test_full_flow_rules_comment_one_revision_thanks_and_completion(): void
    {
        $this->actingAs($user = $this->participant());
        $fake = $this->fake([self::comment()]);

        $c = Livewire::test(PersonalRulesForm::class)
            ->assertSet('completed', false)
            ->assertSee('Send my rules')
            ->assertDontSee('Thank you for attending');
        $this->submit($c, self::RULES)
            ->assertHasNoErrors()
            ->assertSee('Your rule about opening the app yourself would have stopped the iCloud button.')
            ->assertSee('Save my final rules')
            ->assertSet('rules', [self::RULES[0], self::RULES[1], '']) // prefilled for the one revision
            ->assertDontSee('Thank you for attending');

        // ONE call, with this user's missed clues, guidelines and rules (as JSON data).
        $this->assertCount(1, $fake->calls);
        $system = $fake->calls[0]['system'];
        $this->assertStringContainsString('"id": "m4-p1-icloud:c3"', $system);
        $this->assertStringContainsString('"id": "m4-p3-coworker:c4"', $system);
        $this->assertStringNotContainsString('m4-p2-airbnb', $system); // nothing missed there
        $this->assertStringNotContainsString('m4-l1-airbnb', $system);
        $this->assertStringContainsString('- Check the domain after the @ in the sender address', $system);
        $this->assertStringContainsString(json_encode(self::RULES[0]), $system);
        $this->assertStringNotContainsString('Alice', $system); // the name never reaches the provider
        $this->assertSame('low', $fake->calls[0]['options']['effort']);
        $this->assertArrayHasKey('json_schema', $fake->calls[0]['options']);

        $v1 = self::response($user, 'rules_v1');
        $this->assertSame([self::RULES[0], self::RULES[1]], $v1->response_json['rules']);
        $this->assertSame("{$v1->response_json['rules'][0]}\n{$v1->response_json['rules'][1]}", $v1->response_text);
        $this->assertSame($user->training->id, $v1->training_id);
        $log = RulesComment::where('user_id', $user->id)->sole();
        $this->assertSame(RulesComment::SHOWN, $log->status);
        $this->assertSame(['m4-p1-icloud:c3'], $log->covered_clue_ids);
        $this->assertSame('m4-p3-coworker:c1', $log->focus_clue_id);
        $this->assertFalse($log->generic_rule);
        $this->assertSame(['m4-p1-icloud:c3', 'm4-p1-icloud:c7', 'm4-p3-coworker:c1', 'm4-p3-coworker:c3', 'm4-p3-coworker:c4'], $log->missed_clue_ids);
        $this->assertSame('fake', $log->model);
        $this->assertNotNull($log->latency_ms);
        $this->assertCount(1, $log->raw_output);
        $this->assertNull($log->validator_errors);

        // The training cannot be completed between the two versions.
        $this->get(route('training.complete'))->assertRedirect(route('training.show'))->assertSessionHas('error');

        // The one revision: saved as rules_v2, no second comment, the thanks appear, completion is enabled.
        $revised = [self::RULES[0], 'When an email is urgent, I check the domain after the @ before acting.', 'When a colleague asks for a payment, I call them.'];
        $this->submit($c, $revised)
            ->assertHasNoErrors()
            ->assertSet('completed', true)
            ->assertEmitted('rulesCompleted')
            ->assertDispatchedBrowserEvent('rules-completed')
            ->assertSee('Thank you for attending, Alice!')
            ->assertSee('When a colleague asks for a payment, I call them.')
            ->assertDontSee('Save my final rules');
        $this->assertCount(1, $fake->calls);
        $v2 = self::response($user, 'rules_v2');
        $this->assertSame($revised, $v2->response_json['rules']);
        $this->assertFalse($v2->response_json['unchanged']);

        // No third version.
        $result = (new PersonalRules($user))->submitRevision($revised);
        $this->assertSame(ChatSubmitResult::NOT_ACCEPTING, $result->status);

        $this->get(route('training.complete', ['time' => 99]))->assertRedirect(route('emails', ['folder' => 'inbox']));
        $this->assertNotNull($user->fresh()->training_completed);
    }

    public function test_reload_resumes_the_revision_and_the_final_state(): void
    {
        $this->actingAs($user = $this->participant());
        $this->fake([self::comment()]);
        (new PersonalRules($user))->submitRules(self::RULES);

        Livewire::test(PersonalRulesForm::class)
            ->assertSet('rules', [self::RULES[0], self::RULES[1], ''])
            ->assertSee('Your rule about opening the app yourself')
            ->assertSee('data-rules-completed="false"', false);

        // Unchanged revision is allowed (= keeping the rules) and recorded as such.
        (new PersonalRules($user))->submitRevision(self::RULES);
        $this->assertTrue(self::response($user, 'rules_v2')->response_json['unchanged']);
        Livewire::test(PersonalRulesForm::class)
            ->assertSet('completed', true)
            ->assertDispatchedBrowserEvent('rules-completed')
            ->assertSee('data-rules-completed="true"', false)
            ->assertSee('Thank you for attending, Alice!');
    }

    public function test_first_name_in_the_thanks_is_escaped(): void
    {
        $this->actingAs($user = $this->participant('<b>Al&ice</b>'));
        $this->fake([self::comment()]);
        $service = new PersonalRules($user);
        $service->submitRules(self::RULES);
        $service->submitRevision(self::RULES);

        Livewire::test(PersonalRulesForm::class)
            ->assertSee('Thank you for attending, &lt;b&gt;Al&amp;ice&lt;/b&gt;!', false)
            ->assertDontSee('<b>Al&ice</b>', false);
    }

    public function test_comment_is_escaped(): void
    {
        $this->actingAs($this->participant());
        $this->fake([self::comment(['message' => 'Your rule is <b>clear</b> & <script>alert(1)</script> concrete.'])]);

        $c = Livewire::test(PersonalRulesForm::class);
        $this->submit($c, self::RULES)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>clear</b>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_invalid_output_is_retried_once(): void
    {
        $this->actingAs($user = $this->participant());
        $fake = $this->fake(['Sure, here is my comment!', self::comment()]);

        $this->submit(Livewire::test(PersonalRulesForm::class), self::RULES)->assertSee('which rule would help you next time?');
        $this->assertCount(2, $fake->calls);
        $log = RulesComment::where('user_id', $user->id)->sole();
        $this->assertSame(RulesComment::SHOWN, $log->status);
        $this->assertSame([false, true], array_column($log->raw_output, 'ok'));
        $this->assertSame(['K1:not_json'], $log->raw_output[0]['violations']);
        $this->assertSame('K1', $log->validator_errors[0]['rule']);
    }

    public function test_invalid_twice_skips_the_comment_logs_it_and_still_allows_the_revision(): void
    {
        Log::spy();
        $this->actingAs($user = $this->participant());
        $fake = $this->fake([
            self::comment(['covered_clue_ids' => ['m4-p2-airbnb:c1']]), // G3: not a missed cue
            self::comment(['message' => 'Rule one: when you get an email, check it. Rule two: hover. Rule three: call. Rule four: wait.']),
        ]);

        $c = $this->submit(Livewire::test(PersonalRulesForm::class), self::RULES)
            ->assertHasNoErrors()
            ->assertDontSee('Rule one')
            ->assertSee('Read your rules again')
            ->assertSee('Save my final rules');
        $this->assertCount(2, $fake->calls);
        $log = RulesComment::where('user_id', $user->id)->sole();
        $this->assertSame(RulesComment::SKIPPED, $log->status);
        $this->assertNull($log->message);
        $this->assertSame(['G3:covered_not_missed'], $log->raw_output[0]['violations']);
        $this->assertSame(['G5:too_many_sentences'], $log->raw_output[1]['violations']);
        $this->assertSame('skipped', self::response($user, 'rules_v1')->response_json['comment_status']);
        Log::shouldHaveReceived('warning')->withArgs(fn ($event) => $event === 'rules_comment.skipped')->once();

        $this->submit($c, self::RULES)->assertSet('completed', true);
        $this->assertNotNull(self::response($user, 'rules_v2'));
        $this->assertCount(2, $fake->calls);
    }

    public function test_provider_errors_twice_skip_the_comment(): void
    {
        $this->actingAs($user = $this->participant());
        $this->fake([['error' => 'overloaded'], ['hang' => true]]);

        $this->submit(Livewire::test(PersonalRulesForm::class), self::RULES)->assertHasNoErrors()->assertSee('Save my final rules');
        $log = RulesComment::where('user_id', $user->id)->sole();
        $this->assertSame(RulesComment::SKIPPED, $log->status);
        $this->assertSame(['provider_error', 'timeout'], array_column($log->raw_output, 'failure'));
    }

    public function test_input_gate_never_calls_the_model(): void
    {
        $this->actingAs($user = $this->participant());
        $fake = $this->fake([]);

        $c = Livewire::test(PersonalRulesForm::class);
        $this->submit($c, ['When I see a link, I hover over it.', '', ''])->assertHasErrors('rules'); // one rule
        $this->submit($c, ['When I see a link, I hover over it.', 'Be careful', ''])->assertHasErrors('rules'); // too short
        $this->submit($c, ['When I see a link, I hover over it. '.str_repeat('a', 600), 'When '.str_repeat('b ', 250), ''])->assertHasErrors('rules'); // > 1000 total
        $this->submit($c, [str_repeat('x', 1001), 'When I see a link, I hover over it.', ''])->assertHasErrors('rules.0');
        $this->assertCount(0, $fake->calls);
        $this->assertNull(self::response($user, 'rules_v1'));
        $this->assertSame(0, RulesComment::count());
    }

    public function test_double_submit_never_makes_a_second_paid_call(): void
    {
        $this->actingAs($user = $this->participant());
        $fake = $this->fake([self::comment(), self::comment()]);

        // Concurrent request still holding the per-user lock: refused without any call.
        $lock = Cache::lock('rules-lock:'.$user->id, 60);
        $this->assertTrue($lock->get());
        $busy = (new PersonalRules($user))->submitRules(self::RULES);
        $this->assertSame(ChatSubmitResult::BUSY, $busy->status);
        $this->assertCount(0, $fake->calls);
        $lock->release();

        $this->assertTrue((new PersonalRules($user))->submitRules(self::RULES)->ok());
        // A replayed first submit (double click, second tab) after the first one finished: no second comment.
        $again = (new PersonalRules($user))->submitRules(['When anything happens, I think twice about it.', 'When in doubt, I ask the IT desk first.']);
        $this->assertSame(ChatSubmitResult::NOT_ACCEPTING, $again->status);
        $this->assertCount(1, $fake->calls);
        $this->assertSame(1, RulesComment::count());
        $this->assertSame([self::RULES[0], self::RULES[1]], self::response($user, 'rules_v1')->response_json['rules']);
    }

    public function test_rate_limit(): void
    {
        config(['tutor.rules_rate_limit_per_minute' => 1]);
        $this->actingAs($user = $this->participant());
        $fake = $this->fake([self::comment()]);
        $service = new PersonalRules($user);
        $service->resetRateLimit();

        $this->assertTrue($service->submitRules(self::RULES)->ok());
        $this->assertSame(ChatSubmitResult::RATE_LIMITED, $service->submitRevision(self::RULES)->status);
        $this->assertNull(self::response($user, 'rules_v2'));
        $this->assertCount(1, $fake->calls);
        $service->resetRateLimit();
    }

    public function test_rules_need_a_completed_module_4_and_the_interactive_condition(): void
    {
        $this->actingAs($user = $this->participant(tutorCompleted: false));
        $fake = $this->fake([self::comment()]);
        $c = Livewire::test(PersonalRulesForm::class);
        $this->submit($c, self::RULES)->assertSee('Please complete the Module 4 exercises first.');
        $this->assertCount(0, $fake->calls);
        $this->assertNull(self::response($user, 'rules_v1'));

        $this->actingAs($this->participant('Bob', true, 'yes'));
        Livewire::test(PersonalRulesForm::class)->assertForbidden();
    }

    public function test_each_user_only_reaches_their_own_data(): void
    {
        $alice = $this->participant('Alice');
        $bob = $this->participant('Bob');
        // Bob recognised everything on exercise 1: his prompt must not contain Alice's missed icloud clues.
        TutorSession::where('user_id', $bob->id)->update(['closed_states' => json_encode([
            ['card_id' => 'm4-p1-icloud', 'position' => 1, 'recognized' => ['c1', 'c3', 'c7']],
        ])]);
        $fake = $this->fake([self::comment(), self::comment(['covered_clue_ids' => [], 'focus_clue_id' => null, 'message' => 'Your rules already cover the details you found.'])]);

        $this->actingAs($alice);
        $this->submit(Livewire::test(PersonalRulesForm::class), self::RULES)->assertHasNoErrors();
        $this->actingAs($bob);
        // Bob sees no comment of Alice and starts from an empty form.
        $c = Livewire::test(PersonalRulesForm::class)
            ->assertSet('rules', ['', '', ''])
            ->assertDontSee('Your rule about opening the app yourself')
            ->assertSee('Send my rules');
        $this->submit($c, ['When I get an invoice, I call the colleague on a known number.', 'When a link asks for a password, I type the site myself.'])
            ->assertSee('Your rules already cover the details you found.');

        $this->assertStringNotContainsString('m4-p1-icloud:c3', $fake->calls[1]['system']);
        $this->assertStringContainsString("# 3. CUES MISSED IN THE EXERCISES (from the solution cards)\n(none)", $fake->calls[1]['system']);
        $this->assertStringNotContainsString('When an email asks me to log in', $fake->calls[1]['system']);
        $this->assertSame([self::RULES[0], self::RULES[1]], self::response($alice, 'rules_v1')->response_json['rules']);
        $this->assertSame(RulesComment::SHOWN, RulesComment::where('user_id', $alice->id)->sole()->status);
        $this->assertSame(2, RulesComment::count());
        // The component exposes only what the user typed: no user, training or comment ids to tamper with.
        $this->assertSame(['rules', 'notice', 'completed'], array_keys(Livewire::test(PersonalRulesForm::class)->instance()->getPublicPropertiesDefinedBySubClass()));
    }

    public function test_training_page_shows_the_recap_and_hides_the_thanks_until_done(): void
    {
        $this->actingAs($user = $this->participant());
        $this->fake([]);
        $html = app(TrainingController::class)->showTraining()->render();

        $this->assertStringContainsString('Recap: stop, check the sender, hover the links.', $html);
        $this->assertStringNotContainsString('Thank you for attending', $html);
        $this->assertStringNotContainsString('data-role="thanks"', $html);
        $this->assertStringContainsString('data-rules-completed="false"', $html);
        $this->assertStringContainsString('x-on:rules-completed.window="rulesDone = true"', $html);
        $this->assertStringContainsString(':disabled="locked()"', $html);
        $this->assertStringContainsString('|| (this.step === 5 && !this.rulesDone);', $html);
        $this->assertStringContainsString("if (this.step === 5 && !this.rulesDone) return 'Write and save your personal rules first';", $html);
        $this->assertStringContainsString('document.querySelector(\'[data-rules-completed="true"]\')', $html);
    }

    public function test_paper_conditions_keep_the_whole_conclusions_and_no_rules(): void
    {
        $this->actingAs($this->participant('Carol', true, 'yes'));
        $html = app(TrainingController::class)->showTraining()->render();

        $this->assertStringContainsString('data-role="thanks"', $html);
        $this->assertStringNotContainsString('rulesDone', $html);
        $this->assertStringNotContainsString('data-rules-completed', $html);
        $this->assertStringNotContainsString('personal-rules', $html);
    }
}
