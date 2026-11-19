<?php

namespace App\Console\Commands;

use App\Console\Demo\DemoTrainingAnswers;
use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Models\User;
use App\Models\UserQuestionnaireScale;
use App\Services\Llm\LlmClient;
use App\Services\Tutor\FakeLlmClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * LOCAL DEMO ONLY: creates (or resets) a participant of the "interactive" condition who has completed every phase
 * before the training, with a ready training built offline from DemoTrainingAnswers through the real generation
 * job (no provider call). Refuses to run unless APP_ENV=local.
 *
 * Login: StudyAuth logs in the participant whose prolific_id matches the PROLIFIC_PID query parameter,
 * so the printed URL is the whole login (email and password are not used by the study flow).
 */
class DemoInteractive extends Command
{
    protected $signature = 'demo:interactive {--email=demo@example.test : identifies the demo participant (reset if it exists)}
                            {--name=Alice : first name shown in the training}
                            {--url=http://localhost:8000 : base URL printed for the login link}
                            {--real : generate with the configured LlmClient (TRAINING_LLM_PROVIDER): real provider, billed}';

    protected $description = 'LOCAL ONLY: create/reset a demo participant of the interactive condition with a ready training';

    /** Local demo password, printed on purpose (the study flow logs in by PROLIFIC_PID, not by password). */
    public const PASSWORD = 'demo-password';

    public function handle(): int
    {
        if (! $this->laravel->environment('local')) {
            $this->error('demo:interactive runs only with APP_ENV=local (current: ' . $this->laravel->environment() . ').');

            return self::FAILURE;
        }

        $email = (string) $this->option('email');
        $prolificId = self::prolificIdFor($email);

        $user = DB::transaction(function () use ($email, $prolificId) {
            $user = User::where('email', $email)->first() ?? new User();
            if ($user->exists) {
                $this->resetProgress($user);
            }
            $now = now();
            $user->forceFill([
                'name' => (string) $this->option('name'),
                'email' => $email,
                'password' => Hash::make(self::PASSWORD),
                'prolific_id' => $prolificId,
                'training_personalization' => 'interactive',
                'training_length' => 'short',
                // Dummy study columns, as StudyAuth sets them for every participant.
                'warning_type' => 'popup_link',
                'show_explanation' => 1,
                'show_details' => 'no',
                'llm' => 'llama3_3',
                'explanation_type' => 'feature_based',
                // Every phase before the training is done (RedirectToTheRightPhase order).
                'expelled' => false,
                'given_consent' => $now->format('H:i:s'),
                'demographics_completed' => $now,
                'bfi_completed' => $now,
                'stp_completed' => $now,
                'teique_completed' => $now,
                'pre_training_completed' => $now,
                'training_completed' => null,
                'post_training_completed' => null,
                'training_reaction_completed' => null,
                'study_completed' => null,
            ])->save();

            // Personality scales read by the generation prompt: neutral mid-scale values.
            $scales = [];
            foreach ((new UserQuestionnaireScale())->getFillable() as $column) {
                if ($column !== 'user_id') {
                    $scales[$column] = 3;
                }
            }
            UserQuestionnaireScale::updateOrCreate(['user_id' => $user->id], $scales);

            return $user;
        });

        // Real generation job: scripted answers by default (same parsing, interactive_data and Module 4 sequence draw),
        // or the configured provider with --real (cost test before the study).
        $real = (bool) $this->option('real');
        $training = Training::create(['user_id' => $user->id, 'generated' => false]);
        $llm = $real ? $this->laravel->make(LlmClient::class) : new FakeLlmClient(DemoTrainingAnswers::answers());
        (new ProcessTraining($training->id, $user->fresh()))->handle($llm);
        $training->refresh();
        if (! $training->generated) {
            $this->error('Generation failed: ' . ($training->generation_error ?? 'unknown error'));

            return self::FAILURE;
        }

        $url = rtrim((string) $this->option('url'), '/') . '/training?PROLIFIC_PID=' . $prolificId;
        $this->info($real
            ? 'Demo participant ready (interactive condition, training generated with the configured provider).'
            : 'Demo participant ready (interactive condition, training generated offline).');
        if ($real) {
            $this->line('  models:       ' . $training->llm_model);
            $this->line('  usage:        ' . json_encode($training->llm_usage));
        }
        $this->line("  user id:      {$user->id}");
        $this->line("  email:        {$email}");
        $this->line('  password:     ' . self::PASSWORD . '  (not used by the study flow)');
        $this->line("  PROLIFIC_PID: {$prolificId}");
        $this->line('  M4 sequence:  ' . implode(', ', $training->interactive_data['m4']['sequence'] ?? []));
        $this->line("  login URL:    {$url}");
        $this->line('Open the URL in a private window (an existing session of another participant is replaced).');
        $this->line('Module 4 tutor answers and the Module 5 comment on the personal rules are offline only if the server runs with APP_ENV=local TUTOR_FAKE=true.');

        return self::SUCCESS;
    }

    /** Stable, unique PROLIFIC_PID for the demo email (column max 30 chars). */
    public static function prolificIdFor(string $email): string
    {
        return 'DEMO' . substr(sha1(strtolower($email)), 0, 12);
    }

    /** Removes the training and the Module 4/5 data of a previous demo run of this participant. */
    private function resetProgress(User $user): void
    {
        foreach (['tutor_turns', 'training_responses', 'rules_comments', 'tutor_sessions', 'trainings'] as $table) {
            DB::table($table)->where('user_id', $user->id)->delete();
        }
    }
}
