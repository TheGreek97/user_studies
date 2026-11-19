<?php

namespace Tests\Feature\Demo;

use App\Console\Commands\DemoInteractive;
use App\Models\RulesComment;
use App\Models\Training;
use App\Models\TutorSession;
use App\Models\User;
use App\Services\Tutor\DemoLlmClient;
use App\Services\Tutor\PersonalRules;
use App\Services\Tutor\Phase;
use App\Services\Tutor\TutorConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** `demo:interactive`: local-only demo participant with a ready interactive training, built without any provider. */
class DemoInteractiveCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_refuses_to_run_outside_local(): void
    {
        foreach (['testing', 'production'] as $env) {
            $this->app['env'] = $env;
            $this->artisan('demo:interactive')->assertExitCode(1);
        }
        $this->assertSame(0, User::count());
        $this->assertSame(0, Training::count());
    }

    public function test_creates_a_participant_ready_for_the_interactive_training(): void
    {
        $this->app['env'] = 'local';
        $pid = DemoInteractive::prolificIdFor('demo@example.test');

        $this->artisan('demo:interactive')
            ->expectsOutputToContain("/training?PROLIFIC_PID={$pid}")
            ->assertExitCode(0);

        $user = User::where('email', 'demo@example.test')->sole();
        $this->assertSame($pid, $user->prolific_id);
        $this->assertSame('interactive', $user->training_personalization);
        foreach (['given_consent', 'demographics_completed', 'bfi_completed', 'stp_completed', 'teique_completed', 'pre_training_completed'] as $column) {
            $this->assertNotNull($user->$column, $column);
        }
        $this->assertNull($user->training_completed);

        $training = $user->training;
        $this->assertTrue($training->generated);
        $this->assertStringNotContainsString('<script', $training->scenario);
        $this->assertStringContainsString('data-role="decision-point"', $training->scenario);
        $this->assertCount(4, $training->interactive_data['m4']['sequence']);
        $this->assertCount(5, $training->interactive_data['defense_guidelines']);
        $this->assertCount(4, $training->interactive_data['scenario_email']['clues']);
    }

    public function test_real_option_generates_with_the_container_llm_client(): void
    {
        $this->app['env'] = 'local';
        $fake = new \App\Services\Tutor\FakeLlmClient(\App\Console\Demo\DemoTrainingAnswers::answers());
        $this->app->instance(\App\Services\Llm\LlmClient::class, $fake);

        $this->artisan('demo:interactive', ['--real' => true])
            ->expectsOutputToContain('generated with the configured provider')
            ->assertExitCode(0);

        $this->assertSame(0, $fake->remaining()); // every generation call went through the bound client
        $this->assertTrue(User::where('email', 'demo@example.test')->sole()->training->generated);
    }

    public function test_login_url_reaches_the_training_page_with_the_tutor_chat(): void
    {
        $this->app['env'] = 'local';
        $this->artisan('demo:interactive')->assertExitCode(0);

        $this->get('/training?PROLIFIC_PID=' . DemoInteractive::prolificIdFor('demo@example.test'))
            ->assertOk()
            ->assertSee('data-tutor-completed="false"', false)
            ->assertSee('Exercise 1 of 4');
    }

    public function test_rerun_resets_the_same_participant(): void
    {
        $this->app['env'] = 'local';
        $this->artisan('demo:interactive')->assertExitCode(0);
        $user = User::where('email', 'demo@example.test')->sole();
        $first = $user->training->id;
        $this->get('/training?PROLIFIC_PID=' . $user->prolific_id)->assertOk();  // mounts the chat: creates the tutor session
        $this->assertSame(1, TutorSession::where('user_id', $user->id)->count());
        $user->forceFill(['training_completed' => now()])->save();

        $this->artisan('demo:interactive')->assertExitCode(0);

        $user->refresh();
        $this->assertSame(1, User::where('email', 'demo@example.test')->count());
        $this->assertNull($user->training_completed);
        $this->assertSame(1, Training::where('user_id', $user->id)->count());
        $this->assertNotSame($first, $user->training->id);
        $this->assertSame(0, TutorSession::where('user_id', $user->id)->count());
    }

    public function test_offline_demo_reaches_module_5_and_completes_the_training(): void
    {
        $this->app['env'] = 'local';
        $this->artisan('demo:interactive')->assertExitCode(0);
        $user = User::where('email', 'demo@example.test')->sole();
        $this->app->instance('tutor.llm', new DemoLlmClient());  // what TUTOR_FAKE=true binds in local
        $this->actingAs($user);

        // Module 4 with the offline tutor: answer every email until the sequence is completed.
        $chat = new TutorConversation($user);
        for ($i = 0; $i < 40 && ! $chat->session(fresh: true)->isCompleted(); $i++) {
            $chat->resetRateLimit();
            if ($chat->dialog()->phase === Phase::Closed) {
                $chat->advance();
                continue;
            }
            $this->assertTrue($chat->submit('phishing', 'The sender address domain looks wrong and the link button is odd')->ok());
        }
        $this->assertTrue($chat->session(fresh: true)->isCompleted());

        // Module 5: the generated recap is on the page, the thanks only after the revision.
        $this->get('/training?PROLIFIC_PID=' . $user->prolific_id)->assertOk()
            ->assertSee('data-rules-completed="false"', false)
            ->assertDontSee('data-role="thanks"', false);
        $rules = new PersonalRules($user->fresh());
        $this->assertTrue($rules->submitRules(['When an email asks for my password, I open the app myself.', 'When I am in a hurry, I read the sender address again.'])->ok());
        $comment = RulesComment::where('user_id', $user->id)->sole();
        $this->assertSame(RulesComment::SHOWN, $comment->status, json_encode($comment->validator_errors));
        $this->assertSame(DemoLlmClient::MODEL, $comment->model);
        $this->assertTrue($rules->submitRevision(['When an email asks for my password, I open the app myself.', 'When I am in a hurry, I read the sender address again.'])->ok());
        $this->get('/training?PROLIFIC_PID=' . $user->prolific_id)->assertOk()
            ->assertSee('data-rules-completed="true"', false)
            ->assertSee('Thank you for attending this training, Alice!');

        $this->get(route('training.complete', ['time' => 600]))->assertRedirect(route('emails', ['folder' => 'inbox']));
        $this->assertNotNull($user->fresh()->training_completed);
    }
}
