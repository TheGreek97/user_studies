<?php

namespace App\Http\Livewire;

use App\Http\Controllers\TrainingController;
use App\Models\Training;
use App\Models\TrainingResponse;
use App\Services\Training\InteractiveBlocks;
use App\Services\Training\ScenarioEmail;
use App\Services\Training\ScenarioSection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Module 2, interactive condition (generation-prompts-diff.md section 2): the generated scenario text, the scenario
 * email rendered by the platform with its suspicious elements activated one at a time in `order`, and the
 * generated decision point, shown only once every element has been opened.
 *
 * State is on the server (training_responses, module 2): item "clues" holds the opened ids (in order) and the ids
 * whose target.text was not found (logged once); item "decision" the chosen radio of the decision point. The
 * browser never sends a clue id or a label: openNext() opens the next clue in the fixed order, saveDecision()
 * receives the index of a radio and the server reads value and label from the generated HTML.
 *
 * The generated HTML after the decision point (the reflection/feedback, which reveals the right answer) is not sent to
 * the browser until the decision is saved (or, without radio buttons, until every clue is opened). When it appears
 * after a Livewire request its scripts are run by the browser (data-m2-after x-init), as they would be on page load.
 *
 * "Next" of Module 2 is enabled (event `m2-completed`, data-m2-completed="true" after a reload) when the decision is
 * saved, or when all clues are opened if the decision point has no radio buttons. Without a valid scenario email the
 * component prints the section HTML as the paper's conditions do, with no gate.
 */
class ScenarioClues extends Component
{
    public const MODULE = 2;
    public const ITEM_CLUES = 'clues';
    public const ITEM_DECISION = 'decision';

    /** Whether the part after the decision point was already in the page when it was loaded (its scripts ran). */
    public bool $afterShownAtMount = false;

    private ?Training $trainingCache = null;

