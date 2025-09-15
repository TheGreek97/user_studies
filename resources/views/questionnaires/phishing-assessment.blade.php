<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl">
                <div class="p-6 text-sky-900">
                    <h1 class="text-3xl font-bold text-center mb-4">
                        {{ trans('phishing_assessment.title') }}
                        ({{ trans('phishing_assessment.version') }})
                    </h1>
                    <p class="text-lg text-center mb-4">
                        {!! trans('phishing_assessment.instructions') !!}
                    </p>
                    <form action="{{ route('phishing-assessment.store') }}" method="POST">
                        <div>
                            @csrf
                            @method('post')
                            <input type="hidden" name="started_token" value="{{ $startedToken ?? '' }}">
                            @foreach($sections as $idx => $section)
                                <div id="sectionCounter_{{ $idx }}" class="tab {{ $idx === 0 ? '' : 'hidden' }}">
                                    @if($idx !== 0)
                                        <h2 class="text-xl mt-2 mb-0">{{ $section['name'] }}</h2>
                                    @endif
                                    @foreach($section['items'] as $qIdx => $item)
                                        @if($idx === 0)
                                            <div class="w-full py-4 border-t-2 border-b-2 border-solid border-gray-300 text-center">
                                                <h2 class="text-2xl font-bold text-center mb-2">{{ $section['name'] }}</h2>
                                                <p class="text-xl font-semibold text-gray-800 py-3">
                                                    {!! $item['text'] !!}
                                                </p>
                                            </div>
                                        @else
                                            @if(false)
                                                {{-- control question
                                                @if($qIdx === $controlPositions[$idx])
                                                    <div class="py-4 flex flex-col border-b-2 border-gray-300">
                                                        <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 py-3">
                                                            {{ trans('phishing_assessment.control_question') }}<em>{{ __($scale[$correctAnswers[$idx]]) }}</em>.
                                                        </p>
                                                        <div class="flex sm:flex-col md:flex-row items-start md:items-start w-full gap-4 my-4" style="justify-content: space-around">
                                                        @foreach($scale as $key => $label)
                                                                <div class="flex flex-col items-center md:w-1/5">
                                                                    <input class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full
                                                                            checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                           type="radio"
                                                                           id="control_question_{{ $idx . '_' . $key }}"
                                                                           name="control_question_{{ $idx }}"
                                                                           value="{{ $key == $correctAnswers[$idx] ? 1 : 0 }}"
                                                                           required>
                                                                    <label for="control_question_{{ $idx . '_' . $key }}"
                                                                           class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                    {{ trans("phishing_assessment.scale.$key") }}
                                                                    </label>
                                                                </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif --}}@endif
                                            <div class="py-4 flex flex-col border-b-2 border-gray-300">
                                                <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 py-3">
                                                    {!! ($item['text']) !!}
                                                </p>
                                                <div class="flex sm:flex-col md:flex-row items-start md:items-start w-full gap-4 my-4" style="justify-content: space-around">
                                                    @if(isset($item['answer_1']))
                                                        @php $ans = 1; @endphp
                                                        @while(isset($item["answer_{$ans}"]))
                                                            @php
                                                                $key = "answer_{$ans}";
                                                                $val = $section['points'][$key] ?? $ans;
                                                            @endphp
                                                            <div class="flex flex-col items-center md:w-1/3">
                                                                <input class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                       type="radio"
                                                                       id="{{ $item['id'] . '_' . $ans }}"
                                                                       name="{{ $item['id'] }}"
                                                                       value="{{  $val  }}"
                                                                       required
                                                                    {{ (string)old($item['id']) === (string)$val ? 'checked' : '' }}>
                                                                <label for="{{ $item['id'] . '_' . $ans }}"
                                                                       class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                    {{ $item[$key] }}
                                                                </label>
                                                            </div>
                                                            @php $ans++ @endphp
                                                        @endwhile
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $isOverclaiming = ($item['id'] === 'Q4' || $item['id'] === 'Q19');
                                                        @endphp
                                                        @foreach($scale as $val)
                                                            @if($isOverclaiming && $i === 1)
                                                                <div class="flex flex-col items-center md:w-1/6">
                                                                    <input class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full
                                                                    checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                           type="radio"
                                                                           id="{{ $item['id'] . '_0' }}"
                                                                           name="{{ $item['id'] }}"
                                                                           value="1-overclaiming"
                                                                           required
                                                                        {{ old($item['id']) === '1-overclaiming' ? 'checked' : '' }}>
                                                                    <label for="{{ $item['id'] . '_0' }}"
                                                                           class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                        {{ trans("phishing_assessment.overclaiming_answer") }}
                                                                    </label>
                                                                </div>
                                                            @endif
                                                            <div class="flex flex-col items-center {{ $isOverclaiming ? 'md:w-1/6' : 'md:w-1/5' }}">
                                                                <input class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full
                                                                    checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                       type="radio"
                                                                       id="{{ $item['id'] . '_' . $i }}"
                                                                       name="{{ $item['id'] }}"
                                                                       value="{{ $i }}"
                                                                       required
                                                                    {{ old($item['id']) === (string)$i ? 'checked' : '' }}>
                                                                <label for="{{ $item['id'] . '_' . $i }}"
                                                                       class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                    {{ trans("phishing_assessment.scale.$i") }}
                                                                </label>
                                                            </div>
                                                            @php $i++ @endphp
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach {{-- fine questions --}}
                                </div>
                            @endforeach {{-- fine sections --}}
                        </div>
                        <input type="hidden" name="fastClickCount" id="fastClickCount" value="0" />
                        <div class="flex justify-between items-center w-full py-4">
                            <div class="flex">
                                <x-primary-button type="button" id="prevBtn"
                                                  onclick="nextPrev(-1)">{{ trans('phishing_assessment.previous') }}</x-primary-button>
                            </div>
                            <div class="flex space-x-2">
                                <x-primary-button type="button" id="nextBtn"
                                                  onclick="nextPrev(1)">{{ trans('phishing_assessment.next') }}</x-primary-button>
                                <x-primary-button id="submit" class="hidden"
                                                  type="submit">{{ trans('phishing_assessment.submit') }}</x-primary-button>
                            </div>
                        </div>
                        <div class="flex justify-center mt-4 space-x-2">
                            @for($i=0; $i<count($sections); $i++)
                                <span class="step w-3 h-3 bg-gray-300 rounded-full"></span>
                            @endfor
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>

