<x-app-layout>
    <x-slot name="slot">
        <div x-data="load()"@if ($training->isInteractive()) x-on:tutor-completed.window="tutorDone = true" x-on:m1-completed.window="m1Done = true" x-on:m2-completed.window="m2Done = true" x-on:rules-completed.window="rulesDone = true"@endif class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="{{ $training->isInteractive() ? 'p-4 md:p-12 m-2 md:m-10 min-w-0 max-w-full' : 'p-12 m-10' }} bg-white md:min-w-100 rounded-2xl shadow-xl z-20">
                <h1 class="text-3xl font-bold text-center mb-5">Anti-phishing Training</h1>
                @if (session('error'))
                    <div role="alert" class="mb-5 rounded-lg bg-red-100 px-4 py-3 text-red-800">{{ session('error') }}</div>
                @endif

                <div x-show="step === 0" id="section-training-descritpion">
                    <p class="text-justify text-xl">
                        You are now entering the central part of our study, where you will be exposed to phishing training.
                        <br>
                        Please read the entire training carefully as it is essential to the accuracy of the study results and the effectiveness of the research.
                        <br>
                        You should take approximately <b>{{Auth::user()?->training_length == "short" ? 8 : 15}} minutes </b> to read it at a medium/slow pace.
                        <br>
                        Your full attention will help to ensure that the training is properly assessed and that the findings of the study can be used to improve cybersecurity education.
                        <br><br>
                        Thank you for your cooperation!
                    </p>
                </div>

                <div x-show="step === 1" id="section-intro">
                    <div class="text-2xl font-bold text-center mb-6"> -- Introduction -- </div>
                    <div class="text-xl {{ $training->isInteractive() ? 'text-left md:text-justify' : 'text-justify' }}">
                        {!! $training->renderSection('introduction') !!}
@if ($training->isInteractive())
                        {{-- Module 1, interactive condition: scenario header + question (gates Next until answered). --}}
                        <livewire:scenario-question />
@endif
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 1 of 5</h2>
                </div>

                <div x-show="step === 2" id="section-scenario">
                    <div class="text-xl {{ $training->isInteractive() ? 'text-left md:text-justify' : 'text-justify' }}">
                    <div class="text-2xl font-bold text-center mb-6"> -- Phishing Scenario -- </div>
@if ($training->isInteractive())
                        {{-- Module 2, interactive condition: annotated email, clues one at a time, then the decision point. --}}
                        <livewire:scenario-clues />
@else
                        {!! $training->renderSection('scenario') !!}
@endif
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 2 of 5</h2>
                </div>

                <div x-show="step === 3" id="section-strategies">

                    <div class="text-2xl font-bold text-center mb-6"> -- Defense Strategies -- </div>
                    <div class="text-xl {{ $training->isInteractive() ? 'text-left md:text-justify' : 'text-justify' }}">
                        {!! $training->renderSection('defense_strategies') !!}
@if ($training->isInteractive())
                        {{-- Module 3, interactive condition: diagram of the scenario email with the guidelines. --}}
                        @include('training.partials.m3-diagram', ['diagram' => \App\Services\Training\DefenseDiagram::forTraining($training)])
@endif
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 3 of 5</h2>
                </div>

                <div x-show="step === 4" id="section-exercises">
                    <div class="text-2xl font-bold text-center mb-6"> -- Exercises -- </div>
                    <div class="text-xl {{ $training->isInteractive() ? 'text-left md:text-justify' : 'text-justify' }}">
                        {!! $training->renderSection('exercises') !!}
                        @if ($training->isInteractive())
                            {{-- Module 4 of the interactive condition: emails from the bank + tutor (partial owned by the tutor lot). --}}
                            @includeIf('training.partials.m4-interactive', ['training' => $training, 'user' => $training->user])
                        @endif
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 4 of 5</h2>
                </div>

                <div x-show="step === 5" id="section-conclusions">
                    <div class="text-2xl font-bold text-center mb-6"> -- Conclusion -- </div>
                    <div class="text-xl {{ $training->isInteractive() ? 'text-left md:text-justify' : 'text-justify' }}">
                        {!! $training->isInteractive() ? view('training.partials.m5-interactive', ['training' => $training])->render() : $training->renderSection('conclusions') !!}
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 5 of 5</h2>
                </div>

                <!-- Loading view -->
                <div x-show="step === 6" class="pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-12 h-12 mx-auto stroke-green-500 animate-spin">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <div class="text-center text-green-500 font-bold my-3 text-xl w-full">
                        Loading
                    </div>
                </div>

                <div class="text-center mt-4 ">
@if ($training->isInteractive())
                    {{-- Interactive condition: same buttons, sized to fit narrow screens (no horizontal scroll at 390px). --}}
                    <div x-show="step !== 6" class="flex flex-row w-full" :class="{'space-x-3 md:space-x-6' : step > 0}">
                        <div x-show="step > 1" class="flex-1 md:flex-none">
                            <button type="button" id="back" @click="previous()"
                                    class="py-3 w-full md:w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                Previous
                            </button>
                        </div>
                        <div x-show="step >= 0" class="hidden md:block md:flex-1"></div>
                        <div :class="step === 0 ? 'flex-1 w-full' : 'flex-1 md:flex-none'">
                            <button type="button" id="next" :disabled="locked()" :title="lockMessage()" :style="locked() ? 'opacity: 0.5; cursor: not-allowed' : ''" @click="next()"
                                    :class="{'w-full' : step === 0, 'w-full md:w-64' : step > 0}"
                                    class="py-3 px-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Next
                            </button>
                        </div>
                    </div>
