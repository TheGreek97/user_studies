<?php

namespace App\Services\Tutor\Harness;

use App\Services\Llm\LlmClient;
use App\Services\Tutor\Classification;
use App\Services\Tutor\DialogSession;
use App\Services\Tutor\FakeLlmClient;
use App\Services\Tutor\InputRejectedException;
use App\Services\Tutor\Materials;
use App\Services\Tutor\Phase;
use App\Services\Tutor\PromptBuilder;
use App\Services\Tutor\TurnResult;
use App\Services\Tutor\TurnType;
use App\Services\Tutor\Tutor;
use App\Services\Tutor\TutorOutput;
use App\Services\Tutor\Violation;
use App\Services\Tutor\Validator;
use Closure;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Harness (port of tutor-core `harness/run.ts`): runs the 22 cases of `test-dialogs.md` against a provider and
 * builds a markdown report (pass/fail per case and per rule) for Cap. 5.
 *
 * Model cases are evaluated on the FIRST reply of the model (no retry), because they measure the model;
 * the retry/fallback path of the platform is exercised by T22 with simulated failures (always fake).
 *
 * A case run is an array: key, id, run, checks (list of [id, rules, desc, pass, detail?]), manual,
 * message?, output? (TutorOutput), violations?, error?, notes?.
 */
class HarnessRunner
{
    /** Sample Module 3 guidelines (the real ones are saved at generation time, generation-prompts-diff.md). */
    public const SAMPLE_GUIDELINES = [
        ['id' => 'g1', 'text' => "Read the sender's address after the @ and compare it with the real organisation's domain.", 'email_part' => 'sender'],
        ['id' => 'g2', 'text' => 'Hover over every link and button before clicking and read the real address.', 'email_part' => 'link'],
        ['id' => 'g3', 'text' => 'Be suspicious of deadlines, threats and rewards that push you to act now.', 'email_part' => 'tone'],
        ['id' => 'g4', 'text' => 'Never type passwords or card details after following a link in an email.', 'email_part' => 'request'],
        ['id' => 'g5', 'text' => 'A genuine email uses the official domain, links to the official site and asks for no data.', 'email_part' => 'none'],
    ];

    public const SAMPLE_STYLE = ['Plain, friendly wording.', 'Short sentences.', 'No jargon unless the user uses it.'];

    public const RULE_ORDER = ['K1', 'G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8', 'G9', 'G10', 'G11', '4.4.4', 'Piattaforma'];

    private const P = ['Piattaforma'];

    private array $bank;

    private array $schema;

    private string $template;

    private string $testMd;

    private Closure $clock;

    /** Model reported by the real client (report header). */
    private ?string $model = null;

    /**
     * @param string $provider 'fake' (scripted ideal outputs) or 'llm' (the given LlmClient: real calls, real cost)
     */
    public function __construct(
        private readonly Materials $materials,
        public readonly string $provider = 'fake',
        private readonly ?LlmClient $llm = null,
        private readonly int $timeoutMs = 30_000,
        public readonly int $runs = 1,
    ) {
        if (! in_array($provider, ['fake', 'llm'], true)) {
            throw new InvalidArgumentException("Unknown provider: {$provider}");
        }
        if ($provider === 'llm' && $llm === null) {
            throw new InvalidArgumentException('Provider llm needs an LlmClient');
        }
        $this->bank = $materials->loadEmailBank();
        $this->schema = $materials->loadOutputSchema();
        $this->template = PromptBuilder::extractSystemTemplate($materials->loadPromptMarkdown());
        $this->testMd = $materials->loadTestDialogsMarkdown();
        $this->clock = fn () => new DateTimeImmutable('2026-11-12T10:00:00Z');
    }

    private function makeClient(array $script): LlmClient
    {
        return $this->provider === 'fake' ? new FakeLlmClient($script) : $this->llm;
    }

    private function engineOf(LlmClient $client, ?int $t = null): Tutor
    {
        return new Tutor($client, $this->schema, $this->template, $t ?? $this->timeoutMs);
    }

    private static function ok(string $id, array $rules, string $desc, bool $pass, ?string $detail = null): array
    {
        $c = ['id' => $id, 'rules' => $rules, 'desc' => $desc, 'pass' => $pass];
        if ($detail !== null && $detail !== '' && ! $pass) {
            $c['detail'] = $detail;
        }

        return $c;
    }

