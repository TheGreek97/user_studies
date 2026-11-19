<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\Harness\HarnessCases;
use App\Services\Tutor\Harness\HarnessRunner;

/**
 * The 22 cases of harness/cases.ts run with the fake client (ideal outputs, like the TS MockProvider):
 * every automatic check must pass, with the same per-rule totals as the TS harness report.
 */
class HarnessCasesTest extends TutorTestCase
{
    private static ?array $runs = null;

    private static function runs(): array
    {
        self::setUpBeforeClass();

        return self::$runs ??= (new HarnessRunner(self::$materials, 'fake'))->run();
    }

    public static function caseIds(): array
    {
        $ids = array_values(array_unique(array_map(fn ($c) => $c['id'], HarnessCases::all())));

        return array_combine($ids, array_map(fn ($id) => [$id], $ids));
    }

    /** @dataProvider caseIds */
    public function test_harness_case_passes_every_automatic_check(string $id): void
    {
        $runs = array_values(array_filter(self::runs(), fn ($r) => $r['id'] === $id));
        $this->assertNotEmpty($runs);
        foreach ($runs as $r) {
            $this->assertNotEmpty($r['checks'], $r['key']);
            $failed = array_map(fn ($c) => $c['id'].' '.($c['detail'] ?? ''), array_values(array_filter($r['checks'], fn ($c) => ! $c['pass'])));
            $this->assertSame([], $failed, $r['key']);
        }
    }

    public function test_spec_has_22_cases_and_24_variants_aligned_with_test_dialogs(): void
    {
        $this->assertCount(22, self::caseIds());
        $this->assertCount(24, array_unique(array_map(fn ($r) => $r['key'], self::runs())));
        $runner = new HarnessRunner(self::$materials, 'fake');
        $this->assertSame([], $runner->driftWarnings($runner->parseMd()['cases']));
    }

    /** Same numbers as the "Per regola" table of the TS report (reports/harness-mock-2026-11-12T11-13-49.md). */
    public function test_per_rule_totals_match_the_ts_harness(): void
    {
        $this->assertSame([
            'K1' => [23, 23], 'G1' => [27, 27], 'G2' => [4, 4], 'G3' => [28, 28], 'G4' => [6, 6], 'G5' => [22, 22],
            'G6' => [26, 26], 'G7' => [6, 6], 'G8' => [23, 23], 'G9' => [32, 32], 'G10' => [1, 1], 'G11' => [0, 0],
            '4.4.4' => [5, 5], 'Piattaforma' => [28, 28],
        ], HarnessRunner::ruleTotals(self::runs()));
    }

    public function test_report_has_rule_and_case_tables(): void
    {
        $runner = new HarnessRunner(self::$materials, 'fake');
        $report = $runner->report(self::runs());
        $this->assertFalse($report['failed']);
        $this->assertStringContainsString('| 22 | 22 | 0 | 24 | 75 |', $report['text']);
        $this->assertStringContainsString('| G1 | T01, T06, T09, T10, T13, T22 | 27/27 | PASS |', $report['text']);
        $this->assertStringContainsString('| T09-body |', $report['text']);
        $this->assertStringContainsString('| T15b |', $report['text']);
    }
}
