<x-app-layout>
    <x-slot name="slot">
        <div x-data="load()" class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white md:min-w-100 rounded-2xl shadow-xl z-20">
                <h1 class="text-3xl font-bold text-center mb-5">Training</h1>
                <div x-show="step === 1" id="section-1">
                    <h3 class="text-xl mt-5 mb-6">Introduction</h3>
                    {!! $training->introduction !!}
                    <h2 class="text-l w-full text-center mt-10">Section 1 of 5</h2>
                </div>

                <div x-show="step === 2" id="section-2">
                    <h3 class="text-xl mt-5 mb-6">Phishing Scenario</h3>
                    {!! $training->scenario !!}
                    <h2 class="text-l w-full text-center mt-10">Section 2 of 5</h2>
                </div>

                <div x-show="step === 3" id="section-3">
                    <h3 class="text-xl mt-5 mb-6">Defense Strategies</h3>
                    {!! $training->defense_strategies !!}
                    <h2 class="text-l w-full text-center mt-10">Section 3 of 5</h2>
                </div>

                <div x-show="step === 4" id="section-4">
                    <h3 class="text-xl mt-5 mb-6">Exercises</h3>
                    {!! $training->exercises !!}
                    <h2 class="text-l w-full text-center mt-10">Section 4 of 5</h2>
                </div>

                <div x-show="step === 5" id="section-5">
                    <h3 class="text-xl mt-5 mb-6">Conclusion</h3>
                    {!! $training->conclusion !!}
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
                        <div x-show="step > 0" class="flex-1"></div>
                        <div :class="{'flex-1 w-full': step === 0}">
                            <button type="button" id="next" @click="next()"
                                    :class="{'w-full' : step === 0, 'w-64' : step > 0}"
                                    class="py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            function load() {
                let num_steps = 5;
                return {
                    step: 1,
                    previous(e) {
                        if (this.step > 1)
                            this.step--;

                        $("#alert").hide();

                        $(window).scrollTop(0);

                        if (this.step < num_steps) {
                            $("#next").text("Next");
                        }
                    },
                    next(e) {
                        if (this.step !== num_steps+1) {
                            this.step++;
                            $("#alert").hide();
                        }
                        $(window).scrollTop(0);
                        if (this.step === num_steps) {
                            $("#next").text("Proceed to last phase");
                        }
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
