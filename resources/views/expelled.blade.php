<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-20 h-20 mx-auto text-red-500">
                        <!-- Cerchio -->
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <!-- Croce leggermente più corta -->
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 16L16 8M8 8l8 8"/>
                    </svg>

                    <div class="text-center font-bold my-3 text-3xl w-full text-red-600">
                        We're sorry, but we have to terminate the study
                    </div>
                    <div class="text-xl max-w-2xl text-left pt-3">
                        Unfortunately, due tue <a href="https://researcher-help.prolific.com/en/article/fb63bb">Prolific's attention and comprehension check policy</a>, you do not meet the requirements to complete this study.
                        <br>This is because you answered to an attention check question incorrectly, indicating that your answers were given without the attention and focus required for this study. <br>
                        <br>The attention check questions in our study looked like:
                        <div class="py-4 flex flex-col border-b-2 border-gray-300">
                            <p class="text-left md:text-center text-lg md:text-xl font-semibold text-gray-800 py-3">
                                This item is a quality control check, please select <i>Agree</i>.
                            </p>
                            <div class="flex flex-row items-start start w-full justify-around gap-4 text-sm" style="justify-content: space-around">
                                @for ($i = 1; $i <= 7; $i++)
                                    <div class="flex flex-col items-center w-1/7">
                                        <input
                                            class="cursor-pointer appearance-none w-5 h-5 border-2 border-gray-400 rounded-full
                                                checked:bg-sky-600 hover:border-sky-500 transition-all duration-200"
                                            type="radio"
                                            disabled
                                            id="trivial_question_{{ $i }}"
                                            name="trivial_question"
                                        >
                                        <label
                                            for="trivial_question_{{ $i }}"
                                            class="talic text-gray-400 text-center md:text-lg ml-2 md:ml-0">
                                            @lang("questionnaire-campaign.tei-que-sf.scale.$i")
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <br>
                        As a result, we stopped the session early to not make you lose any more time. Based on these criteria, we cannot proceed to paying you.
                        However, we will proceed to delete all the data related to this session and not include it in our analysis
                        <br><br>
                        <div class="text-center mt-6">
                            We're sorry for the inconvenience. Thank you for your time.
                        </div>
                        <br>
                        <div style="font-style: italic">
                            IVU Lab, University of Bari, Italy, <br>
                            University of Cagliari, Italy, <br>
                            CYS group, King's College London, UK
                        </div>
                        <div class="mt-6 flex flex-row justify-center">
                            <img src="{{asset('/assets/img/UNIBA_logo.svg')}}" alt="University of Bari logo" style="max-width: 25%">
                            <img src="{{asset('/assets/img/UNICA_logo.png')}}" alt="University of Cagliari logo" style="max-width: 25%">
                            <img src="{{asset('/assets/img/KCL_logo.png')}}" alt="King's College London logo" style="max-width: 20%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
