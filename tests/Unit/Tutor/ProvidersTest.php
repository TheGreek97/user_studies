<?php

namespace Tests\Unit\Tutor;

use App\Services\Llm\LlmException;
use App\Services\Tutor\FakeLlmClient;
use App\Services\Tutor\HelpLevel;
use App\Services\Tutor\PromptBuilder;
use App\Services\Tutor\TurnType;
use App\Services\Tutor\TutorTimeoutException;
use App\Services\Tutor\ValidationContext;
use App\Services\Tutor\Validator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Port of test/providers.test.ts. The TS MockProvider becomes FakeLlmClient; the OpenAI request tests become
 * checks of the LlmClient request the tutor builds (the provider-specific schema reduction lives in the
 * LlmClient implementations, so here we check that the FULL schema is sent and re-validated locally).
 */
class ProvidersTest extends TutorTestCase
{
    public function test_fake_client_replays_the_script_in_order_and_fails_when_exhausted(): void
    {
        $p = new FakeLlmClient(['a', ['json' => ['x' => 1]], ['error' => 'boom']]);
        $this->assertSame('a', $p->complete('SYS', [])->text);
        $this->assertSame('{"x":1}', $p->complete('SYS', [])->text);
        try {
            $p->complete('SYS', []);
            $this->fail('expected boom');
        } catch (LlmException $e) {
            $this->assertSame('boom', $e->getMessage());
        }
        try {
            $p->complete('SYS', []);
            $this->fail('expected exhausted');
        } catch (LlmException $e) {
            $this->assertMatchesRegularExpression('/exhausted/', $e->getMessage());
        }
        $this->assertCount(4, $p->calls);
    }

    public function test_a_hang_ends_only_as_a_timeout(): void
    {
        $engine = self::engine([['hang' => true]], 30, $client);
        $this->expectException(TutorTimeoutException::class);
        $this->expectExceptionMessageMatches('/no reply within 30 ms/');
        $engine->callWithTimeout('SYS', 'USR');
    }

    public function test_builds_the_llm_request_with_the_full_schema_low_effort_and_timeout(): void
    {
        $engine = self::engine(['{}'], 20000, $client);
        $engine->callWithTimeout('SYS', 'USR');
        $call = $client->calls[0];
        $this->assertSame('SYS', $call['system']);
        $this->assertSame([['role' => 'user', 'content' => 'USR']], $call['messages']);
        $this->assertSame(self::$schema, $call['options']['json_schema']);
        $this->assertSame(self::$schema['required'], $call['options']['json_schema']['required']);
        $this->assertSame('low', $call['options']['effort']);
        $this->assertSame(20, $call['options']['timeout']);
        $this->assertDoesNotMatchRegularExpression('/Bearer|api[_-]?key/i', json_encode($call['options']));
        $this->assertSame(PromptBuilder::TURN_INSTRUCTION, PromptBuilder::buildPrompt(self::$template, (new \App\Services\Tutor\DialogSession(self::card('m4-p1-icloud'), 1))->promptContext(TurnType::Dialog))->user);
    }

    public function test_keywords_a_provider_may_strip_are_still_enforced_locally(): void
    {
        $c = self::card('m4-p3-coworker');
        $ctx = new ValidationContext($c, TurnType::Dialog, HelpLevel::Low, false, ['x'], []);
        $bad = [
            ['focus_clue_id' => 'x9'],                                      // pattern
            ['recognized_clue_ids' => ['c1', 'c1']],                        // uniqueItems
            ['message' => ''],                                              // minLength
            ['message' => str_repeat('a', 901)],                            // maxLength
            ['unsupported_claims' => [str_repeat('b', 201)]],               // items.maxLength
        ];
        foreach ($bad as $over) {
            $raw = json_encode(self::makeOutput($c, 'What do you notice?', [], $over));
            $this->assertSame(['K1:schema'], Validator::validateTutorReply($raw, self::$schema, $ctx)->keys(), json_encode($over));
        }
    }

    public function test_no_file_contains_something_that_looks_like_an_api_key(): void
    {
        $root = dirname(__DIR__, 3);
        $files = [];
        foreach (['app/Services/Tutor', 'resources/tutor', 'tests/Unit/Tutor', 'config/tutor.php'] as $rel) {
            $path = "{$root}/{$rel}";
            if (is_file($path)) {
                $files[] = $path;

                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)) as $f) {
                if (preg_match('/\.(php|json|md)$/', $f->getFilename())) {
                    $files[] = $f->getPathname();
                }
            }
        }
        $this->assertGreaterThan(5, count($files));
        foreach ($files as $f) {
            $this->assertDoesNotMatchRegularExpression('/sk-(proj-|ant-)?[A-Za-z0-9_-]{20,}/', file_get_contents($f), $f);
        }
    }
}
