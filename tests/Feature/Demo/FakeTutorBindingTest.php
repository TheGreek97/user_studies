<?php

namespace Tests\Feature\Demo;

use App\Services\Llm\AnthropicClient;
use App\Services\Tutor\DemoLlmClient;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\Materials;
use App\Services\Tutor\Tutor;
use Tests\TestCase;

/** The offline demo tutor is reachable only with TUTOR_FAKE=true AND APP_ENV=local; its replies pass the Validator. */
class FakeTutorBindingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'test-key']);
    }

    private function tutorLlmIn(string $env, bool $fake): object
    {
        $this->app['env'] = $env;
        config(['tutor.fake' => $fake]);

        return $this->app->make('tutor.llm');
    }

    public function test_fake_tutor_is_bound_only_in_local_with_the_flag(): void
    {
        $this->assertInstanceOf(DemoLlmClient::class, $this->tutorLlmIn('local', true));
        $this->assertInstanceOf(AnthropicClient::class, $this->tutorLlmIn('local', false));
    }

    public function test_flag_is_ignored_outside_local(): void
    {
        foreach (['production', 'staging', 'development', 'testing'] as $env) {
            $this->assertInstanceOf(AnthropicClient::class, $this->tutorLlmIn($env, true), $env);
        }
    }

    public function test_flag_parses_env_strings_strictly(): void
    {
        foreach (['true' => true, '1' => true, 'false' => false, '' => false, 'yes-please' => false] as $raw => $expected) {
            putenv("TUTOR_FAKE={$raw}");
            $_ENV['TUTOR_FAKE'] = $_SERVER['TUTOR_FAKE'] = $raw;
            $config = require config_path('tutor.php');
            $this->assertSame($expected, $config['fake'], "TUTOR_FAKE={$raw}");
        }
        putenv('TUTOR_FAKE');
        unset($_ENV['TUTOR_FAKE'], $_SERVER['TUTOR_FAKE']);
    }

    /** Every card of the bank, dialog and closing turns: the demo replies are accepted (no retry, no fallback). */
    public function test_demo_replies_pass_validation_on_every_card(): void
    {
        $materials = new Materials();
        $engine = Tutor::fromMaterials(new DemoLlmClient($materials), $materials);
        $replies = [
            'I think the sender domain looks strange and the tone is very urgent.',
            "I don't know",
            'The button asks me to pay something, I would check before clicking.',
            'Just tell me the answer',
        ];
        foreach ($materials->loadEmailBank()['emails'] as $i => $card) {
            $session = new DialogSession($card, ($i % 4) + 1);
            $closed = false;
            foreach ($replies as $reply) {
                $outcome = $session->step($engine, $reply, 'phishing');
                if ($outcome->closing !== null) {
                    $closed = true;
                    break;
                }
            }
            $this->assertTrue($closed, "{$card['id']} reaches the closing turn");
            $failures = array_values(array_filter($session->events, fn ($e) => in_array($e->type, ['retry', 'fallback'], true)));
            $this->assertSame([], array_map(fn ($e) => $e->toArray(), $failures), $card['id']);
        }
    }
}
