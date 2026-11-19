{{-- Module 4 tutor chat. Receives only the browser-safe projection of the email while its dialog is open. --}}
<div class="tutor-chat w-full min-w-0 max-w-full md:max-w-3xl mx-auto text-left text-base md:text-xl" data-tutor-completed="{{ $completed ? 'true' : 'false' }}"
     x-data x-on:tutor-email-changed.window="setTimeout(() => $refs.heading?.focus(), 0)">
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 mb-4">
        {{-- After "Next email" focus moves here (tabindex=-1) and the status below announces the new exercise. --}}
        <h3 id="tutor-exercise-heading" x-ref="heading" tabindex="-1" class="text-xl font-bold focus:outline-none">Exercise {{ $position }} of {{ $total }}</h3>
        <span class="text-sm text-gray-600">Is this email phishing or legitimate? Explain why.</span>
    </div>
    <p class="sr-only" role="status" aria-live="polite" aria-atomic="true">Exercise {{ $position }} of {{ $total }}</p>

    @if (! empty($email['context']))
        <p class="mb-3 text-base text-gray-700 italic">{{ $email['context'] }}</p>
    @endif

    {{-- The email, as a mail client would show it. Links are not followed; hovering/focusing shows the real address. --}}
    <article aria-label="Email to analyse" class="min-w-0 max-w-full border border-gray-300 rounded-xl overflow-hidden mb-6 bg-white"
             x-data="{ hover: null }"
             x-on:click="if ($event.target.closest('a')) { $event.preventDefault() }"
             x-on:mouseover="hover = $event.target.closest('a')?.getAttribute('href') ?? null"
             x-on:focusin="hover = $event.target.closest('a')?.getAttribute('href') ?? null"
             x-on:mouseout="hover = null">
        <dl class="bg-gray-50 border-b border-gray-200 px-3 md:px-4 py-3 text-sm grid grid-cols-[4.5rem_minmax(0,1fr)] md:grid-cols-[6rem_minmax(0,1fr)] gap-x-3 gap-y-1 break-words [overflow-wrap:anywhere]">
            <dt class="text-gray-500">From</dt>
            <dd><span class="font-semibold">{{ $email['sender_name'] ?? '' }}</span> &lt;{{ $email['sender_address'] ?? '' }}&gt;</dd>
            @if (! empty($email['reply_to']))
                <dt class="text-gray-500">Reply-to</dt>
                <dd>{{ $email['reply_to'] }}</dd>
            @endif
            <dt class="text-gray-500">To</dt>
            <dd>{{ $email['recipient']['name'] ?? '' }} &lt;{{ $email['recipient']['address'] ?? '' }}&gt;</dd>
            @if ($emailDate)
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $emailDate }}</dd>
            @endif
            <dt class="text-gray-500">Subject</dt>
            <dd class="font-semibold">{{ $email['subject'] ?? '' }}</dd>
        </dl>
        <div class="min-w-0 max-w-full px-3 md:px-4 py-4 overflow-x-auto break-words [overflow-wrap:anywhere] [&_table]:w-full [&_table]:max-w-full [&_img]:max-w-full [&_img]:h-auto">
            {{-- Authored bank content (resources/tutor/emails-m4.json), not user input. --}}
            {!! $email['body_html'] ?? '' !!}
        </div>
        @if (! empty($email['attachments']))
            <ul class="px-3 md:px-4 pb-3 text-sm flex flex-wrap gap-2 break-words [overflow-wrap:anywhere]" aria-label="Attachments">
                @foreach ($email['attachments'] as $attachment)
                    <li class="max-w-full border border-gray-300 rounded px-2 py-1 bg-gray-50">
                        &#128206; {{ is_array($attachment) ? ($attachment['filename'] ?? '') : $attachment }}
                        @if (is_array($attachment) && isset($attachment['size_kb']))
                            <span class="text-gray-500">({{ $attachment['size_kb'] }} KB)</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
        <div class="px-4 py-1 text-xs bg-gray-100 border-t border-gray-200 text-gray-700 font-mono truncate" aria-hidden="true"
             x-show="hover" x-text="hover" style="display: none"></div>
    </article>

    {{-- Conversation with the tutor. --}}
    <section aria-label="Conversation with the tutor" class="mb-4">
        <ol class="space-y-3" role="log" aria-live="polite" aria-relevant="additions">
            @foreach ($transcript as $turn)
                @if ($turn['author'] === 'user')
                    <li class="flex justify-end">
                        <div class="min-w-0 max-w-[85%] md:max-w-[80%] break-words [overflow-wrap:anywhere] rounded-2xl rounded-br-sm bg-blue-600 text-white px-4 py-2">
                            <span class="sr-only">You:</span>
                            @if ($turn['classification'])
                                <span class="block text-xs uppercase tracking-wide opacity-80">{{ $turn['classification'] === 'phishing' ? 'Phishing' : 'Legitimate' }}</span>
                            @endif
                            {!! nl2br(e($turn['text'])) !!}
                        </div>
                    </li>
                @else
                    <li class="flex justify-start">
                        <div class="min-w-0 max-w-[85%] md:max-w-[80%] break-words [overflow-wrap:anywhere] rounded-2xl rounded-bl-sm bg-gray-100 text-gray-900 px-4 py-2">
                            <span class="block text-xs font-semibold text-gray-500">Tutor</span>
                            {!! nl2br(e($turn['text'])) !!}
                        </div>
                    </li>
                @endif
            @endforeach
        </ol>
    </section>

    @if ($closing)
        {{-- Closing: verdict from the card, tutor feedback, annotated clues (shown only after the dialog is closed). --}}
        <section aria-label="Solution of this email" aria-live="polite" class="border-2 rounded-xl p-3 md:p-4 mb-4 break-words [overflow-wrap:anywhere] {{ $closing['verdict'] === 'Phishing' ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50' }}">
            <p class="text-lg font-bold mb-1">This email is {{ $closing['verdict'] === 'Phishing' ? 'a phishing email' : 'legitimate' }}.</p>
            <p class="mb-3 text-sm">{{ $closing['correct'] ? 'Your final answer was correct.' : 'Your final answer was not correct.' }}</p>
            <div class="mb-4 rounded-lg bg-white px-4 py-2">
                <span class="block text-xs font-semibold text-gray-500">Tutor</span>
                {!! nl2br(e($closing['message'])) !!}
            </div>
            @php
                $groups = [
                    'found' => ['title' => 'What you noticed', 'shown' => $closing['found'], 'more' => []],
                    'missed' => ['title' => 'What you missed', 'shown' => $closing['missed']['shown'], 'more' => $closing['missed']['more']],
                ];
            @endphp
            @foreach ($groups as $group => $g)
                @if (! empty($g['shown']))
                    <h4 class="font-semibold mt-2 mb-1">{{ $g['title'] }}</h4>
                    <ul class="space-y-2 mb-2">
                        @foreach ($g['shown'] as $item)
                            @include('livewire.partials.tutor-closing-item', ['item' => $item, 'group' => $group])
                        @endforeach
                    </ul>
                    @if (! empty($g['more']))
                        {{-- Main signals come first; the long tail stays one click away (cap4: avoid overload at closing). --}}
                        <details class="mb-2">
                            <summary class="cursor-pointer text-sm font-semibold text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-400 rounded">Show {{ count($g['more']) }} more {{ count($g['more']) === 1 ? 'signal' : 'signals' }} you missed</summary>
                            <ul class="space-y-2 mt-2">
                                @foreach ($g['more'] as $item)
                                    @include('livewire.partials.tutor-closing-item', ['item' => $item, 'group' => $group])
                                @endforeach
                            </ul>
                        </details>
                    @endif
                @endif
            @endforeach

            @if ($completed)
                <p class="mt-4 font-semibold" role="status">You have completed all the exercises. You can continue with the training.</p>
            @elseif (! $isLast)
                <div class="mt-4 text-right">
                    <button type="button" wire:click="nextEmail" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50">
                        Next email
                    </button>
                </div>
            @endif
        </section>
    @endif

    @if ($notice)
        <p class="mb-3 rounded-md bg-yellow-50 border border-yellow-300 px-3 py-2 text-sm" role="status" aria-live="polite">{{ $notice }}</p>
    @endif

    @if ($accepting)
        <form wire:submit.prevent="send" class="space-y-3" aria-label="Your answer">
            <fieldset>
                <legend class="font-semibold mb-1">Your classification</legend>
                <div class="flex flex-wrap gap-x-4 gap-y-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tutor-choice" value="phishing" wire:model="choice" class="text-blue-600">
                        <span>Phishing</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tutor-choice" value="legitimate" wire:model="choice" class="text-blue-600">
                        <span>Legitimate</span>
                    </label>
                </div>
            </fieldset>
            <div>
                <label for="tutor-message" class="font-semibold block mb-1">
                    {{ $awaitingFirst ? 'Why? Explain your reasoning' : 'Your reply' }}
                </label>
                <textarea id="tutor-message" wire:model.defer="message" rows="3" maxlength="{{ $maxChars }}"
                          aria-describedby="tutor-message-help @error('message') tutor-message-error @enderror"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200"></textarea>
                <p id="tutor-message-help" class="text-xs text-gray-500">
                    {{ $awaitingFirst ? 'At least 4 words. It is fine to be unsure.' : 'Answer the tutor, or say what else you notice.' }}
                </p>
                @error('message')
                    <p id="tutor-message-error" class="text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-end gap-3">
                <span wire:loading wire:target="send" class="text-sm text-gray-500" role="status">The tutor is thinking…</span>
                <button type="submit" wire:loading.attr="disabled" wire:target="send"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50">
                    Send
                </button>
            </div>
        </form>
    @endif
</div>
