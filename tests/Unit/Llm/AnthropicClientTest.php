<?php

namespace Tests\Unit\Llm;

use App\Services\Llm\AnthropicClient;
use App\Services\Llm\LlmException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * AnthropicClient against a mocked transport (Guzzle MockHandler): no network calls.
 */
class AnthropicClientTest extends TestCase
{
    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    private MockHandler $mock;

    /** @var int[] timeouts requested from the transporter factory */
    private array $timeouts = [];

    private function client(array $responses, int $maxRetries = 0): AnthropicClient
    {
        $this->mock = new MockHandler($responses);
        $stack = HandlerStack::create($this->mock);
        $stack->push(Middleware::history($this->history));
        $http = new GuzzleClient(['handler' => $stack]);

        return new AnthropicClient(
            apiKey: 'test-key',
            model: 'claude-opus-5-5',
            effort: 'medium',
            timeout: 180,
            maxTokens: 4000,
            maxRetries: $maxRetries,
            transporterFactory: function (int $seconds) use ($http) {
                $this->timeouts[] = $seconds;
                return $http;
            },
            baseUrl: 'https://api.anthropic.test',
        );
    }

    private static function message(array $content, string $stopReason = 'end_turn', string $model = 'claude-opus-5-5'): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => 'msg_test',
            'type' => 'message',
            'role' => 'assistant',
            'model' => $model,
            'content' => $content,
            'stop_reason' => $stopReason,
            'stop_sequence' => null,
            'usage' => ['input_tokens' => 120, 'output_tokens' => 45],
        ]));
    }

    private static function error(int $status, string $type, string $message): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode([
            'type' => 'error',
            'error' => ['type' => $type, 'message' => $message],
        ]));
    }

    private function sentBody(int $i = 0): array
    {
        return json_decode((string) $this->history[$i]['request']->getBody(), true);
    }

    public function test_success_concatenates_text_blocks_and_sends_expected_request(): void
    {
        $client = $this->client([self::message([
            ['type' => 'thinking', 'thinking' => 'hidden reasoning', 'signature' => 'sig'],
            ['type' => 'text', 'text' => '<div>Hello'],
            ['type' => 'text', 'text' => ' Alice</div>'],
        ], 'end_turn', 'claude-opus-5-5')]);

        $res = $client->complete('You are a trainer.', [
            ['role' => 'user', 'content' => 'Intro'],
            ['role' => 'assistant', 'content' => '<div>Intro</div>'],
            ['role' => 'user', 'content' => 'Scenario'],
        ]);

        $this->assertSame('<div>Hello Alice</div>', $res->text);
        $this->assertSame('claude-opus-5-5', $res->model);
        $this->assertSame('end_turn', $res->stopReason);
        $this->assertSame(['input_tokens' => 120, 'output_tokens' => 45], $res->usage);

        $request = $this->history[0]['request'];
        $this->assertSame('/v1/messages', $request->getUri()->getPath());
        $this->assertSame('Bearer test-key', $request->getHeaderLine('Authorization'));  // SDK v1.8 auth scheme
        $this->assertStringContainsString(AnthropicClient::FALLBACK_BETA, $request->getHeaderLine('anthropic-beta'));

        $body = $this->sentBody();
        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame(4000, $body['max_tokens']);
        $this->assertSame('default', $body['fallbacks']);
        $this->assertSame('You are a trainer.', $body['system']);
        $this->assertSame(['effort' => 'medium'], $body['output_config']);
        $this->assertCount(3, $body['messages']);
        $this->assertSame('assistant', $body['messages'][1]['role']);
        foreach (['thinking', 'temperature', 'top_p', 'top_k'] as $absent) {
            $this->assertArrayNotHasKey($absent, $body);
        }
    }

    public function test_json_schema_is_sanitized_in_request_body_and_options_override_defaults(): void
    {
        $client = $this->client([self::message([['type' => 'text', 'text' => '{"tip":"ok"}']])]);

        $client->complete('sys', [['role' => 'user', 'content' => 'json please']], [
            'effort' => 'low',
            'max_tokens' => 500,
            'timeout' => 30,
            'json_schema' => [
                '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                'type' => 'object',
                'required' => ['tip'],
                'properties' => ['tip' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200]],
            ],
        ]);

        $config = $this->sentBody()['output_config'];
        $this->assertSame('low', $config['effort']);
        $this->assertSame('json_schema', $config['format']['type']);
        $schema = $config['format']['schema'];
        $this->assertArrayNotHasKey('$schema', $schema);
        $this->assertFalse($schema['additionalProperties']);
        $this->assertArrayNotHasKey('minLength', $schema['properties']['tip']);
        $this->assertArrayNotHasKey('maxLength', $schema['properties']['tip']);
        $this->assertSame(500, $this->sentBody()['max_tokens']);
        $this->assertSame([180, 30], $this->timeouts);  // default transporter + per-call one
    }

    public function test_refusal_is_not_retryable(): void
    {
        $client = $this->client([self::message([], 'refusal')]);

        try {
            $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmException');
        } catch (LlmException $e) {
            $this->assertFalse($e->retryable);
            $this->assertStringContainsString('refusal', $e->getMessage());
        }
    }

    public function test_max_tokens_throws(): void
    {
        $client = $this->client([self::message([['type' => 'text', 'text' => '<div>cut']], 'max_tokens')]);

        $this->expectException(LlmException::class);
        $this->expectExceptionMessage('max_tokens');
        $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
    }

    public function test_empty_text_throws(): void
    {
        $client = $this->client([self::message([['type' => 'thinking', 'thinking' => 'only thoughts', 'signature' => 's']])]);

        $this->expectException(LlmException::class);
        $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
    }

    public function test_rate_limit_is_retryable(): void
    {
        $client = $this->client([self::error(429, 'rate_limit_error', 'Too many requests')]);

        try {
            $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmException');
        } catch (LlmException $e) {
            $this->assertTrue($e->retryable);
            $this->assertStringContainsString('429', $e->getMessage());
            $this->assertStringNotContainsString('test-key', $e->getMessage());
        }
    }

    public function test_overloaded_is_retried_by_the_sdk_then_succeeds(): void
    {
        $client = $this->client([
            self::error(529, 'overloaded_error', 'Overloaded')->withHeader('retry-after-ms', '1'),
            self::message([['type' => 'text', 'text' => 'ok']]),
        ], maxRetries: 1);

        $this->assertSame('ok', $client->complete('sys', [['role' => 'user', 'content' => 'x']])->text);
        $this->assertCount(2, $this->history);
    }

    public function test_bad_request_is_not_retryable(): void
    {
        $client = $this->client([self::error(400, 'invalid_request_error', 'bad param')]);

        try {
            $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmException');
        } catch (LlmException $e) {
            $this->assertFalse($e->retryable);
            $this->assertStringContainsString('bad param', $e->getMessage());
        }
    }

    public function test_connection_error_is_retryable_but_not_a_timeout(): void
    {
        $client = $this->client([
            new \GuzzleHttp\Exception\ConnectException('Connection refused', new \GuzzleHttp\Psr7\Request('POST', '/'), null, ['errno' => 7]),
        ]);

        try {
            $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmException');
        } catch (LlmException $e) {
            $this->assertTrue($e->retryable);
            $this->assertNotInstanceOf(\App\Services\Llm\LlmTimeoutException::class, $e);
        }
    }

    public function test_transport_timeout_raises_llm_timeout_exception(): void
    {
        $client = $this->client([
            new \GuzzleHttp\Exception\ConnectException('cURL error 28: Operation timed out after 180001 milliseconds', new \GuzzleHttp\Psr7\Request('POST', '/'), null, ['errno' => 28]),
        ]);

        try {
            $client->complete('sys', [['role' => 'user', 'content' => 'x']]);
            $this->fail('Expected LlmTimeoutException');
        } catch (\App\Services\Llm\LlmTimeoutException $e) {
            $this->assertTrue($e->retryable);
            $this->assertMatchesRegularExpression('/timed out/i', $e->getMessage());
        }
    }

    public function test_reports_fallback_model_actually_used(): void
    {
        $client = $this->client([self::message([['type' => 'text', 'text' => 'ok']], 'end_turn', 'claude-sonnet-5-5')]);

        $this->assertSame('claude-sonnet-5-5', $client->complete('sys', [['role' => 'user', 'content' => 'x']])->model);
    }
}