    private function newSession(array $card, int $position): DialogSession
    {
        return new DialogSession($card, $position, module3Guidelines: self::SAMPLE_GUIDELINES, styleCard: self::SAMPLE_STYLE, clock: $this->clock);
    }

    private function replay(array $c, array $card): DialogSession
    {
        $s = $this->newSession($card, $c['position']);
        foreach ($c['history'] as $h) {
            if ($h['author'] === 'user') {
                $s->submitUserReply($h['text'], $h['classification'] ?? $s->classification);
            } else {
                $s->appendTutorMessage($h['text']);
            }
        }
        $s->markRecognized($c['recognizedBefore'] ?? []);

        return $s;
    }

    /** @return list<array{id: string, rules: list<string>, desc: string, match: Closure(Violation, bool): bool}> */
    private static function kRules(): array
    {
        return [
            ['id' => 'K2', 'rules' => ['G1'], 'desc' => 'no verdict wording (dialog)', 'match' => fn (Violation $v, bool $cl) => ! $cl && $v->rule === 'G1'],
            ['id' => 'K2c', 'rules' => ['G2'], 'desc' => 'closing does not contradict the card verdict', 'match' => fn (Violation $v, bool $cl) => $cl && $v->rule === 'G2'],
            ['id' => 'K3', 'rules' => ['G5'], 'desc' => 'sentence/question limits', 'match' => fn (Violation $v) => $v->rule === 'G5'],
            ['id' => 'K4', 'rules' => ['G3'], 'desc' => 'ids exist in the card and partition it', 'match' => fn (Violation $v) => $v->rule === 'G3'],
            ['id' => 'V-G6', 'rules' => ['G6'], 'desc' => 'no zone hint above the help level', 'match' => fn (Violation $v) => $v->rule === 'G6'],
            ['id' => 'V-G8', 'rules' => ['G8'], 'desc' => 'no disclosure of unnamed items', 'match' => fn (Violation $v) => $v->rule === 'G8'],
            ['id' => 'V-G9', 'rules' => ['G9'], 'desc' => 'no card internals in the message', 'match' => fn (Violation $v) => $v->rule === 'G9'],
        ];
    }

    public static function caseKey(array $c): string
    {
        $v = $c['variant'] ?? null;

        return $v !== null ? $c['id'].(strlen($v) === 1 ? $v : "-{$v}") : $c['id'];
    }

    private function runModelCase(array $c, int $run): array
    {
        $base = Materials::findCard($this->bank, $c['emailId']);
        $card = $c['cardOverride'] !== null ? ($c['cardOverride'])($base) : $base;
        $res = ['key' => self::caseKey($c), 'id' => $c['id'], 'run' => $run, 'checks' => [], 'manual' => $c['manual']];
        if ($c['notes'] !== null) {
            $res['notes'] = $c['notes'];
        }
        $turnType = TurnType::from($c['turnType']);
        $closing = $turnType === TurnType::Closing;
        $session = $this->replay($c, $card);
        $client = $this->makeClient(($c['mock'])($card));
        $engine = $this->engineOf($client);
        $prompt = PromptBuilder::buildPrompt($this->template, $session->promptContext($turnType));

        try {
            $raw = $engine->callWithTimeout($prompt->system, $prompt->user, $this->timeoutMs);
            $this->model = $engine->lastModel ?? $this->model;
        } catch (Exception $e) {
            $res['error'] = $e->getMessage();
            $res['checks'][] = self::ok('K1', ['K1'], 'valid JSON reply', false, "provider: {$res['error']}");

            return $res;
        }
        $v = Validator::validateTutorReply($raw, $this->schema, $session->validationContext($turnType));
        $res['violations'] = $v->violations;
        $res['checks'][] = self::ok('K1', ['K1'], 'valid JSON per schema', $v->output !== null, implode('; ', array_map(fn (Violation $x) => $x->detail, $v->violations)));
        if ($v->output === null) {
            return $res;
        }
        $res['output'] = $v->output;
        $res['message'] = $v->output->message;
        foreach (self::kRules() as $k) {
            if (($k['id'] === 'K2' && $closing) || ($k['id'] === 'K2c' && ! $closing)) {
                continue;
            }
            $hits = array_values(array_filter($v->violations, fn (Violation $x) => ($k['match'])($x, $closing)));
            $res['checks'][] = self::ok($k['id'], $k['rules'], $k['desc'], $hits === [], implode('; ', array_map(fn (Violation $h) => $h->detail, $hits)));
        }
        foreach ($c['checks'] as $chk) {
            $r = ($chk['fn'])($v->output, $card);
            $res['checks'][] = self::ok($chk['id'], $chk['rules'], $chk['desc'], $r === true, $r === true ? null : $r);
        }
        if ($c['platform'] !== null) {
            array_push($res['checks'], ...$this->platformContinuation($c, $session, $v->output, $client, $v->ok));
        }

        return $res;
    }

