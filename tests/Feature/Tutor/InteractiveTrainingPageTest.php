<?php

namespace Tests\Feature\Tutor;

use App\Http\Controllers\TrainingController;
use App\Models\Training;
use App\Models\TrainingResponse;
use App\Models\TutorSession;
use App\Services\Tutor\FakeLlmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Llm\ProcessTrainingTest;
use Tests\TestCase;

/** Training page of the "interactive" condition: Module 4 mounts the tutor chat and gates "Next" on its completion. */
class InteractiveTrainingPageTest extends TestCase
{
    use RefreshDatabase;

    private const GUIDELINES = [
        ['id' => 'g1', 'text' => 'Check the domain after the @ in the sender address', 'email_part' => 'sender'],
        ['id' => 'g2', 'text' => 'Hover over links before clicking them', 'email_part' => 'link'],
    ];

    private function render(string $personalization, array $interactiveData = []): array
    {
        $this->app->instance('tutor.llm', new FakeLlmClient([])); // rendering never calls the model
        $user = ProcessTrainingTest::participant($personalization);
        Training::create([
            'user_id' => $user->id,
            'generated' => true,
            'introduction' => '<p>Intro</p>',
            'scenario' => '<p>Scenario</p>',
            'defense_strategies' => '<p>Strategies</p>',
            'exercises' => '<p>Exercises</p>',
            'conclusions' => '<p>Recap</p>',
            'interactive_data' => $interactiveData ?: null,
        ]);
        $this->actingAs($user = $user->fresh());

        return [$user, app(TrainingController::class)->showTraining()->render()];
    }

    public function test_interactive_page_mounts_the_tutor_with_guidelines_and_gates_next(): void
    {
        $sequence = ['m4-p1-icloud', 'm4-p2-airbnb', 'm4-l1-airbnb', 'm4-p3-coworker'];
        [$user, $html] = $this->render('interactive', ['defense_guidelines' => self::GUIDELINES, 'm4' => ['sequence' => $sequence, 'seed' => 7]]);

        // Chat mounted inside Module 4, not completed yet.
        $this->assertStringContainsString('wire:id', $html);
        $this->assertStringContainsString('Exercise 1 of 4', $html);
        $this->assertStringContainsString('data-tutor-completed="false"', $html);

        // Guidelines of the generated training passed as they are to the dialog (prompt input).
        $session = TutorSession::where('user_id', $user->id)->sole();
        $this->assertSame($sequence, $session->sequence);
        $this->assertSame(7, $session->seed);
        $this->assertEquals(self::GUIDELINES, $session->state['module3_guidelines']);

        // "Next" gated on Module 4 until tutor-completed (event or data attribute after a reload).
        $this->assertStringContainsString('x-on:tutor-completed.window="tutorDone = true"', $html);
        $this->assertStringContainsString(':disabled="locked()"', $html);
        $this->assertStringContainsString('if (this.locked()) {', $html);
        $this->assertStringContainsString('|| (this.step === 4 && !this.tutorDone) || (this.step === 5 && !this.rulesDone);', $html);
        $this->assertStringContainsString('document.querySelector(\'[data-tutor-completed="true"]\')', $html);
    }

    public function test_interactive_page_is_responsive_and_paper_markup_is_unchanged(): void
    {
        [, $html] = $this->render('interactive', ['defense_guidelines' => self::GUIDELINES]);
        $this->assertStringContainsString('<div class="p-4 md:p-12 m-2 md:m-10 min-w-0 max-w-full bg-white md:min-w-100 rounded-2xl shadow-xl z-20">', $html);
        $this->assertStringContainsString('<div class="text-xl text-left md:text-justify">', $html);
        $this->assertStringNotContainsString('<div class="text-xl text-justify">', $html);
        $this->assertStringContainsString('<div class="mt-8 min-w-0 max-w-full">', $html); // chat spaced from the intro
        $this->assertStringContainsString('[&_table]:w-full', $html); // email body tables fit the card
        $this->assertStringContainsString('class="py-3 w-full md:w-64 text-lg text-black', $html); // nav buttons fit 390px

        foreach (['yes', 'no'] as $personalization) {
            [, $html] = $this->render($personalization);
            $this->assertStringContainsString('<div class="p-12 m-10 bg-white md:min-w-100 rounded-2xl shadow-xl z-20">', $html);
            $this->assertSame(5, substr_count($html, '<div class="text-xl text-justify">'));
            $this->assertStringContainsString('class="py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl"', $html);
            $this->assertStringContainsString(':class="{\'w-full\' : step === 0, \'w-64\' : step > 0}"', $html);
            foreach (['p-4 md:p-12', 'm-2 md:m-10', 'min-w-0', 'text-left md:text-justify', 'mt-8 min-w-0', 'tutor-exercise-heading', 'md:w-64', 'space-x-3'] as $interactiveOnly) {
                $this->assertStringNotContainsString($interactiveOnly, $html);
            }
            $this->assertStringContainsString("            return {\n                step: 0,\n                canProceed: 1,", $html);
        }
    }

