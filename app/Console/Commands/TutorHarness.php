<?php

namespace App\Console\Commands;

use App\Services\Llm\LlmClient;
use App\Services\Tutor\Harness\HarnessRunner;
use App\Services\Tutor\Materials;
use Illuminate\Console\Command;

/**
 * Runs the 22 test cases of the Module 4 tutor (test-dialogs.md) and writes a markdown report
 * (rule x case) to storage/app/tutor-reports/. `--provider=llm` uses the container-bound LlmClient:
 * real model calls, real cost (about 30 calls per run).
 */
class TutorHarness extends Command
{
    protected $signature = 'tutor:harness
        {--runs=1 : executions per case (test-dialogs.md asks for at least 3 with a real model)}
        {--provider=fake : fake (scripted ideal outputs, no network) or llm (container LlmClient)}
        {--timeout=30000 : per-call timeout in ms}';

    protected $description = 'Run the Module 4 tutor harness (22 cases) and write a markdown report';

    public function handle(): int
    {
        $provider = (string) $this->option('provider');
        if (! in_array($provider, ['fake', 'llm'], true)) {
            $this->error("Unknown provider: {$provider} (fake|llm)");

            return 2;
        }
        $runs = max(1, (int) $this->option('runs'));
        $runner = new HarnessRunner(
            new Materials(),
            $provider,
            $provider === 'llm' ? app(LlmClient::class) : null,
            (int) $this->option('timeout'),
            $runs,
        );
        $report = $runner->report($runner->run());

        $dir = storage_path('app/tutor-reports');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir.'/harness-'.$provider.'-'.gmdate('Y-m-d\TH-i-s').'.md';
        file_put_contents($path, $report['text']);

        $this->line($report['summary']);
        $this->info("Report: {$path}");

        return $report['failed'] ? 1 : 0;
    }
}