    /** Platform behaviour after the model reply (A criteria "la piattaforma ..."). */
    private function platformContinuation(array $c, DialogSession $s, TutorOutput $out, LlmClient $client, bool $valid): array
    {
        $P = self::P;
        if (! $valid) {
            return [self::ok('platform', $P, 'platform continuation', false, 'skipped: model output not valid')];
        }
        $applied = $s->applyDialogResult(new TurnResult($out, 'model', []));
        $phase = $applied['phase'];
        switch ($c['platform']) {
            case 'T05':
                return [self::ok('no_close_before_2', $P, 'platform asks another turn despite propose_close (1 reply)', $phase === Phase::AwaitingReply, "phase {$phase->value}")];
            case 'T15a':
                return [self::ok('no_close_after_1_minimal', $P, 'one minimal reply does not close', $phase === Phase::AwaitingReply, "phase {$phase->value}")];
            case 'T15b':
                $checks = [self::ok('closes_after_2_minimal', $P, 'platform closes after 2 consecutive minimal replies (3 >= 2)', $phase === Phase::Closing && $s->closeReason?->value === 'minimal_replies', "phase {$phase->value}")];
                if ($phase !== Phase::Closing) {
                    return $checks;
                }
                $view = $s->runClosingTurn($this->engineOf($client));
                $checks[] = self::ok('closing_turn_called', $P, 'closing turn called, verdict from the card', $s->phase === Phase::Closed && $view->verdict === Classification::Phishing, "phase {$s->phase->value}");
                $checks[] = self::ok('closing_turn_valid', ['G2', 'G5'], 'closing reply valid (no fallback)', $view->source === 'model', 'fallback used');

                return $checks;
            case 'T13':
                // Provider-independent: two more replies with fake outputs; the 4th reply must close with no dialog call.
                $card = $s->card;
                $fake = new FakeLlmClient([
                    HarnessCases::ideal($card, 'What else would you check?'),
                    HarnessCases::ideal($card, 'Next time, check the sender domain and the links.', [], ['propose_close' => true]),
                ]);
                $e = $this->engineOf($fake);
                $s->step($e, 'I think it is fine overall', $s->classification);
                $last = $s->step($e, 'Still a receipt to me', $s->classification);
                $reason = $s->closeReason?->value ?? 'null';

                return [
                    self::ok('closes_at_4', $P, 'dialog closes at the 4th reply (no skip button needed)', $last->phase === Phase::Closed && $reason === 'max_replies', "phase {$last->phase->value}, reason {$reason}"),
                    self::ok('no_dialog_call_at_4', $P, 'no dialog call for the 4th reply, only closing', count($fake->calls) === 2, count($fake->calls).' calls'),
                ];
            default:
                return [];
        }
    }

    private function runT02(int $run): array
    {
        $card = Materials::findCard($this->bank, 'm4-p1-icloud');
        $s = $this->newSession($card, 1);
        $fake = new FakeLlmClient([]);
        $gate = DialogSession::checkUserInput('looks fake', Classification::Phishing, true);
        $rejected = false;
        try {
            $s->step($this->engineOf($fake), 'looks fake', Classification::Phishing);
        } catch (InputRejectedException) {
            $rejected = true;
        }
        $P = self::P;

        return [
            'key' => 'T02', 'id' => 'T02', 'run' => $run, 'manual' => [],
            'checks' => [
                self::ok('gate_too_short', $P, '"looks fake" (< 4 words) blocked', ! $gate->ok && $gate->reason === 'too_short'),
                self::ok('rejected', $P, 'submission rejected', $rejected),
                self::ok('no_model_call', $P, 'no model call', count($fake->calls) === 0, count($fake->calls).' calls'),
                self::ok('no_turn_saved', $P, 'no turn saved', $s->history === [] && $s->events === []),
            ],
        ];
    }

