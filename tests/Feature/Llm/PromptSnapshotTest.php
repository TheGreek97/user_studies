<?php

namespace Tests\Feature\Llm;

use App\Jobs\ProcessTraining;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Non-regression for the paper's conditions: the developer prompt and the 5 section prompts
 * must stay byte-identical to the snapshots taken before the `interactive` condition was added.
 *
 * The exercises prompt picks random services (array_rand), so the Mt generator is seeded.
 * Regenerate the snapshots only on purpose: UPDATE_PROMPT_SNAPSHOTS=1 phpunit --filter PromptSnapshotTest
 */
class PromptSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private const DIR = __DIR__ . '/../../fixtures/prompts';

    public static function conditions(): array
    {
        $cases = [];
        foreach (['no', 'yes', 'few_shot', 'table', 'primed'] as $personalization) {
            foreach (['short', 'long'] as $length) {
                $cases["$personalization-$length"] = [$personalization, $length];
            }
        }
        return $cases;
    }

    /**
     * @dataProvider conditions
     */
    public function test_prompts_of_existing_conditions_are_unchanged(string $personalization, string $length): void
    {
        $this->assertMatchesSnapshot($personalization, $length);
    }

    /**
     * The thesis condition: the snapshot documents the final prompts sent to the provider
     * (reviewed against prototipo/materiali/generation-prompts-diff.md).
     *
     * @testWith ["short"]
     *           ["long"]
     */
    public function test_prompts_of_interactive_condition_match_snapshot(string $length): void
    {
        $this->assertMatchesSnapshot('interactive', $length);
    }

    private function assertMatchesSnapshot(string $personalization, string $length): void
    {
        $user = ProcessTrainingTest::participant($personalization);
        $user->training_length = $length;
        $user->save();

        $actual = self::renderPrompts(new ProcessTraining(1, $user));
        $file = self::DIR . "/$personalization-$length.txt";

        if (getenv('UPDATE_PROMPT_SNAPSHOTS') === '1') {
            if (!is_dir(self::DIR)) {
                mkdir(self::DIR, 0777, true);
            }
            file_put_contents($file, $actual);
        }

        $this->assertFileExists($file);
        $this->assertSame(file_get_contents($file), $actual);
    }

    /** Developer prompt + section prompts joined with explicit separators. */
    public static function renderPrompts(ProcessTraining $job): string
    {
        $developer = new ReflectionMethod($job, 'getDeveloperPrompt');
        $developer->setAccessible(true);
        $sections = new ReflectionMethod($job, 'getSectionPrompts');
        $sections->setAccessible(true);

        mt_srand(42);
        $out = "=== developer ===\n" . $developer->invoke($job) . "\n";
        foreach ($sections->invoke($job) as $section => $prompt) {
            $out .= "=== $section ===\n$prompt\n";
        }
        mt_srand();

        return $out;
    }
}
