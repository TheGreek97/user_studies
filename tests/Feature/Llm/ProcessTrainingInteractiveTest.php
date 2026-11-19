<?php

namespace Tests\Feature\Llm;

use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmResponse;
use App\Services\Training\InteractiveBlockException;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\Materials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Training\InteractiveFixtures;

class ProcessTrainingInteractiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_interactive_training_and_saves_the_structured_data(): void
    {
        $user = ProcessTrainingTest::participant('interactive');
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $llm = new ScriptedLlmClient(InteractiveFixtures::answers());

        (new ProcessTraining($training->id, $user))->handle($llm);

        // The name never reaches the provider; the placeholder and the tenth subscale do.
        $this->assertCount(5, $llm->calls);
        foreach ($llm->calls as $call) {
            $this->assertStringNotContainsString('Alice', $call['system'] . json_encode($call['messages']));
        }
        $system = $llm->calls[0]['system'];
        $this->assertStringContainsString('write the placeholder {{first_name}} exactly as shown', $system);
        $this->assertStringContainsString('Positive attitudes towards advertising = 3', $system);
        $this->assertStringContainsString('data-role="..."> blocks', $system);
        $this->assertStringContainsString('TRAIT DESCRIPTIONS', $system);
        $prompts = array_map(fn ($call) => end($call['messages'])['content'], $llm->calls);
        $this->assertStringContainsString('data-role="scenario-header"', $prompts[0]);
        $this->assertStringContainsString('data-role="scenario-email"', $prompts[1]);
        $this->assertStringContainsString('data-role="defense-guidelines"', $prompts[2]);
        $this->assertStringContainsString('do not write any email, form, button or feedback', $prompts[3]);
        $this->assertStringNotContainsString('namely', $prompts[3]);
        $this->assertStringContainsString('data-role="thanks"', $prompts[4]);
        // The context keeps the raw answers: the scenario must see the header it has to develop.
        $this->assertStringContainsString('data-role="scenario-header"', $llm->calls[1]['messages'][1]['content']);

        $training->refresh();
        $this->assertTrue($training->generated);
        foreach (ProcessTraining::SECTIONS as $section) {
            $this->assertStringNotContainsString('<script', $training->$section);
        }
        $this->assertSame('<div><p>Hello {{first_name}}, welcome.</p></div>', $training->introduction);
        $this->assertStringContainsString('data-role="decision-point"', $training->scenario);
        $this->assertSame('<div><p>Now some exercises.</p></div>', $training->exercises);

        $data = $training->interactive_data;
        $this->assertEquals(InteractiveFixtures::header(), $data['scenario_header']);  // MySQL JSON reorders keys
        $this->assertEquals(InteractiveFixtures::email()['clues'], $data['scenario_email']['clues']);
        $this->assertEquals(InteractiveFixtures::guidelines(), $data['defense_guidelines']);
        $this->assertSequenceFollowsTheBank($data['m4']['sequence']);
        $this->assertIsInt($data['m4']['seed']);
        $this->assertSame($data['m4']['sequence'], DialogSession::drawSequence($this->bankMeta(), $data['m4']['seed'])['sequence']);
    }

    public function test_invalid_block_fails_the_attempt_before_the_next_calls(): void
    {
        $user = ProcessTrainingTest::participant('interactive');
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $answers = InteractiveFixtures::answers();
        $answers[0] = '<div><p>Hello {{first_name}}.</p></div>';  // scenario-header missing
        $llm = new ScriptedLlmClient($answers);

        try {
            (new ProcessTraining($training->id, $user))->handle($llm);
            $this->fail('Expected InteractiveBlockException');
        } catch (InteractiveBlockException $e) {
            $this->assertTrue($e->retryable);
        }

        $this->assertCount(1, $llm->calls);
        $training->refresh();
        $this->assertFalse($training->generated);
        $this->assertNull($training->introduction);
        $this->assertNull($training->interactive_data);
    }

    public function test_invalid_block_after_last_attempt_marks_training_as_failed(): void
    {
        $user = ProcessTrainingTest::participant('interactive');
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $answers = InteractiveFixtures::answers();
        $answers[2] = '<div><script type="application/json" data-role="defense-guidelines">[{"id": "g1",</script></div>';
        $this->app->instance(LlmClient::class, new ScriptedLlmClient($answers));

        try {
            ProcessTraining::dispatchSync($training->id, $user);
        } catch (InteractiveBlockException) {
            // the sync queue calls failed() and rethrows
        }

        $training->refresh();
        $this->assertFalse($training->generated);
        $this->assertNotNull($training->generation_failed_at);
        $this->assertStringContainsString('InteractiveBlockException: Malformed JSON', $training->generation_error);
    }

    public function test_paper_conditions_do_not_store_interactive_data(): void
    {
        $user = ProcessTrainingTest::participant('yes');
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);

        (new ProcessTraining($training->id, $user))->handle(new FakeLlmClient());

        $training->refresh();
        $this->assertTrue($training->generated);
        $this->assertNull($training->interactive_data);
    }

    public function test_module_four_sequence_inserts_the_legitimate_email_between_positions_2_and_4(): void
    {
        $positions = [];
        for ($i = 0; $i < 60; $i++) {
            $draw = ProcessTraining::moduleFourSequence();
            $sequence = $draw['sequence'];
            $this->assertSequenceFollowsTheBank($sequence);
            $this->assertSame($draw['legitimate_position'], array_search($this->bankMeta()['legitimate_main'], $sequence, true) + 1);
            $positions[array_search($this->bankMeta()['legitimate_main'], $sequence, true) + 1] = true;
        }
        ksort($positions);
        $this->assertSame([2, 3, 4], array_keys($positions));
    }

    private function bankMeta(): array
    {
        return (new Materials())->loadEmailBank()['_meta'];
    }

    private function assertSequenceFollowsTheBank(array $sequence): void
    {
        $meta = $this->bankMeta();
        $this->assertCount(count($meta['default_sequence']) + 1, $sequence);
        $position = array_search($meta['legitimate_main'], $sequence, true);
        $this->assertNotFalse($position);
        $this->assertGreaterThanOrEqual(1, $position);
        $phishing = array_values(array_diff($sequence, [$meta['legitimate_main']]));
        $this->assertSame($meta['default_sequence'], $phishing);
    }
}

/**
 * LlmClient that answers with the given texts in order and records the calls.
 */
class ScriptedLlmClient implements LlmClient
{
    public array $calls = [];

    public function __construct(private array $answers)
    {
    }

    public function complete(string $system, array $messages, array $options = []): LlmResponse
    {
        $this->calls[] = ['system' => $system, 'messages' => $messages, 'options' => $options];

        return new LlmResponse($this->answers[count($this->calls) - 1], 'fake-model', 'end_turn', ['input_tokens' => 10, 'output_tokens' => 5]);
    }
}