    private function training(): ?Training
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);
        if ($this->trainingCache === null) {
            // Fresh row: the training page styles the sections of the user's cached relation in memory (addCSS).
            $training = Training::query()->where('user_id', $user->id)->first();
            $this->trainingCache = $training !== null && $training->generated && $training->isInteractive() ? $training : null;
        }

        return $this->trainingCache;
    }

    private function email(Training $training): ?ScenarioEmail
    {
        $data = $training->interactiveBlock(InteractiveBlocks::SCENARIO_EMAIL);

        return $data === null ? null : new ScenarioEmail($data, (string) $training->user->name);
    }

    /** Scenario HTML styled and filled as training_show does for the other sections, split at the decision point. */
    private function section(Training $training): array
    {
        $styled = TrainingController::addCSS(clone $training);

        return ScenarioSection::split($styled->renderSection('scenario'));
    }

    private function response(Training $training, string $item): ?TrainingResponse
    {
        return TrainingResponse::query()
            ->where('user_id', $training->user_id)
            ->where('module', self::MODULE)
            ->where('item_key', $item)
            ->first();
    }

    /** Progress row of the clues; created on first view, when the clues not found in the email are logged. */
    private function progress(Training $training, ScenarioEmail $email): array
    {
        $row = $this->response($training, self::ITEM_CLUES);
        if ($row !== null) {
            return $row->response_json ?? [];
        }
        $unlocated = $email->unlocatedIds();
        if ($unlocated !== []) {
            Log::warning('Module 2 clue target not found in the scenario email: explanation shown without highlight', [
                'training_id' => $training->id, 'user_id' => $training->user_id, 'clue_ids' => $unlocated,
            ]);
        }
        $progress = ['opened' => [], 'opened_at' => [], 'unlocated' => $unlocated];
        TrainingResponse::record($training->user_id, $training->id, self::MODULE, self::ITEM_CLUES, null, $progress);

        return $progress;
    }

    private static function allOpened(ScenarioEmail $email, array $progress): bool
    {
        return count(array_diff(array_column($email->clues(), 'id'), $progress['opened'] ?? [])) === 0;
    }

    private function completed(Training $training, ScenarioEmail $email, array $progress, array $section): bool
    {
        if (! self::allOpened($email, $progress)) {
            return false;
        }

        return $section['options'] === [] || $this->response($training, self::ITEM_DECISION) !== null;
    }

    /**
     * Module 2 completed by the training's user, from the stored state only (used to resume after a reload): the
     * decision saved, or every clue opened when the decision point has no radio buttons. False without a valid email.
     */
    public static function completedFor(Training $training): bool
    {
        $data = $training->interactiveBlock(InteractiveBlocks::SCENARIO_EMAIL);
        if ($data === null) {
            return false;
        }
        $training = $training->fresh() ?? $training;  // the page may pass the training with its sections already styled
        $component = new self();
        $component->trainingCache = $training;
        $progress = $component->response($training, self::ITEM_CLUES)?->response_json;

        return $progress !== null && $component->completed($training, new ScenarioEmail($data, (string) $training->user->name), $progress, $component->section($training));
    }

    public function mount(): void
    {
        $training = $this->training();
        $email = $training === null ? null : $this->email($training);
        if ($email === null) {
            return;
        }
        $completed = $this->completed($training, $email, $this->progress($training, $email), $this->section($training));
        $this->afterShownAtMount = $completed;
        if ($completed) {
            $this->dispatchBrowserEvent('m2-completed');
        }
    }

    /** Opens the next clue in the fixed order (the browser cannot choose which one). */
    public function openNext(): void
    {
        $training = $this->training();
        $email = $training === null ? null : $this->email($training);
        abort_unless($email !== null, 403);

        $progress = $this->progress($training, $email);
        $opened = $progress['opened'] ?? [];
        foreach ($email->clues() as $clue) {
            if (! in_array($clue['id'], $opened, true)) {
                $progress['opened'][] = $clue['id'];
                $progress['opened_at'][$clue['id']] = now()->toIso8601String();
                TrainingResponse::record($training->user_id, $training->id, self::MODULE, self::ITEM_CLUES, null, $progress);
                break;
            }
        }
        if ($this->completed($training, $email, $progress, $this->section($training))) {
            $this->dispatchBrowserEvent('m2-completed');
        }
    }

    /** Saves the radio chosen in the decision point (index among its radios, in document order). */
    public function saveDecision($index): void
    {
        $training = $this->training();
        $email = $training === null ? null : $this->email($training);
        abort_unless($email !== null, 403);
        abort_unless(self::allOpened($email, $this->progress($training, $email)), 403);

        $options = $this->section($training)['options'];
        $index = filter_var($index, FILTER_VALIDATE_INT);
        abort_unless($index !== false && isset($options[$index]), 422);

        $previous = $this->response($training, self::ITEM_DECISION)?->response_json ?? [];
        TrainingResponse::record($training->user_id, $training->id, self::MODULE, self::ITEM_DECISION, $options[$index]['label'], [
            'index' => $index,
            'value' => $options[$index]['value'],
            // First choice kept for the analysis; later changes only update the current answer.
            'first' => $previous['first'] ?? ['index' => $index, 'value' => $options[$index]['value'], 'at' => now()->toIso8601String()],
            'changes' => isset($previous['index']) ? ($previous['changes'] ?? 0) + (int) ($previous['index'] !== $index) : 0,
        ]);
        $this->dispatchBrowserEvent('m2-completed');
    }

    public function render()
    {
        $training = $this->training();
        $email = $training === null ? null : $this->email($training);
        if ($training === null || $email === null) {
            return view('livewire.scenario-clues', [
                'available' => false,
                'html' => $training === null ? '' : TrainingController::addCSS(clone $training)->renderSection('scenario'),
            ]);
        }

        $progress = $this->progress($training, $email);
        $section = $this->section($training);
        $opened = $progress['opened'] ?? [];
        $clues = $email->clues();
        $current = null;
        $states = [];
        foreach ($clues as $clue) {
            if (in_array($clue['id'], $opened, true)) {
                $states[$clue['id']] = ScenarioEmail::STATE_OPENED;
            } elseif ($current === null) {
                $current = $clue;
                $states[$clue['id']] = ScenarioEmail::STATE_CURRENT;
            }
        }
        // Opened clues in the order they were opened (= the fixed order).
        $openedClues = array_values(array_filter($clues, fn ($c) => in_array($c['id'], $opened, true)));
        $decision = $this->response($training, self::ITEM_DECISION);
        $completed = $current === null && ($section['options'] === [] || $decision !== null);

        return view('livewire.scenario-clues', [
            'available' => true,
            'section' => $section,
            'fields' => $email->render($states),
            'current' => $current,
            'openedClues' => $openedClues,
            'total' => count($clues),
            'allOpened' => $current === null,
            'decisionSaved' => $decision !== null,
            'completed' => $completed,
            // The part after the decision point is shown with the module completed (= answer saved).
            'showAfter' => $completed && trim($section['after']) !== '',
            'runAfterScripts' => $completed && ! $this->afterShownAtMount,
            'fieldLabels' => ScenarioEmail::FIELD_LABELS,
        ]);
    }
}
