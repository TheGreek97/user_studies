<?php

namespace Tests\Feature\Llm;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Models\User;
use App\Models\UserQuestionnaireScale;
use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmException;
use App\Services\Llm\LlmResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTrainingTest extends TestCase
{
    use RefreshDatabase;

    public static function participant(string $personalization = 'yes'): User
    {
        $user = User::factory()->create([
            'name' => 'Alice',
            'training_personalization' => $personalization,
            'training_length' => 'short',
        ]);
        $scales = [];
        foreach ((new UserQuestionnaireScale())->getFillable() as $column) {
            if ($column !== 'user_id') {
                $scales[$column] = 3;
            }
        }
        UserQuestionnaireScale::create(['user_id' => $user->id] + $scales);

        return $user;
    }

    public function test_generates_five_sections_with_growing_context_and_saves_usage(): void
    {
        $user = self::participant();
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $llm = new FakeLlmClient();

        (new ProcessTraining($training->id, $user))->handle($llm);

        $this->assertCount(5, $llm->calls);
        foreach ($llm->calls as $i => $call) {
            // Same developer prompt as system on every call; context = previous (user, assistant) pairs + new prompt.
            $this->assertStringContainsString('Alice', $call['system']);
            $this->assertSame($llm->calls[0]['system'], $call['system']);
            $this->assertCount(2 * $i + 1, $call['messages']);
            $this->assertSame('user', end($call['messages'])['role']);
            if ($i > 0) {
                $this->assertSame('<div>section ' . $i . '</div>', $call['messages'][2 * $i - 1]['content']);
                $this->assertSame('assistant', $call['messages'][2 * $i - 1]['role']);
            }
        }

        $training->refresh();
        $this->assertTrue($training->generated);
        foreach (ProcessTraining::SECTIONS as $i => $section) {
            $this->assertSame('<div>section ' . ($i + 1) . '</div>', $training->$section);
        }
        $this->assertSame('fake-model', $training->llm_model);
        $this->assertEquals(['input_tokens' => 500, 'output_tokens' => 250, 'calls' => 5], $training->llm_usage);
        $this->assertNull($training->generation_failed_at);
    }

    public function test_nothing_is_saved_when_a_call_fails(): void
    {
        $user = self::participant();
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $llm = new FakeLlmClient(failOnCall: 3);

        try {
            (new ProcessTraining($training->id, $user))->handle($llm);
            $this->fail('Expected LlmException');
        } catch (LlmException $e) {
            $this->assertTrue($e->retryable);
        }

        $training->refresh();
        $this->assertFalse($training->generated);
        $this->assertNull($training->introduction);
    }

    public function test_failed_marks_training_as_failed_after_sync_dispatch(): void
    {
        $user = self::participant();
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $this->app->instance(LlmClient::class, new FakeLlmClient(failOnCall: 1));

        try {
            ProcessTraining::dispatchSync($training->id, $user);
        } catch (LlmException) {
            // the sync queue calls failed() and rethrows
        }

        $training->refresh();
        $this->assertFalse($training->generated);
        $this->assertNotNull($training->generation_failed_at);
        $this->assertStringContainsString('upstream overloaded', $training->generation_error);
        $this->assertLessThanOrEqual(500, mb_strlen($training->generation_error));
    }

    public function test_job_robustness_settings(): void
    {
        config(['services.llm.timeout' => 100]);
        $job = new ProcessTraining(1, self::participant());

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120], $job->backoff);
        $this->assertSame(560, $job->timeout);
        $this->assertGreaterThan($job->timeout, config('queue.connections.database.retry_after'));
    }
}

/**
 * In-memory LlmClient: records the calls and answers "<div>section N</div>".
 */
class FakeLlmClient implements LlmClient
{
    public array $calls = [];

    public function __construct(private ?int $failOnCall = null)
    {
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $this->calls[] = ['system' => $system, 'messages' => $messages, 'options' => $options];
        $n = count($this->calls);
        if ($n === $this->failOnCall) {
            throw new LlmException('upstream overloaded (HTTP 529)', true);
        }

        return new LlmResponse("<div>section $n</div>", 'fake-model', 'end_turn', ['input_tokens' => 100, 'output_tokens' => 50]);
    }
}