    private function runT03(int $run): array
    {
        $card = Materials::findCard($this->bank, 'm4-p1-icloud');
        $P = self::P;
        $a = DialogSession::checkUserInput('   ', Classification::Phishing, true);
        $b = DialogSession::checkUserInput('The sender address is strange', null, true);
        $s = $this->newSession($card, 1);
        $fake = new FakeLlmClient([HarnessCases::ideal($card, "Look at the sender's address, after the @. What do you notice?", [], ['focus_clue_id' => 'c1'])]);
        $s->step($this->engineOf($fake), 'The sender address is strange', Classification::Phishing);
        $before = ['calls' => count($fake->calls), 'history' => count($s->history)];
        $rejected = false;
        try {
            $s->step($this->engineOf($fake), '   ', Classification::Phishing);
        } catch (InputRejectedException) {
            $rejected = true;
        }

        return [
            'key' => 'T03', 'id' => 'T03', 'run' => $run, 'manual' => [],
            'checks' => [
                self::ok('a_empty_explanation', $P, '(a) empty explanation blocked', ! $a->ok && $a->reason === 'empty'),
                self::ok('b_no_classification', $P, '(b) missing classification blocked', ! $b->ok && $b->reason === 'missing_classification'),
                self::ok('c_empty_reply', $P, '(c) empty dialog reply blocked', $rejected),
                self::ok('c_no_call_no_turn', $P, '(c) no model call, no turn saved', count($fake->calls) === $before['calls'] && count($s->history) === $before['history']),
            ],
        ];
    }

    private function runT22(int $run): array
    {
        $card = Materials::findCard($this->bank, 'm4-p3-coworker');
        $P = self::P;
        $valid = HarnessCases::ideal($card, 'What are you basing that on?');
        $verdict = HarnessCases::ideal($card, 'Yes, this is phishing: look at the sender.');
        $badId = HarnessCases::ideal($card, 'What do you notice?', ['c42']);
        $scenarios = [
            ['a', ['not json', 'not json either'], 'invalid_output', ['K1']],
            ['b', [$verdict, $verdict], 'invalid_output', ['G1']],
            ['c', [$badId, $badId], 'invalid_output', ['G3']],
            ['d', [['hang' => true], ['hang' => true]], 'timeout', []],
        ];
        $checks = [];
        foreach ($scenarios as [$name, $script, $failure, $rules]) {
            $s = $this->newSession($card, 3);
            $fake = new FakeLlmClient($script);
            $out = $s->step($this->engineOf($fake, 50), 'Laura always sends invoices like this.', Classification::Legitimate);
            $fb = $s->eventsOfType('fallback')[0] ?? null;
            $d = $fb?->detail;
            $checks[] = self::ok("{$name}_one_retry", [...$P, ...$rules], "({$name}) exactly one retry", count($fake->calls) === 2, count($fake->calls).' calls');
            $checks[] = self::ok("{$name}_fallback_hint", [...$P, ...$rules], "({$name}) first card hint shown", ($out->shown[0] ?? null) === $card['fallback_hints']['low'][0], $out->shown[0] ?? null);
            $allFailure = $d !== null && array_filter($d['failures'], fn ($f) => $f !== $failure) === [];
            $checks[] = self::ok("{$name}_logged", $P, "({$name}) fallback logged with type and attempts", ($d['attempts'] ?? null) === 2 && $allFailure, $d !== null ? json_encode($d) : null);
            if ($name === 'b') {
                $checks[] = self::ok('b_never_shown', ['G1'], '(b) verdict message never reaches the user', ! str_contains(implode(' ', $out->shown), 'this is phishing'));
            }
        }
        // Retry that succeeds.
        $s = $this->newSession($card, 3);
        $fake = new FakeLlmClient(['not json', $valid]);
        $out = $s->step($this->engineOf($fake, 50), 'Laura always sends invoices like this.', Classification::Legitimate);
        $checks[] = self::ok('retry_success', $P, 'valid retry is used, no fallback', ($out->shown[0] ?? null) === 'What are you basing that on?' && $s->eventsOfType('fallback') === []);
        // Closing fallback.
        $s = $this->newSession($card, 3);
        $fake = new FakeLlmClient([$valid, $valid, $valid, 'x', 'x']);
        $e = $this->engineOf($fake, 50);
        foreach (['Laura always sends invoices like this.', 'It has an invoice number', 'The signature is right', 'Nothing else to add'] as $t) {
            $s->step($e, $t, Classification::Legitimate);
        }
        $checks[] = self::ok('closing_static_feedback', $P, 'closing fallback shows static_feedback, verdict from the card', $s->closingView?->message === $card['static_feedback'] && $s->closingView->verdict === Classification::Phishing);

        return ['key' => 'T22', 'id' => 'T22', 'run' => $run, 'manual' => [], 'checks' => $checks];
    }