<!-- Error modal -->
<x-modal name="error-modal" id="error-modal" title="{{ __('phishing_assessment.compileError') }}" :show="false">
    <div class="p-4 rounded-lg relative text-center">
        <p class="text-2xl font-semibold text-red-700 pb-8">@lang('phishing_assessment.compileError')</p>
        <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
    </div>
</x-modal>

<x-modal name="too-fast-modal" id="too-fast-modal" title="{{  __('phishing_assessment.too_fast_title') }}" :show="false" x-data="{ show: false }" x-show="show" @open-modal.window="show = true">
    <div class="p-4 rounded-lg relative text-center">
        <p class="text-2xl font-semibold text-red-700 pb-8">@lang('phishing_assessment.too_fast_heading')</p>
        <p class="text-lg text-gray-800 pb-8">{!! trans('phishing_assessment.too_fast_body') !!}</p>
        <x-primary-button x-on:click="$dispatch('close')">@lang('phishing_assessment.close')</x-primary-button>
    </div>
</x-modal>

<script>
    window.appConfig = {
        disableTimers: @json(env('DISABLE_TIMERS', false))
    };
</script>
<script src="{{ asset('js/questionnaireForm.js') }}" defer></script>

<style>
    .step { display:inline-block; width:20px; height:20px; border-radius:50%; background:#E5E7EB; margin:0 5px; transition:background 0.3s }
    .step.active, .step.finish { background:#3F83F8 }
</style>