    public function test_navigation_script_uses_vanilla_js_not_jquery(): void
    {
        foreach (['yes', 'no', 'interactive'] as $personalization) {
            [, $html] = $this->render($personalization, $personalization === 'interactive' ? ['defense_guidelines' => self::GUIDELINES] : []);
            // jQuery is never loaded: $(...) threw a ReferenceError on every click (upstream bug).
            $this->assertStringNotContainsString('$(window)', $html);
            $this->assertStringNotContainsString('$("#next")', $html);
            $this->assertSame(2, substr_count($html, 'window.scrollTo(0, 0);'));
            $this->assertStringContainsString('document.getElementById(\'next\').textContent = this.step === num_steps ? "Proceed to last phase" : "Next";', $html);
            // Label set on init only in the interactive branch (paper pages unchanged).
            $this->assertSame($personalization === 'interactive' ? 1 : 0, substr_count($html, 'resumed at the last module'));
        }
    }

    public function test_reload_reopens_module_4_while_the_tutor_chat_is_unfinished_then_module_5(): void
    {
        [$user, $html] = $this->render('interactive', ['defense_guidelines' => self::GUIDELINES]);
        // First view: the chat mounts and creates the session, but nothing was written yet.
        $this->assertStringContainsString('step: 0,', $html);
        $show = fn () => app(TrainingController::class)->showTraining()->render();
        $this->assertStringContainsString('step: 0,', $show());

        // A message was sent (dialog state saved): reload opens Module 4.
        $initialStep = fn () => app(TrainingController::class)->showTraining()->getData()['initial_step'];
        $session = TutorSession::where('user_id', $user->id)->sole();
        $this->assertSame([], $session->state['history']);
        $session->update(['state' => ['history' => [['author' => 'user', 'text' => 'The sender looks odd']]] + $session->state]);
        $this->assertSame(4, $initialStep());

        // Between two emails (state reset, position advanced) it still resumes at Module 4, whose timer restarts.
        $session->update(['state' => null, 'position' => 2]);
        $html = $show();
        $this->assertStringContainsString('step: 4,', $html);
        $this->assertStringContainsString('if (this.step > 0) {', $html);
        $this->assertStringContainsString('Exercise 2 of', $html);

        // Completed: Module 5 (the next gated module), personal rules still to write.
        $session->update(['completed_at' => now()]);
        $this->assertSame(5, $initialStep());

        // Module 5 personal rules already sent (revision pending or done): still the conclusions.
        TrainingResponse::record($user->id, $user->training->id, 5, 'rules_v1', 'r', ['rules' => ['r']]);
        $this->assertSame(5, $initialStep());
        // Reopened at the last module, the button label is the one next() sets when reaching it.
        $html = $show();
        $this->assertStringContainsString('step: 5,', $html);
        $this->assertStringContainsString("if (this.step === num_steps) {\n                        // resumed at the last module: same label next() sets when reaching it\n                        document.getElementById('next').textContent = \"Proceed to last phase\";", $html);
    }

    public function test_paper_conditions_always_start_at_step_0(): void
    {
        foreach (['yes', 'no'] as $personalization) {
            [$user] = $this->render($personalization);
            $this->assertSame(0, app(TrainingController::class)->showTraining()->getData()['initial_step']);
        }
    }

