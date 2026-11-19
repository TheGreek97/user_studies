{{-- Module 5, interactive condition: recap of the generated conclusions, then the personal rules with the tutor
     comment (Livewire). The `<div data-role="thanks">` of the conclusions is NOT printed here: the component shows it
     only once the revised rules are saved, and emits `rulesCompleted` / browser event `rules-completed`. --}}
{!! \App\Services\Training\Conclusions::split($training->renderSection('conclusions'))['recap'] !!}
<div class="mt-8 min-w-0 max-w-full">
    <livewire:personal-rules-form />
</div>
