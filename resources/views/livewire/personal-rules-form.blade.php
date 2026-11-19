{{-- Module 5 personal rules (interactive condition). The tutor comment is model output: always escaped. --}}
<div class="personal-rules w-full min-w-0 max-w-full md:max-w-3xl mx-auto text-left text-base md:text-xl" data-rules-completed="{{ $completed ? 'true' : 'false' }}">
    <h3 class="text-xl font-bold mb-2">Your personal rules</h3>
    @if ($stage === 'write')
        <p class="mb-4">
            Think about the emails of the exercises. Write {{ $minRules }} or 3 short rules you will follow from now on,
            in the form <span class="italic">"When ..., I ..."</span>. Do not write personal data.
        </p>
    @elseif ($stage === 'revise')
        <p class="mb-4">You can now improve your rules once. Change what you like, or keep them as they are, then save.</p>
    @endif

    @if ($commentMessage)
        <section aria-label="Tutor comment on your rules" class="mb-4">
            <div class="rounded-xl bg-blue-50 border border-blue-200 px-3 md:px-4 py-3 break-words [overflow-wrap:anywhere]" role="status" aria-live="polite">
                <p class="text-sm font-semibold text-blue-900 mb-1">Tutor</p>
                <p>{{ $commentMessage }}</p>
            </div>
        </section>
    @elseif ($commentSkipped && $stage === 'revise')
        <p class="mb-4 text-gray-700" role="status">Read your rules again and check whether they would have helped you in each exercise.</p>
    @endif

    @if ($notice)
        <p class="mb-3 rounded-md bg-yellow-50 border border-yellow-300 px-3 py-2 text-sm" role="status" aria-live="polite">{{ $notice }}</p>
    @endif

    @if ($stage === 'done')
        <ol class="list-decimal list-inside space-y-1 mb-6 break-words [overflow-wrap:anywhere]" aria-label="Your rules">
            @foreach ($rules as $rule)
                @if (trim($rule) !== '')
                    <li>{{ $rule }}</li>
                @endif
            @endforeach
        </ol>
        @if ($thanks)
            {{-- Generated "thanks" part of the conclusions (prompt M5), shown only now. --}}
            <div class="mt-6 [&_p]:mb-4" data-section="thanks">{!! $thanks !!}</div>
        @endif
    @else
        <form wire:submit.prevent="submit" class="space-y-3" aria-label="Your personal rules">
            @foreach ([0, 1, 2] as $i)
                <div>
                    <label for="rule-{{ $i }}" class="font-semibold block mb-1">
                        Rule {{ $i + 1 }}@if ($i >= $minRules) <span class="font-normal text-gray-600">(optional)</span>@endif
                    </label>
                    <textarea id="rule-{{ $i }}" wire:model.defer="rules.{{ $i }}" rows="2" maxlength="{{ $maxChars }}"
                              placeholder="When ..., I ..."
                              aria-describedby="rules-help @error('rules') rules-error @enderror"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200"></textarea>
                </div>
            @endforeach
            <p id="rules-help" class="text-xs text-gray-500">At least {{ $minRules }} rules, at most {{ $maxChars }} characters in total.</p>
            @error('rules')
                <p id="rules-error" class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
            @error('rules.*')
                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
            <div class="flex items-center justify-end gap-3">
                <span wire:loading wire:target="submit" class="text-sm text-gray-500" role="status">
                    {{ $stage === 'write' ? 'The tutor is reading your rules…' : 'Saving…' }}
                </span>
                <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50">
                    {{ $stage === 'write' ? 'Send my rules' : 'Save my final rules' }}
                </button>
            </div>
        </form>
    @endif
</div>
