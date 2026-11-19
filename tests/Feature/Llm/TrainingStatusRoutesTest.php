<?php

namespace Tests\Feature\Llm;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TrainingStatusRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function failedTraining($user): Training
    {
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $training->update(['generation_failed_at' => now(), 'generation_error' => 'LlmException: boom']);

        return $training;
    }

    public function test_status_requires_authentication(): void
    {
        $this->getJson(route('training.status'))->assertStatus(401);
        $this->postJson(route('training.retry'))->assertStatus(401);
    }

    public function test_status_reports_own_training_state(): void
    {
        $user = ProcessTrainingTest::participant();
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);

        $this->actingAs($user->fresh())->getJson(route('training.status'))
            ->assertOk()->assertExactJson(['generated' => false, 'failed' => false]);

        $training->update(['generation_failed_at' => now()]);
        $this->actingAs($user->fresh())->getJson(route('training.status'))
            ->assertExactJson(['generated' => false, 'failed' => true]);

        $training->update(['generated' => true, 'generation_failed_at' => null]);
        $this->actingAs($user->fresh())->getJson(route('training.status'))
            ->assertExactJson(['generated' => true, 'failed' => false]);
    }

    public function test_retry_clears_failure_and_redispatches(): void
    {
        Queue::fake();
        $user = ProcessTrainingTest::participant();
        $training = $this->failedTraining($user);

        $this->actingAs($user)->post(route('training.retry'))->assertRedirect(route('training.show'));

        $training->refresh();
        $this->assertNull($training->generation_failed_at);
        $this->assertNull($training->generation_error);
        Queue::assertPushed(ProcessTraining::class, 1);
    }

    public function test_retry_is_refused_when_not_failed_and_never_touches_other_users(): void
    {
        Queue::fake();
        $owner = ProcessTrainingTest::participant();
        $ownerTraining = $this->failedTraining($owner);
        $other = ProcessTrainingTest::participant();
        Training::create(['user_id' => $other->id, 'generated' => false]);

        $this->actingAs($other)->post(route('training.retry'))->assertStatus(409);

        $this->assertNotNull($ownerTraining->refresh()->generation_failed_at);
        Queue::assertNothingPushed();
    }

    public function test_waiting_page_shows_retry_button_when_failed(): void
    {
        $user = ProcessTrainingTest::participant();
        $training = $this->failedTraining($user);
        $this->actingAs($user);

        $html = (string) $this->view('training.status_not_ready', ['training' => $training]);

        $this->assertStringContainsString(route('training.retry'), $html);
        $this->assertStringContainsString(json_encode(route('training.status')), $html);
        $this->assertStringContainsString('Try again', $html);
    }
}
