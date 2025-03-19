<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sky-900">
                        <!-- Questionnaire -->
                        <form class="pb-4" action="{{ route('big-five-inventory.create') }}" method="POST">
                            @csrf
                            @method('post')

                            <p class="text-3xl font-bold text-center mb-4 cursor-pointer">
                                Questionnaire 1 of 3</p>
                            @php
                                $questions = trans('questionnaire-campaign.bfi2xs.question');
                                $questionsPerTab = 16;
                                $totalQuestions = count($questions);
                                $correctAnswer = 1;
                                $trivialPosition = rand(3, $totalQuestions - 3);
                            @endphp

                            <p class="text-2xl section-counter text-center mb-4">
                               
                            </p>
                            <div>
                                @foreach (array_chunk($questions, $questionsPerTab) as $tabIndex => $tabQuestions)
                                    <p class="font-semibold text-center  text-lg md:text-xl pt-6">Here are a number of characteristics
                                        that may or may not apply to you. For example, do you agree that you are
                                        someone who likes to spend time with others? 
                                        Please select the option that best represents the extent to which you agree or disagree with each statement.
                                    </p>

                                    <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 pt-4">I am someone who... </p>
                                    <div class="tab hidden">
                                        @foreach ($tabQuestions as $index => $question)
                                            {{-- Trivial question --}}
                                            @if (($tabIndex * $questionsPerTab + $index) == $trivialPosition)
                                                <div class="py-4 flex flex-col border-b-2 border-gray-300">
                                                    <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 py-3">
                                                        This item is a quality control check, please select {{ __("questionnaire-campaign.bfi2xs.scale.$correctAnswer") }}.
                                                    </p>
                                                    <div class="flex flex-col md:flex-row items-start md:items-start w-full md:justify-around gap-4">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <div class="flex flex-row md:flex-col items-start md:items-center md:w-1/5">
                                                                <input
                                                                    class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full 
                                                                        checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                    type="radio"
                                                                    id="trivial_question_{{ $i }}"
                                                                    name="trivial_question"
                                                                    value="{{ $i == $correctAnswer ? 1 : 0 }}" required>
                                                                <label
                                                                    for="trivial_question_{{ $i }}"
                                                                    class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                    @lang("questionnaire-campaign.bfi2xs.scale.$i")
                                                                </label>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="py-4 flex flex-col border-b-2 border-gray-300">
                                                <!-- Question -->
                                                <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 py-3">
                                                    {{ $question['text'] }}
                                                </p>
                                    
                                                <!-- Answer Options -->
                                                <div class="flex flex-col md:flex-row items-start md:items-start w-full md:justify-around gap-4">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <div class="flex flex-row md:flex-col items-start md:items-center md:w-1/5">
                                                            <input class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full 
                                                                        checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                                                type="radio"
                                                                id="q{{ $loop->parent->iteration * $questionsPerTab - ($questionsPerTab - $loop->iteration) }}_{{ $i }}"
                                                                name="q{{ $loop->parent->iteration * $questionsPerTab - ($questionsPerTab - $loop->iteration) }}"
                                                                value="{{ $i }}" required>
                                    
                                                            <label for="q{{ $loop->parent->iteration * $questionsPerTab - ($questionsPerTab - $loop->iteration) }}_{{ $i }}"
                                                                class="italic text-gray-700 text-center md:text-lg ml-2 md:ml-0">
                                                                @lang("questionnaire-campaign.bfi2xs.scale.$i")
                                                            </label>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                @endforeach
                            </div>

                            <input type="hidden" id="fastClickCount" name="fastClickCount" value="0">

                            <div class="flex w-full items-center py-4">
                                <div class="flex w-1/3 justify-center">
                                    <x-primary-button type="button" id="prevBtn"
                                        onclick="nextPrev(-1)">Previous</x-primary-button>
                                </div>

                                <!-- Circles which indicate the steps of the form: -->
                                <div class="flex w-1/3 justify-center flex-row">
                                    @for ($i = 0; $i < ceil(count(trans('questionnaire-campaign.bfi2xs.question')) / $questionsPerTab); $i++)
                                        <span class="step"></span>
                                    @endfor
                                </div>

                                <div class="flex w-1/3 justify-center">
                                    <x-primary-button type="button" id="nextBtn"
                                        onclick="nextPrev(1)">Next</x-primary-button>

                                    <x-primary-button id="submit" class="hidden"
                                        type="submit">@lang('questionnaire-campaign.bfi2xs.submit')</x-primary-button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>

<!-- Error modal -->
<x-modal name="error-modal" id="error-modal" title="Compile all the questions!" :show="false">
    <div class="p-4 rounded-lg relative text-center">
        <p class="text-2xl font-semibold text-red-700 pb-8">@lang('questionnaire-campaign.bfi2xs.compileError')</p>
        <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
    </div>
</x-modal>

<x-modal name="to-fast-modal" id="to-fast-modal" title="Compile all the questions slowly!" :show="false" x-data="{ show: false }" x-show="show" @open-modal.window="show = true">
    <div class="p-4 rounded-lg relative text-center">
        <p class="text-2xl font-semibold text-red-700 pb-8">You're going too fast!</p>
        <p class="text-lg text-gray-800 pb-8">
            Please slow down and carefully read each question before selecting an answer. <br> 
            Your thoughtful responses matter to us.
        </p>
        <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
    </div>
</x-modal>

<script src="{{ asset('js/questionnaireForm.js') }}"></script>
