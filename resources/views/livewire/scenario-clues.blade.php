{{--
    Module 2, interactive condition. The generated HTML parts (before / decision point / after) are model output
    printed as for the paper's conditions, inside wire:ignore so Livewire never re-renders them (their scripts and
    the radio chosen are kept). The part after the decision point is printed only once the answer is saved. The email fields come from ScenarioEmail: escaped, with the reachable clues marked.
--}}
@if (! $available)
    <div class="min-w-0 max-w-full">
        <div wire:ignore>{!! $html !!}</div>
    </div>
@else
    <div class="min-w-0 max-w-full" data-m2-completed="{{ $completed ? 'true' : 'false' }}">
        <style>
            .m2-email [data-clue-state="current"] { background: #fde68a; color: inherit; outline: 2px solid #d97706; outline-offset: 1px; border-radius: 2px; cursor: pointer; }
            .m2-email [data-clue-state="opened"] { background: #fef3c7; color: inherit; border-radius: 2px; }
            .m2-email [data-clue-number]::after { content: attr(data-clue-number); display: inline-block; margin-left: 2px; padding: 0 5px; border-radius: 9999px; background: #b45309; color: #fff; font-size: 0.7em; font-weight: 600; line-height: 1.4; vertical-align: super; }
            .m2-email [data-clue-current]:focus-visible { outline: 3px solid #1d4ed8; }
        </style>

        <div wire:ignore>{!! $section['before'] !!}</div>

        <section aria-labelledby="m2-email-heading" class="my-6 min-w-0 max-w-full text-left">
            <h3 id="m2-email-heading" class="text-lg md:text-xl font-bold mb-2">The email</h3>
            <p class="text-base text-gray-700 mb-3">The suspicious elements of this email are highlighted one at a time. Select the highlighted element (or use the button below) to read why it is suspicious.</p>

            <div x-data
                 x-on:click="if ($event.target.closest('[data-clue-current]')) { $wire.openNext() }"
                 x-on:keydown="if (($event.key === 'Enter' || $event.key === ' ') && $event.target.closest('[data-clue-current]')) { $event.preventDefault(); $wire.openNext() }">
                <x-training.email-card label="Phishing scenario email" class="m2-email mb-4"
                                       :sender-name="$fields['sender_name']" :sender-address="$fields['sender_address']"
                                       :reply-to="$fields['reply_to']" :recipient="$fields['recipient']" :date="$fields['date']"
                                       :subject="$fields['subject']" :attachments="$fields['attachments']">
                    {{ $fields['body'] }}
                </x-training.email-card>
            </div>

            <div class="rounded-xl border border-yellow-300 bg-yellow-50 p-3 md:p-4 text-base">
                <p class="font-semibold" role="status" aria-live="polite">Suspicious elements opened: {{ count($openedClues) }} of {{ $total }}</p>
                @if ($current)
                    <p class="mt-1">
                        @if ($current['located'])
                            Element {{ $current['number'] }} is highlighted in the email ({{ $fieldLabels[$current['field']] ?? $current['field'] }}).
                        @else
                            Element {{ $current['number'] }} could not be highlighted in the email: open it to read the explanation.
                        @endif
                    </p>
                    <button type="button" wire:click="openNext" wire:loading.attr="disabled"
                            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50">
                        Show suspicious element {{ $current['number'] }}
                    </button>
                @else
                    <p class="mt-1">You have opened all the suspicious elements. Now answer the question below.</p>
                @endif

                @if ($openedClues)
                    <ol class="mt-4 space-y-2" aria-label="Suspicious elements">
                        @foreach ($openedClues as $clue)
                            <li class="rounded-lg bg-white px-3 py-2 break-words [overflow-wrap:anywhere]">
                                <span class="inline-block rounded-full bg-yellow-700 text-white text-xs font-semibold px-2 mr-1">{{ $clue['number'] }}</span>
                                <span class="font-semibold">{{ $clue['label'] }}</span>
                                <span class="text-sm text-gray-600">— {{ $fieldLabels[$clue['field']] ?? $clue['field'] }}</span>
                                <p class="text-gray-700 mt-1">{{ $clue['explanation'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        @if ($section['decision'] !== null)
            {{-- Decision point: hidden until every suspicious element is opened (server state). The chosen radio is
                 sent as its index; value and label are read by the server from the generated HTML. --}}
            <div @class(['hidden' => ! $allOpened]) data-m2-decision
                 x-data
                 x-on:submit.prevent
                 x-on:change="const radios = [...$el.querySelectorAll('input[type=radio]')]; const i = radios.indexOf($event.target); if (i >= 0) { $wire.saveDecision(i) }">
                <div wire:ignore>{!! $section['decision'] !!}</div>
                <p role="status" aria-live="polite" class="text-base text-green-700">{{ $decisionSaved ? 'Your answer has been saved.' : '' }}</p>
            </div>
        @endif
        @if ($showAfter)
            {{-- What follows the decision point (feedback, reflection) reveals the answer: it is printed only once the
                 answer is saved. Inserted by a Livewire update, its scripts would not run: x-init re-creates them. --}}
            @if ($runAfterScripts)
                <div data-m2-after x-data x-init="$el.querySelectorAll('script').forEach((old) => { const s = document.createElement('script'); for (const a of old.attributes) { s.setAttribute(a.name, a.value) } s.textContent = old.textContent; old.replaceWith(s) })">
            @else
                <div data-m2-after>
            @endif
                <div wire:ignore>{!! $section['after'] !!}</div>
            </div>
        @endif
    </div>
@endif
