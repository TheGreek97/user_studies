<li class="rounded-lg bg-white px-3 py-2 text-sm">
    <span aria-hidden="true">{{ $group === 'found' ? '✓' : '•' }}</span>
    <span class="font-semibold">{{ $item['label'] }}</span>
    @if ($item['field'])
        <span class="text-gray-500">— {{ $fieldLabels[$item['field']] ?? $item['field'] }}@if ($item['text']): <mark class="bg-yellow-200">{{ $item['text'] }}</mark>@endif</span>
    @endif
    <p class="text-gray-700 mt-1">{{ $item['explanation'] }}</p>
</li>
