<?php

namespace Tests\Feature\Llm;

use App\Services\Llm\AnthropicClient;
use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmException;
use App\Services\Llm\OpenAiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiClientTest extends TestCase
{
    private function client(): OpenAiClient
    {
        return new OpenAiClient(apiKey: 'sk-test', model: 'o3-mini', reasoningEffort: 'medium', timeout: 60);
    }

    public function test_success_sends_paper_setup_and_returns_text_and_usage(): void
    {
        Http::fake([OpenAiClient::ENDPOINT => Http::response([
            'model' => 'o3-mini-2025-01-31',
            'choices' => [['message' => ['role' => 'assistant', 'content' => '<div>Hi</div>'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 300, 'completion_tokens' => 900],
        ])]);

        $res = $this->client()->complete('Developer prompt', [
            ['role' => 'user', 'content' => 'Intro'],
            ['role' => 'assistant', 'content' => '<div>Intro</div>'],
            ['role' => 'user', 'content' => 'Scenario'],
        ]);

        $this->assertSame('<div>Hi</div>', $res->text);
        $this->assertSame('o3-mini-2025-01-31', $res->model);
        $this->assertSame(['input_tokens' => 300, 'output_tokens' => 900], $res->usage);

        Http::assertSent(function (Request $request) {
            $messages = $request['messages'];
            return $request->hasHeader('Authorization', 'Bearer sk-test')
                && $request['model'] === 'o3-mini'
                && $request['reasoning_effort'] === 'medium'
                && !isset($request['temperature'])
                && $messages[0] === ['role' => 'developer', 'content' => 'Developer prompt']
                && count($messages) === 4
                && $messages[3]['content'] === 'Scenario';
        });
    }

    public function test_server_error_is_retryable_and_bad_request_is_not(): void
    {
        Http::fake([OpenAiClient::ENDPOINT => Http::sequence()
            ->push(['error' => ['message' => 'overloaded']], 503)
            ->push(['error' => ['message' => 'invalid model']], 400)]);

        foreach ([true, false] as $expected) {
            try {
                $this->client()->complete('sys', [['role' => 'user', 'content' => 'x']]);
                $this->fail('Expected LlmException');
            } catch (LlmException $e) {
                $this->assertSame($expected, $e->retryable);
            }
        }
    }

    public function test_transport_timeout_raises_llm_timeout_exception(): void
    {
        Http::fake(fn () => throw new \GuzzleHttp\Exception\ConnectException(
            'cURL error 28: Operation timed out after 60000 milliseconds',
            new \GuzzleHttp\Psr7\Request('POST', OpenAiClient::ENDPOINT),
            null,
            ['errno' => 28]
        ));

        try {
            $this->client()->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmTimeoutException');
        } catch (\App\Services\Llm\LlmTimeoutException $e) {
            $this->assertTrue($e->retryable);
            $this->assertMatchesRegularExpression('/timed out/i', $e->getMessage());
        }
    }

    public function test_truncated_or_empty_output_throws(): void
    {
        Http::fake([OpenAiClient::ENDPOINT => Http::sequence()
            ->push(['choices' => [['message' => ['content' => '<div>cut'], 'finish_reason' => 'length']]])
            ->push(['choices' => [['message' => ['content' => ''], 'finish_reason' => 'stop']]])]);

        foreach (['length', 'Empty'] as $expected) {
            try {
                $this->client()->complete('sys', [['role' => 'user', 'content' => 'x']]);
                $this->fail('Expected LlmException');
            } catch (LlmException $e) {
                $this->assertStringContainsString($expected, $e->getMessage());
            }
        }
    }

    public function test_container_binding_follows_training_provider(): void
    {
        config(['services.llm.training_provider' => 'openai']);
        $this->assertInstanceOf(OpenAiClient::class, $this->app->make(LlmClient::class));

        config(['services.llm.training_provider' => 'anthropic', 'services.anthropic.key' => 'test-key']);
        $this->assertInstanceOf(AnthropicClient::class, $this->app->make(LlmClient::class));
    }
}
