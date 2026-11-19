<?php

namespace Tests\Unit\Tutor;

use App\Services\Llm\LlmClient;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\FakeLlmClient;
use App\Services\Tutor\Materials;
use App\Services\Tutor\PromptBuilder;
use App\Services\Tutor\Tutor;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** Shared fixtures (port of tutor-core `test/helpers.ts`). Pure unit tests: no app, no DB, no network. */
abstract class TutorTestCase extends TestCase
{
    protected static ?Materials $materials = null;

    protected static array $bank;

    protected static array $schema;

    protected static string $template;

    public static function setUpBeforeClass(): void
    {
        if (self::$materials === null) {
            self::$materials = new Materials(dirname(__DIR__, 3).'/resources/tutor');
            self::$bank = self::$materials->loadEmailBank();
            self::$schema = self::$materials->loadOutputSchema();
            self::$template = PromptBuilder::extractSystemTemplate(self::$materials->loadPromptMarkdown());
        }
    }

    protected static function card(string $id): array
    {
        self::setUpBeforeClass();

        return Materials::findCard(self::$bank, $id);
    }

    /** Valid output for `$c`, with `$recognized` ids and every other id missing. */
    protected static function makeOutput(array $c, string $message, array $recognized = [], array $extra = []): array
    {
        return array_merge([
            'message' => $message,
            'recognized_clue_ids' => $recognized,
            'missing_clue_ids' => array_values(array_filter(Materials::cardIds($c), fn ($i) => ! in_array($i, $recognized, true))),
            'asked_for_answer' => false,
            'propose_close' => false,
            'focus_clue_id' => null,
            'user_said_dont_know' => false,
            'minimal_reply' => false,
            'unsupported_claims' => [],
            'off_topic' => false,
        ], $extra);
    }

    /** Scripted engine; `$client` is the FakeLlmClient for call assertions. */
    protected static function engine(array $script, int $timeoutMs = 1000, ?FakeLlmClient &$client = null): Tutor
    {
        $client = new FakeLlmClient($script);

        return self::engineWith($client, $timeoutMs);
    }

    protected static function engineWith(LlmClient $client, int $timeoutMs = 1000): Tutor
    {
        return new Tutor($client, self::$schema, self::$template, $timeoutMs);
    }

    protected static function fixedClock(): callable
    {
        return fn () => new DateTimeImmutable('2026-11-12T10:00:00Z');
    }
}
