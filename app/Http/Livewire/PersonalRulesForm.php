<?php

namespace App\Http\Livewire;

use App\Services\Training\Conclusions;
use App\Services\Tutor\ChatSubmitResult;
use App\Services\Tutor\PersonalRules;
use Livewire\Component;

/**
 * Module 5 personal rules, interactive condition (cap4 4.3.5): 2-3 rules "When ..., I ...", ONE tutor comment
 * (prompt 5b), ONE revision, then the "thanks" part of the generated conclusions.
 *
 * Public properties hold ONLY what the user typed: stage, comment and thanks are read from the DB of the
 * authenticated user at every render (PersonalRules), so the browser cannot choose a user, a stage or a comment.
 * When the revision is saved the component emits `rulesCompleted` and the browser event `rules-completed`, and its
 * root carries `data-rules-completed="true"`, so the training page can enable "Proceed".
 */
class PersonalRulesForm extends Component
{
    /** @var list<string> rule fields as typed (PersonalRules::MAX_RULES) */
    public array $rules = ['', '', ''];

    /** Friendly notice (rate limit, busy, already saved). */
    public ?string $notice = null;

    public bool $completed = false;

    private ?PersonalRules $service = null;

    private function service(): PersonalRules
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        return $this->service ??= new PersonalRules($user);
    }

    public function mount(): void
    {
        $stage = $this->service()->stage();
        $this->completed = $stage === PersonalRules::STAGE_DONE;
        $saved = $this->service()->savedRules($this->completed ? PersonalRules::V2 : PersonalRules::V1);
        if ($saved !== []) {
            $this->prefill($saved); // resume: the revision starts from the first version (or shows the final one)
        }
        if ($this->completed) {
            $this->dispatchBrowserEvent('rules-completed');
        }
    }

    private function prefill(array $rules): void
    {
        $this->rules = array_pad(array_slice(array_values($rules), 0, PersonalRules::MAX_RULES), PersonalRules::MAX_RULES, '');
    }

    public function submit(): void
    {
        $this->notice = null;
        $this->resetErrorBag();
        $max = PersonalRules::maxChars();
        $this->validate(
            ['rules' => 'array|max:'.PersonalRules::MAX_RULES, 'rules.*' => "nullable|string|max:{$max}"],
            ['rules.*.max' => "Please keep your rules under {$max} characters in total."],
        );

        $service = $this->service();
        $result = $service->stage() === PersonalRules::STAGE_REVISE
            ? $service->submitRevision($this->rules)
            : $service->submitRules($this->rules);
        if ($result->status === ChatSubmitResult::REJECTED) {
            $this->addError('rules', $result->notice);

            return;
        }
        if (! $result->ok()) {
            $this->notice = $result->notice;

            return;
        }
        $this->prefill(PersonalRules::normalise($this->rules));
        if (! $this->completed && $service->stage() === PersonalRules::STAGE_DONE) {
            $this->completed = true;
            $this->emit('rulesCompleted');
            $this->dispatchBrowserEvent('rules-completed');
        }
    }

    public function render()
    {
        $service = $this->service();
        $stage = $service->stage();
        $comment = $stage === PersonalRules::STAGE_WRITE ? null : $service->comment();
        $thanks = null;
        if ($stage === PersonalRules::STAGE_DONE) {
            // Generated training HTML, same trust level as the rest of the conclusions printed by training_show.
            $thanks = Conclusions::split($service->training()->renderSection('conclusions'))['thanks'];
        }

        return view('livewire.personal-rules-form', [
            'stage' => $stage,
            'commentMessage' => $comment?->isShown() ? $comment->message : null,
            'commentSkipped' => $comment !== null && ! $comment->isShown(),
            'thanks' => $thanks,
            'maxChars' => PersonalRules::maxChars(),
            'minRules' => PersonalRules::MIN_RULES,
        ]);
    }
}
