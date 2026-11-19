{{--
    Module 3, interactive condition: diagram of the scenario email with the defense guidelines (DefenseDiagram).
    $diagram: null (no valid guidelines: nothing shown) | {items, svg: null|geometry}. Every string is escaped.
    The numbered list is the text alternative: always in the page for screen readers, the only visible output below
    md (the SVG is hidden there: scaled to the screen its text is too small to read) and when the SVG cannot be drawn.
--}}
@if ($diagram !== null)
    <figure class="mt-8 min-w-0 max-w-full text-left" aria-labelledby="m3-diagram-caption">
        @if ($diagram['svg'] !== null)
            @php($svg = $diagram['svg'])
            <svg viewBox="0 0 {{ $svg['width'] }} {{ $svg['height'] }}" width="100%" preserveAspectRatio="xMidYMin meet"
                 role="img" aria-labelledby="m3-diagram-title" aria-describedby="m3-guidelines"
                 class="hidden md:block w-full max-w-3xl h-auto mx-auto" font-family="ui-sans-serif, system-ui, sans-serif">
                <title id="m3-diagram-title">The phishing scenario email with the defense guidelines connected to the parts they refer to</title>
                {{-- Email thumbnail --}}
                <rect x="{{ $svg['card']['x'] }}" y="{{ $svg['card']['y'] }}" width="{{ $svg['card']['width'] }}" height="{{ $svg['card']['height'] }}" rx="10" fill="#ffffff" stroke="#9e9e9e"/>
                @foreach ($svg['rows'] as $row)
                    @switch($row['kind'])
                        @case('avatar')
                            <circle cx="{{ $row['x'] }}" cy="{{ $row['y'] }}" r="14" fill="#e5e7eb"/>
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] + 5 }}" text-anchor="middle" font-size="14" font-weight="700" fill="#24262d">{{ $row['text'] }}</text>
                            @break
                        @case('strong')
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] }}" font-size="12" font-weight="700" fill="#24262d">{{ $row['text'] }}</text>
                            @break
                        @case('muted')
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] }}" font-size="11" fill="#4c4f52">{{ $row['text'] }}</text>
                            @break
                        @case('rule')
                            <line x1="{{ $row['x'] }}" y1="{{ $row['y'] }}" x2="{{ $svg['card']['x'] + $svg['card']['width'] - 10 }}" y2="{{ $row['y'] }}" stroke="#d5d6d7"/>
                            @break
                        @case('link')
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] }}" font-size="11" fill="#1d4ed8" text-decoration="underline">{{ $row['text'] }}</text>
                            @break
                        @case('attachment')
                            <rect x="{{ $row['x'] - 4 }}" y="{{ $row['y'] - 13 }}" width="{{ $svg['card']['width'] - 60 }}" height="18" rx="3" fill="#f4f5f7" stroke="#d5d6d7"/>
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] }}" font-size="11" fill="#24262d">{{ $row['text'] }}</text>
                            @break
                        @default
                            <text x="{{ $row['x'] }}" y="{{ $row['y'] }}" font-size="11" fill="#24262d">{{ $row['text'] }}</text>
                    @endswitch
                @endforeach
                @if ($svg['general'])
                    <rect x="{{ $svg['general']['x'] }}" y="{{ $svg['general']['y'] }}" width="{{ $svg['general']['width'] }}" height="{{ $svg['general']['height'] }}" rx="8" fill="#f4f5f7" stroke="#9e9e9e" stroke-dasharray="4 3"/>
                    <text x="{{ $svg['general']['x'] + 14 }}" y="{{ $svg['general']['y'] + 27 }}" font-size="12" font-weight="700" fill="#24262d">{{ $svg['general']['label'] }}</text>
                @endif
                {{-- Connectors first, then numbered circles on top --}}
                @foreach ($svg['items'] as $item)
                    <line x1="{{ $item['marker']['x'] }}" y1="{{ $item['marker']['y'] }}" x2="{{ $item['badge']['x'] }}" y2="{{ $item['badge']['y'] }}" stroke="#707275" stroke-width="1"/>
                @endforeach
                @foreach ($svg['items'] as $item)
                    <circle cx="{{ $item['marker']['x'] }}" cy="{{ $item['marker']['y'] }}" r="10" fill="#1d4ed8"/>
                    <text x="{{ $item['marker']['x'] }}" y="{{ $item['marker']['y'] + 4 }}" text-anchor="middle" font-size="11" font-weight="700" fill="#ffffff">{{ $item['number'] }}</text>
                    <circle cx="{{ $item['badge']['x'] }}" cy="{{ $item['badge']['y'] }}" r="10" fill="#1d4ed8"/>
                    <text x="{{ $item['badge']['x'] }}" y="{{ $item['badge']['y'] + 4 }}" text-anchor="middle" font-size="11" font-weight="700" fill="#ffffff">{{ $item['number'] }}</text>
                    @foreach ($item['lines'] as $line)
                        <text x="{{ $line['x'] }}" y="{{ $line['y'] }}" font-size="12" fill="#24262d">{{ $line['text'] }}</text>
                    @endforeach
                @endforeach
            </svg>
        @endif
        <figcaption id="m3-diagram-caption" class="{{ $diagram['svg'] !== null ? 'sr-only' : 'text-lg font-semibold mb-2' }}">Defense guidelines</figcaption>
        <ol id="m3-guidelines" class="{{ $diagram['svg'] !== null ? 'mt-4 md:sr-only' : '' }} space-y-2 text-base" style="list-style: none">
            @foreach ($diagram['items'] as $item)
                <li class="flex gap-2 break-words [overflow-wrap:anywhere]">
                    <span class="shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-700 text-white text-xs font-bold" aria-hidden="true">{{ $item['number'] }}</span>
                    <span><span class="sr-only">{{ $item['number'] }}.</span> {{ $item['text'] }} <span class="text-gray-600">({{ $item['part_label'] }})</span></span>
                </li>
            @endforeach
        </ol>
    </figure>
@endif