    /** Runs every case `runs` times. @return list<array<string, mixed>> */
    public function run(): array
    {
        $all = [];
        for ($run = 1; $run <= $this->runs; $run++) {
            foreach (HarnessCases::all() as $c) {
                $all[] = match (true) {
                    $c['kind'] === 'model' => $this->runModelCase($c, $run),
                    $c['id'] === 'T02' => $this->runT02($run),
                    $c['id'] === 'T03' => $this->runT03($run),
                    $c['id'] === 'T22' => $this->runT22($run),
                };
            }
        }

        return $all;
    }

    public static function passed(array $r): bool
    {
        foreach ($r['checks'] as $c) {
            if (! $c['pass']) {
                return false;
            }
        }

        return true;
    }

    // ---- test-dialogs.md parsing (titles, rules, rule matrix, drift) -------------------------------------------

    /** @return array{cases: array<string, array{title: string, rules: string, block: string}>, matrix: array<string, list<string>>} */
    public function parseMd(): array
    {
        $md = $this->testMd;
        $cases = [];
        foreach (array_slice(preg_split('/^## /mu', $md), 1) as $b) {
            if (preg_match('/^(T\d{2})\s+[–-]\s+(.+)$/mu', $b, $h) !== 1) {
                continue;
            }
            $rules = preg_match('/\*\*Regole\*\*:\s*(.+)$/mu', $b, $rm) === 1 ? trim($rm[1]) : '';
            $cases[$h[1]] = ['title' => trim($h[2]), 'rules' => $rules, 'block' => $b];
        }
        $matrix = [];
        $mBlock = preg_split('/^## Matrice/mu', $md)[1] ?? '';
        foreach (explode("\n", $mBlock) as $line) {
            if (preg_match('/^\|\s*(G\d+|Piattaforma)[^|]*\|\s*(.+?)\s*\|$/u', $line, $m) === 1) {
                preg_match_all('/T\d{2}/', $m[2], $ids);
                $matrix[$m[1]] = array_values(array_unique($ids[0]));
            }
        }

        return ['cases' => $cases, 'matrix' => $matrix];
    }

    /** @return list<string> */
    public function driftWarnings(array $mdCases): array
    {
        $w = [];
        $all = HarnessCases::all();
        $specIds = array_values(array_unique(array_map(fn ($c) => $c['id'], $all)));
        foreach (array_keys($mdCases) as $id) {
            if (! in_array((string) $id, $specIds, true)) {
                $w[] = "{$id} is in test-dialogs.md but has no executable spec";
            }
        }
        foreach ($specIds as $id) {
            if (! isset($mdCases[$id])) {
                $w[] = "{$id} has a spec but is not in test-dialogs.md";
            }
        }
        foreach ($all as $c) {
            if ($c['kind'] !== 'model') {
                continue;
            }
            $block = $mdCases[$c['id']]['block'] ?? '';
            $emailLine = preg_match('/\*\*Email \/ livello\*\*:\s*(.+)$/mu', $block, $m) === 1 ? $m[1] : '';
            if (str_contains($emailLine, 'm4-') && ! str_contains($emailLine, $c['emailId'])) {
                $w[] = "{$c['id']}: email {$c['emailId']} not in \"{$emailLine}\"";
            }
            if (preg_match('/high|medium|low/', $emailLine) === 1 && ! str_contains($emailLine, $c['helpLevel'])) {
                $w[] = "{$c['id']}: level {$c['helpLevel']} not in \"{$emailLine}\"";
            }
        }

        return $w;
    }

