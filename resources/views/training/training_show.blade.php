<x-app-layout>
    <x-slot name="slot">
        <div x-data="load()" class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white md:min-w-100 rounded-2xl shadow-xl z-20">
                <h1 class="text-3xl font-bold text-center mb-5">Anti-phishing Training</h1>

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
                    <div class="text-xl text-justify">
                        {!! $training->introduction !!}
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 1 of 5</h2>
                </div>

                <div x-show="step === 2" id="section-scenario">
                    <div class="text-xl text-justify">
                    <div class="text-2xl font-bold text-center mb-6"> -- Phishing Scenario -- </div>
                        {!! $training->scenario !!}
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 2 of 5</h2>
                </div>

                <div x-show="step === 3" id="section-strategies">

                    <div class="text-2xl font-bold text-center mb-6"> -- Defense Strategies -- </div>
                    <div class="text-xl text-justify">
                        {!! $training->defense_strategies !!}
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 3 of 5</h2>
                </div>

                <div x-show="step === 4" id="section-exercises">
                    <div class="text-2xl font-bold text-center mb-6"> -- Exercises -- </div>
                    <div class="text-xl text-justify">
                        {!! $training->exercises !!}
                    </div>
                    <h2 class="text-l w-full text-center mt-10">Section 4 of 5</h2>
                </div>

                <div x-show="step === 5" id="section-conclusions">
                    <div class="text-2xl font-bold text-center mb-6"> -- Conclusion -- </div>
                    <div class="text-xl text-justify">
                        {!! $training->conclusions !!}
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
                step: 0,
                canProceed: 1,  // the first step does not have time requirements
                showModal: false, // Controls modal visibility

                previous() {
                    if (this.step > 1) {
                        this.step--;
                    }
                    this.setTimer(); // Reset the timer when going back

                    $(window).scrollTop(0);
                    if (this.step < num_steps) {
                        $("#next").text("Next");
                    }
                },

                next() {
                    console.log(this.step, this.canProceed)
                    if (this.step >= this.canProceed) {
                        this.showModal = true; // Show modal if user clicks too early
                        return; // Prevent advancing before time
                    }
                    if (this.step < num_steps) {
                        this.step++;
                        this.setTimer(); // Start timer for new section
                        $("#next").text(this.step === num_steps ? "Proceed to last phase" : "Next");
                    } else if (this.step === num_steps) {
                        this.completeTraining(); // Redirect to controller
                    }
                    $(window).scrollTop(0);
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
