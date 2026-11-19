{{-- Module 1, interactive condition: scenario header + fixed question. All model strings are escaped. --}}
@if ($header === null)
    <div class="hidden" aria-hidden="true"></div>
@else
    <section aria-labelledby="m1-question" class="mt-8 min-w-0 max-w-full text-left" data-m1-completed="{{ $saved ? 'true' : 'false' }}">
        <p class="mb-3 text-base md:text-lg text-gray-700 italic">{{ $header['scenario_context'] }}</p>

        <x-training.email-card label="Email received" class="mb-6"
                               :sender-name="$header['sender_name']" :sender-address="$header['sender_address']" :subject="$header['subject']">
            <p class="text-gray-700">{{ $header['first_line'] }} <span class="text-gray-500" aria-hidden="true">…</span></p>
        </x-training.email-card>

        <form wire:submit.prevent="save" class="min-w-0">
            <label id="m1-question" for="m1-answer" class="block text-lg md:text-xl font-semibold mb-2">{{ $question }}</label>
            <textarea id="m1-answer" wire:model.defer="answer" rows="4" maxlength="{{ $maxChars }}"
                      aria-describedby="m1-answer-help @error('answer') m1-answer-error @enderror"
                      class="w-full rounded-lg border-gray-300 text-base focus:border-blue-500 focus:ring-blue-500"></textarea>
            <p id="m1-answer-help" class="text-sm text-gray-500 mt-1">Write your answer in a few words (max {{ $maxChars }} characters). It is not graded.</p>
            @error('answer')
                <p id="m1-answer-error" role="alert" class="text-sm text-red-700 mt-1">{{ $message }}</p>
            @enderror
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 text-base bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50">
                    {{ $saved ? 'Update answer' : 'Save answer' }}
                </button>
                <p role="status" aria-live="polite" class="text-sm text-green-700">{{ $saved ? 'Answer saved. You can continue.' : '' }}</p>
            </div>
        </form>
    </section>
@endif
