<?php

namespace App\Http\Livewire;

use App\Models\Training;
use App\Models\TrainingResponse;
use App\Services\Training\FirstName;
use App\Services\Training\InteractiveBlocks;
use Livewire\Component;

/**
 * Module 1, interactive condition (generation-prompts-diff.md section 1): the header of the scenario email
 * (`scenario-header`) followed by the fixed platform question and a free-text answer, saved in training_responses
 * (module 1, item "suspicion"). The training page enables "Next" of Module 1 once an answer is saved: browser
 * event `m1-completed`, or data-m1-completed="true" on the root after a reload.
 *
 * Everything is resolved from the authenticated user (no ids from the browser). Without a valid header the
 * component renders an empty root without data-m1-completed, so Module 1 is not gated.
 */
class ScenarioQuestion extends Component
{
    public const MODULE = 1;
    public const ITEM = 'suspicion';
    public const QUESTION = 'What would make you suspicious about this message?';
    public const MAX_CHARS = 1000;

    /** Answer being written (the only public state: what the user typed). */
    public string $answer = '';

    public bool $saved = false;

    private function training(): ?Training
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);
        $training = Training::query()->where('user_id', $user->id)->first();

        return $training !== null && $training->generated && $training->isInteractive() ? $training : null;
    }

    public function mount(): void
    {
        $training = $this->training();
        if ($training === null) {
            return;
        }
        $previous = TrainingResponse::query()
            ->where('user_id', $training->user_id)
            ->where('module', self::MODULE)
            ->where('item_key', self::ITEM)
            ->first();
        $this->answer = (string) ($previous?->response_text ?? '');
        $this->saved = trim($this->answer) !== '';
        if ($this->saved) {
            $this->dispatchBrowserEvent('m1-completed');
        }
    }

    public function save(): void
    {
        $training = $this->training();
        abort_unless($training !== null && $training->interactiveBlock(InteractiveBlocks::SCENARIO_HEADER) !== null, 403);

        $this->answer = trim($this->answer);
        $this->validate(
            ['answer' => 'required|string|max:' . self::MAX_CHARS],
            [
                'answer.required' => 'Please write your answer before continuing.',
                'answer.max' => 'Please keep your answer under ' . self::MAX_CHARS . ' characters.',
            ],
        );

        TrainingResponse::record($training->user_id, $training->id, self::MODULE, self::ITEM, $this->answer, [
            'question' => self::QUESTION,
            'chars' => mb_strlen($this->answer),
        ]);
        $this->saved = true;
        $this->dispatchBrowserEvent('m1-completed');
    }

    public function render()
    {
        $training = $this->training();
        $header = $training?->interactiveBlock(InteractiveBlocks::SCENARIO_HEADER);
        if ($header !== null) {
            // The model may use the placeholder in the first line ("Dear {{first_name}}"); printed escaped.
            $header = array_map(fn ($v) => is_string($v) ? FirstName::fillText($v, (string) $training->user->name) : $v, $header);
        }

        return view('livewire.scenario-question', [
            'header' => $header,
            'question' => self::QUESTION,
            'maxChars' => self::MAX_CHARS,
        ]);
    }
}