@else
                    <div x-show="step !== 6" class="flex flex-row w-full" :class="{'space-x-6' : step > 0}">
                        <div x-show="step > 1">
                            <button type="button" id="back" @click="previous()"
                                    class="py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                Previous
                            </button>
                        </div>
                        <div x-show="step >= 0" class="flex-1"></div>
                        <div :class="{'flex-1 w-full': step === 0}">
                            <button type="button" id="next" @click="next()"
                                    :class="{'w-full' : step === 0, 'w-64' : step > 0}"
                                    class="py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Next
                            </button>
                        </div>
                    </div>
@endif
                </div>

                <!-- Modal -->
                <div x-show="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                        <h2 class="text-xl font-semibold">Please read the training content carefully</h2>
                        <p class="mt-2 text-gray-600">It seems you're going too fast! Please take your time before proceeding to the next section.</p>
                        <div class="mt-4 flex justify-end">
                            <button @click="showModal = false" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700">I understand</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function load() {
            let num_steps = 5;
            let startTime = Date.now(); // Capture start time
            let minTimePerStep = {
                1: {{$wait_times["introduction"]}},
                2: {{$wait_times["scenario"]}},
                3: {{$wait_times["defense_strategies"]}},
                4: {{$wait_times["exercises"]}},
                5: {{$wait_times["conclusions"]}}
            }; // Required wait time in Seconds per step

            return {
@if ($training->isInteractive())
                // Interactive condition: after a reload with a started, unfinished tutor chat the page reopens
                // directly at Module 4 (step 4); otherwise it starts from the description (step 0).
                step: {{ (int) ($initial_step ?? 0) }},
                // Interactive condition: Module 4 (step 4) is passed only once the tutor chat is completed
                // (browser event tutor-completed, or data-tutor-completed="true" after a reload).
                tutorDone: false,
                // Modules 1 and 2 are passed once their activity is saved (events m1-completed / m2-completed, or
                // data-m1/m2-completed="true" after a reload); without the activity (no valid data) they are not gated.
                m1Done: true,
                m2Done: true,
                // Module 5 (step 5): "Proceed" only once the revised personal rules are saved (browser event
                // rules-completed, or data-rules-completed="true" after a reload).
                rulesDone: false,
                init() {
                    this.tutorDone = document.querySelector('[data-tutor-completed="true"]') !== null;
                    this.m1Done = document.querySelector('[data-m1-completed="false"]') === null;
                    this.m2Done = document.querySelector('[data-m2-completed="false"]') === null;
                    this.rulesDone = document.querySelector('[data-rules-completed="true"]') !== null;
                    if (this.step > 0) {
                        this.setTimer(); // resumed step: its minimum reading time starts now
                    }
                    if (this.step === num_steps) {
                        // resumed at the last module: same label next() sets when reaching it
                        document.getElementById('next').textContent = "Proceed to last phase";
                    }
                },
                locked() {
                    return (this.step === 1 && !this.m1Done) || (this.step === 2 && !this.m2Done) || (this.step === 4 && !this.tutorDone) || (this.step === 5 && !this.rulesDone);
                },
                lockMessage() {
                    if (this.step === 1 && !this.m1Done) return 'Answer the question first';
                    if (this.step === 2 && !this.m2Done) return 'Open all the suspicious elements and answer the question first';
                    if (this.step === 4 && !this.tutorDone) return 'Complete the exercises with the tutor first';
                    if (this.step === 5 && !this.rulesDone) return 'Write and save your personal rules first';
                    return '';
                },
@else
                step: 0,
@endif
                canProceed: 1,  // the first step does not have time requirements
                showModal: false, // Controls modal visibility

                previous() {
                    if (this.step > 1) {
                        this.step--;
                    }
                    this.setTimer(); // Reset the timer when going back

                    window.scrollTo(0, 0);
                    if (this.step < num_steps) {
                        document.getElementById('next').textContent = "Next";
                    }
                },

                next() {
                    console.log(this.step, this.canProceed)
@if ($training->isInteractive())
                    if (this.locked()) {
                        return; // Module 1, 2, 4 or 5 not completed yet: Next stays disabled
                    }
@endif
                    if (this.step >= this.canProceed) {
                        this.showModal = true; // Show modal if user clicks too early
                        return; // Prevent advancing before time
                    }
                    if (this.step < num_steps) {
                        this.step++;
                        this.setTimer(); // Start timer for new section
                        document.getElementById('next').textContent = this.step === num_steps ? "Proceed to last phase" : "Next";
                    } else if (this.step === num_steps) {
                        this.completeTraining(); // Redirect to controller
                    }
                    window.scrollTo(0, 0);
                },

                setTimer() {
                    if (this.canProceed < this.step) {
                        this.canProceed = this.step;
                    }
                    setTimeout(() => {
                        this.canProceed = this.step + 1;
                    }, minTimePerStep[this.step] * 1000);
                },

                completeTraining() {
                    let timeSpent = Math.floor((Date.now() - startTime) / 1000); // Calculate time in seconds
                    window.location.href = "{{route('training.complete')}}?time=" + timeSpent;
                }
            }
        }
        </script>
    </x-slot>
</x-app-layout>