    public function test_completed_module_is_flagged_on_reload(): void
    {
        [$user] = $this->render('interactive', ['defense_guidelines' => self::GUIDELINES]);
        TutorSession::where('user_id', $user->id)->update(['completed_at' => now()]);

        $html = app(TrainingController::class)->showTraining()->render();
        $this->assertStringContainsString('data-tutor-completed="true"', $html);
    }

    public function test_paper_conditions_have_no_tutor_and_no_gate(): void
    {
        foreach (['yes', 'no'] as $personalization) {
            [, $html] = $this->render($personalization);
            $this->assertStringNotContainsString('tutorDone', $html);
            $this->assertStringNotContainsString('data-tutor-completed', $html);
        }
        $this->assertSame(0, TutorSession::count());
    }

    /** Participant at the training phase (everything before the training completed), as RedirectToTheRightPhase expects. */
    private function atTrainingPhase(string $personalization): \App\Models\User
    {
        [$user] = $this->render($personalization, $personalization === 'interactive' ? ['defense_guidelines' => self::GUIDELINES] : []);
        $user->forceFill([
            'given_consent' => now(), 'demographics_completed' => now(), 'bfi_completed' => now(), 'stp_completed' => now(),
            'teique_completed' => now(), 'pre_training_completed' => now(),
        ])->save();

        return $user->fresh();
    }

    public function test_interactive_training_cannot_be_completed_before_the_tutor(): void
    {
        $user = $this->atTrainingPhase('interactive');
        $this->assertFalse(TutorSession::where('user_id', $user->id)->sole()->isCompleted());

        $this->actingAs($user)->get(route('training.complete', ['time' => 42]))
            ->assertRedirect(route('training.show'))
            ->assertSessionHas('error');
        $user->refresh();
        $this->assertNull($user->training_completed);
        $this->assertSame(0, $user->training->time_taken);

        // Without any tutor session at all (chat never mounted) the gate holds as well.
        TutorSession::where('user_id', $user->id)->delete();
        $this->get(route('training.complete'))->assertRedirect(route('training.show'));
        $this->assertNull($user->fresh()->training_completed);

        // The error is shown on the training page.
        $this->withSession(['error' => 'Please complete the Module 4 exercises'])->get(route('training.show'))
            ->assertSee('Please complete the Module 4 exercises');
    }

    public function test_interactive_training_completes_after_the_tutor_and_the_personal_rules(): void
    {
        $user = $this->atTrainingPhase('interactive');
        TutorSession::where('user_id', $user->id)->update(['completed_at' => now()]);

        // Module 4 done, Module 5 not: refused. Rules sent but not revised (rules_v1 only): still refused.
        $this->actingAs($user)->get(route('training.complete', ['time' => 42]))
            ->assertRedirect(route('training.show'))
            ->assertSessionHas('error', 'Please write and save your personal rules in the Conclusion before finishing the training.');
        TrainingResponse::record($user->id, $user->training->id, 5, 'rules_v1', 'r', ['rules' => ['r']]);
        $this->get(route('training.complete', ['time' => 42]))->assertRedirect(route('training.show'));
        $this->assertNull($user->fresh()->training_completed);
        // rules_v2 of ANOTHER training of the same user (e.g. a reset) does not count.
        TrainingResponse::record($user->id, null, 5, 'rules_v2', 'r', ['rules' => ['r']]);
        $this->get(route('training.complete', ['time' => 42]))->assertRedirect(route('training.show'));

        TrainingResponse::record($user->id, $user->training->id, 5, 'rules_v2', 'r', ['rules' => ['r']]);
        $this->get(route('training.complete', ['time' => 42]))
            ->assertRedirect(route('emails', ['folder' => 'inbox']));
        $this->assertNotNull($user->fresh()->training_completed);
        $training = $user->fresh()->training;
        $this->assertEquals(42, $training->time_taken);
    }

    public function test_paper_conditions_complete_without_a_tutor(): void
    {
        $user = $this->atTrainingPhase('yes');

        $this->actingAs($user)->get(route('training.complete', ['time' => 315]))->assertRedirect(route('emails', ['folder' => 'inbox']));
        $this->assertNotNull($user->fresh()->training_completed);
        $this->assertEquals(315, $user->fresh()->training->time_taken);
    }
}
