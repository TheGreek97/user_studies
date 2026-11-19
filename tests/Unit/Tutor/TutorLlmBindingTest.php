<?php

namespace Tests\Unit\Tutor;

use App\Services\Llm\AnthropicClient;
use Tests\TestCase;

class TutorLlmBindingTest extends TestCase
{
    public function test_tutor_llm_binding_is_an_anthropic_client_independent_of_training_provider(): void
    {
        config(['services.llm.training_provider' => 'openai', 'services.anthropic.key' => 'test-key']);

        $this->assertInstanceOf(AnthropicClient::class, $this->app->make('tutor.llm'));
    }
}
