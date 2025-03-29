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
                        You have been expelled from the study
                    </div>
                    <div class="text-xl max-w-2xl text-left pt-3">
                        Unfortunately, based on our criteria, you did not meet the necessary conditions to complete this study. <br>This is because you answered incorrectly to a quality check question, which indicates that your responses were given without the attention and focus required for this study.
                        <br>
                        As a result, we will not include your data in our analysis.
                        <br><br>
                        <div class="text-center mt-6">
                            Thank you for your time.
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
