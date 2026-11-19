{{--
    Email as a mail client shows it, shared by the interactive modules (M1 header, M2 annotated email; usable by M4).
    Field props are plain strings (escaped here) or HtmlString already escaped by the caller (e.g. ScenarioEmail with
    highlights). The body is the default slot. Links never navigate: a click is cancelled and hovering/focusing a link
    shows its real address (data-href, or href for authored content) in a status bar at the bottom-left of the window;
    the sanitized body links also carry it as title (native tooltip).
--}}
@props([
    'label' => 'Email',
    'senderName' => null,
    'senderAddress' => null,
    'replyTo' => null,
    'recipient' => null,
    'date' => null,
    'subject' => null,
    'attachments' => [],
])
<article aria-label="{{ $label }}"
         {{ $attributes->merge(['class' => 'min-w-0 max-w-full border border-gray-300 rounded-xl overflow-hidden bg-white text-left']) }}
         x-data="{ hover: null, hrefOf(el) { const a = el.closest('a'); return a ? (a.dataset.href ?? a.getAttribute('href')) : null } }"
         x-on:click="if ($event.target.closest('a')) { $event.preventDefault() }"
         x-on:mouseover="hover = hrefOf($event.target)"
         x-on:focusin="hover = hrefOf($event.target)"
         x-on:mouseout="hover = null"
         x-on:focusout="hover = null">
    <dl class="bg-gray-50 border-b border-gray-200 px-3 md:px-4 py-3 text-sm grid grid-cols-[4.5rem_minmax(0,1fr)] md:grid-cols-[6rem_minmax(0,1fr)] gap-x-3 gap-y-1 break-words [overflow-wrap:anywhere]">
        @if ($senderName !== null || $senderAddress !== null)
            <dt class="text-gray-500">From</dt>
            <dd><span class="font-semibold">{{ $senderName }}</span>@if ($senderAddress !== null) &lt;{{ $senderAddress }}&gt;@endif</dd>
        @endif
        @if ($replyTo !== null)
            <dt class="text-gray-500">Reply-to</dt>
            <dd>{{ $replyTo }}</dd>
        @endif
        @if ($recipient !== null)
            <dt class="text-gray-500">To</dt>
            <dd>{{ $recipient }}</dd>
        @endif
        @if ($date !== null)
            <dt class="text-gray-500">Date</dt>
            <dd>{{ $date }}</dd>
        @endif
        @if ($subject !== null)
            <dt class="text-gray-500">Subject</dt>
            <dd class="font-semibold">{{ $subject }}</dd>
        @endif
    </dl>
    @if (! $slot->isEmpty())
        <div class="min-w-0 max-w-full px-3 md:px-4 py-4 text-base overflow-x-auto break-words [overflow-wrap:anywhere] [&_table]:w-full [&_table]:max-w-full [&_p]:mb-3 [&_a]:text-blue-700 [&_a]:underline [&_a]:cursor-pointer">
            {{ $slot }}
        </div>
    @endif
    @if (! empty($attachments))
        <ul class="px-3 md:px-4 pb-3 text-sm flex flex-wrap gap-2 break-words [overflow-wrap:anywhere]" aria-label="Attachments">
            @foreach ($attachments as $attachment)
                <li class="max-w-full border border-gray-300 rounded px-2 py-1 bg-gray-50">Attachment: {{ $attachment }}</li>
            @endforeach
        </ul>
    @endif
    {{-- Status bar fixed at the bottom-left of the window, as browsers and mail clients show a link destination:
         visible wherever the card is scrolled. Screen readers get the address from the link title. --}}
    <div class="fixed left-0 z-50 px-3 py-1 text-sm bg-gray-800 text-white font-mono rounded-tr-md truncate shadow" aria-hidden="true"
         x-show="hover" x-text="hover" style="display: none; bottom: 0; max-width: 90vw"></div>
</article>
