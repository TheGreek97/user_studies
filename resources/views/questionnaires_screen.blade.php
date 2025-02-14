<x-app-layout>
    <x-slot name="slot">
        @php
            // Conta quanti questionari sono stati completati
            $completed = 0;
            if(session()->has('questionnaire_1done')) $completed++;
            if(session()->has('questionnaire_2done')) $completed++;
            if(session()->has('questionnaire_3done')) $completed++;
            $total = 3; // Numero totale di questionari
        @endphp

        <div class="min-h-screen bg-gray-200 flex flex-col justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20 w-full max-w-2xl">
                <h1 class="text-3xl font-bold mb-6 text-gray-800 text-center">
                    Please fill out all the questionnaires
                </h1>

                <!-- Contatore questionari completati -->
                <p class="text-center text-lg font-semibold text-gray-700 mb-4">
                    Completed: <span class="text-green-500">{{ $completed }}</span> / {{ $total }}
                </p>
                

                <div class="flex flex-col md:flex-row md:justify-between md:space-x-4 space-y-4 md:space-y-0">
                    <form id="sessionForm" action="{{ route('set.session') }}" method="POST">
                        @csrf
                        <input type="hidden" name="questionnaire" id="questionnaireInput">
                    </form>

                    <!-- Bottone Questionario 1 -->
                    <button onclick="setQuestionnaire('questionnaire1_view')"
                        class="flex-1 px-4 py-3 text-lg text-white bg-blue-500  hover:bg-blue-800 rounded-2xl disabled:bg-green-500 disabled:cursor-not-allowed"
                        @if(session()->has('questionnaire_1done')) disabled title="You have already answered this questionnaire" @endif>
                        Questionnaire 1
                    </button>

                    <!-- Bottone Questionario 2 -->
                    <button onclick="setQuestionnaire('questionnaire2_view')"
                        class="flex-1 px-4 py-3 text-lg text-white bg-blue-500  hover:bg-blue-800 rounded-2xl disabled:bg-green-500 disabled:cursor-not-allowed"
                        @if(session()->has('questionnaire_2done')) disabled title="You have already answered this questionnaire" @endif>
                        Questionnaire 2
                    </button>

                    <!-- Bottone Questionario 3 -->
                    <button onclick="setQuestionnaire('questionnaire3_view')"
                        class="flex-1 px-4 py-3 text-lg text-white bg-blue-500  hover:bg-blue-800 rounded-2xl disabled:bg-green-500 disabled:cursor-not-allowed"
                        @if(session()->has('questionnaire_3done')) disabled title="You have already answered this questionnaire" @endif>
                        Questionnaire 3
                    </button>
                </div>

                <div class="mt-6 flex justify-center items-center">
                    <button onclick="setQuestionnaire('questionnaires_done')"
                        class="py-3 w-1/2 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl disabled:bg-gray-400 disabled:cursor-not-allowed"
                        @if($completed < $total) disabled title="Complete all questionnaires first!" @endif>
                        Continue
                    </button>
                </div>
            </div>
        </div>      
    </x-slot>
</x-app-layout>

<script>
    function setQuestionnaire(questionnaire) {
        document.getElementById('questionnaireInput').value = questionnaire;
        document.getElementById('sessionForm').submit();
    }
</script>