    // ---- report -------------------------------------------------------------------------------------------------

    private static function esc(string $s): string
    {
        return str_replace(['|', "\n"], ['\\|', ' '], $s);
    }

    private static function b(bool $v): string
    {
        return $v ? 'true' : 'false';
    }

    /** @return array{text: string, summary: string, failed: bool} */
    public function report(array $all): array
    {
        $md = $this->parseMd();
        $warnings = $this->driftWarnings($md['cases']);
        $keys = array_values(array_unique(array_map(fn ($r) => $r['key'], $all)));
        $byKey = fn (string $k) => array_values(array_filter($all, fn ($r) => $r['key'] === $k));
        $caseIds = array_values(array_unique(array_map(fn ($r) => $r['id'], $all)));
        $casePass = function (string $id) use ($all) {
            foreach ($all as $r) {
                if ($r['id'] === $id && ! self::passed($r)) {
                    return false;
                }
            }

            return true;
        };
        $nPass = count(array_filter($caseIds, $casePass));
        $manualCount = array_sum(array_map(fn ($k) => count($byKey($k)[0]['manual'] ?? []), $keys));
        $checksFor = fn (string $rule) => array_merge([], ...array_map(fn ($r) => array_values(array_filter($r['checks'], fn ($c) => in_array($rule, $c['rules'], true))), $all));

        $L = [];
        array_push($L, '# Report harness – tutor M4', '');
        $L[] = '- Data: '.(new DateTimeImmutable('now'))->format('Y-m-d\TH:i:s.v\Z');
        $L[] = "- Provider: `{$this->provider}`".($this->provider === 'llm' ? ' (modello `'.($this->model ?? '?').'`)' : '')."; esecuzioni per caso: {$this->runs}";
        $L[] = '- Materiali: emails-m4.json v'.($this->bank['_meta']['version'] ?? '?').'; casi in test-dialogs.md: '.count($md['cases']).'; varianti eseguite: '.count($keys);
        if ($this->provider === 'fake') {
            array_push($L, '', '> [!note] Provider fake', '> Le risposte sono uscite ideali scritte a mano (`app/Services/Tutor/Harness/HarnessCases.php`): il report verifica la catena prompt → validator → state machine → report, non la qualità di un modello. I criteri M restano da giudicare a mano.');
        }
        array_push($L, '', '## Riepilogo', '');
        array_push($L, '| Casi | Superati (criteri A) | Falliti | Varianti | Criteri M da rivedere |', '|---|---|---|---|---|');
        $L[] = '| '.count($caseIds)." | {$nPass} | ".(count($caseIds) - $nPass).' | '.count($keys)." | {$manualCount} |";
        if ($warnings !== []) {
            array_push($L, '', '**Disallineamenti con test-dialogs.md**:', ...array_map(fn ($w) => "- {$w}", $warnings));
        }

        array_push($L, '', '## Per regola', '', 'Controlli automatici (A) che verificano la regola, su tutte le esecuzioni; i casi della matrice sono quelli di test-dialogs.md.', '');
        array_push($L, '| Regola | Casi (matrice) | Controlli A superati | Esito |', '|---|---|---|---|');
        foreach (self::RULE_ORDER as $rule) {
            $checks = $checksFor($rule);
            $p = count(array_filter($checks, fn ($c) => $c['pass']));
            $esito = $checks === [] ? 'solo M' : ($p === count($checks) ? 'PASS' : 'FAIL');
            $mx = implode(', ', $md['matrix'][$rule] ?? []) ?: '–';
            $L[] = "| {$rule} | {$mx} | {$p}/".count($checks)." | {$esito} |";
        }

        array_push($L, '', '## Per caso', '');
        array_push($L, '| Caso | Titolo | Regole (test-dialogs.md) | Esecuzioni superate | Controlli falliti | M da rivedere |', '|---|---|---|---|---|---|');
        foreach ($keys as $k) {
            $rs = $byKey($k);
            $r0 = $rs[0];
            $info = $md['cases'][$r0['id']] ?? null;
            $fails = array_values(array_unique(array_merge([], ...array_map(fn ($r) => array_map(fn ($c) => $c['id'], array_values(array_filter($r['checks'], fn ($c) => ! $c['pass']))), $rs))));
            $nOk = count(array_filter($rs, [self::class, 'passed']));
            $L[] = "| {$k} | ".self::esc($info['title'] ?? '').' | '.self::esc($info['rules'] ?? '')." | {$nOk}/".count($rs).' | '.(implode(', ', $fails) ?: '–').' | '.count($r0['manual']).' |';
        }

        array_push($L, '', '## Dettaglio', '');
        foreach ($keys as $k) {
            foreach ($byKey($k) as $r) {
                $L[] = "### {$k}".($this->runs > 1 ? " (esecuzione {$r['run']})" : '').' – '.($md['cases'][$r['id']]['title'] ?? '');
                $L[] = '';
                if (isset($r['notes'])) {
                    $L[] = "- Nota: {$r['notes']}";
                }
                if (isset($r['error'])) {
                    $L[] = "- Errore provider: {$r['error']}";
                }
                if (isset($r['message'])) {
                    $L[] = "- Messaggio: \"{$r['message']}\"";
                }
                if (isset($r['output'])) {
                    $o = $r['output'];
                    $L[] = '- Campi: recognized ['.implode(', ', $o->recognized_clue_ids).'], focus '.($o->focus_clue_id ?? 'null')
                        .', asked_for_answer '.self::b($o->asked_for_answer).', propose_close '.self::b($o->propose_close)
                        .', dont_know '.self::b($o->user_said_dont_know).', minimal '.self::b($o->minimal_reply)
                        .', off_topic '.self::b($o->off_topic).', unsupported ['.implode('; ', $o->unsupported_claims).']';
                }
                foreach ($r['checks'] as $c) {
                    $L[] = '- '.($c['pass'] ? 'PASS' : 'FAIL')." `{$c['id']}` (".implode(', ', $c['rules']).") {$c['desc']}".(isset($c['detail']) ? " — {$c['detail']}" : '');
                }
                foreach ($r['manual'] as $m) {
                    $L[] = "- M da rivedere: {$m}";
                }
                $L[] = '';
            }
        }

        $summary = ["Provider {$this->provider}, runs {$this->runs}: {$nPass}/".count($caseIds).' cases pass all automatic criteria ('.count($keys)." variants, {$manualCount} manual criteria pending)."];
        foreach (self::RULE_ORDER as $rule) {
            $checks = $checksFor($rule);
            $summary[] = '  '.str_pad($rule, 12).' '.count(array_filter($checks, fn ($c) => $c['pass'])).'/'.count($checks);
        }
        foreach ($caseIds as $id) {
            if ($casePass($id)) {
                continue;
            }
            $f = [];
            foreach ($all as $r) {
                if ($r['id'] === $id) {
                    foreach ($r['checks'] as $c) {
                        if (! $c['pass']) {
                            $f[] = $c['id'].(isset($c['detail']) ? " ({$c['detail']})" : '');
                        }
                    }
                }
            }
            $summary[] = "  FAILED {$id}: ".implode('; ', array_values(array_unique($f)));
        }
        foreach ($warnings as $w) {
            $summary[] = "  WARN {$w}";
        }

        return ['text' => implode("\n", $L), 'summary' => implode("\n", $summary), 'failed' => $nPass < count($caseIds)];
    }

    /**
     * Per-rule totals `[rule => [passed, total]]`, as in the "Per regola" table.
     *
     * @return array<string, array{0: int, 1: int}>
     */
    public static function ruleTotals(array $all): array
    {
        $t = [];
        foreach (self::RULE_ORDER as $rule) {
            $p = 0;
            $n = 0;
            foreach ($all as $r) {
                foreach ($r['checks'] as $c) {
                    if (in_array($rule, $c['rules'], true)) {
                        $n++;
                        $p += $c['pass'] ? 1 : 0;
                    }
                }
            }
            $t[$rule] = [$p, $n];
        }

        return $t;
    }
}
