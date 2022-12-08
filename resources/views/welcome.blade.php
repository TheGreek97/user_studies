<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="text-lg p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Grazie per la tua partecipazione
                </div>
                <p class="max-w-4xl text-left pt-4">
                    Gentile partecipante, stiamo conducendo una ricerca presso l'Università degli studi di Bari e l'Università di Salerno a cui ti chiediamo di partecipare rispondendo ai quesiti che ti verranno posti in questo questionario anonimo. L'indirizzo e-mail inserito per partecipare al test è necessario solo per recuperare eventuali interruzioni durante la partecipazione al test e sarà cancellato al termine della sua partecipazione.
                </p>

                <div class="mt-6 ">Il questionario avrà una durata di <span style="text-underline: black; font-weight: bold">20 minuti</span> circa.</div>

                <div class="mt-8 h2 font-bold">CONSENSO INFORMATO</div>

                <div class="mt-8 h2 font-bold">La partecipazione allo studio è obbligatoria?</div>
                <p class="max-w-4xl text-left pt-4">
                    La tua partecipazione è completamente libera. Inoltre, se in un qualsiasi momento, tu dovessi cambiare idea e volessi ritirarsi dalla procedura, sei liber* di farlo.
                </p>

                <p class="max-w-4xl text-left pt-4">
                    Ti chiediamo gentilmente di rispondere sinceramente, eventuali difficoltà nelle risposte non sono riconducibili alla sua persona ma a limiti tecnologici che con questa ricerca miriamo a risolvere.
                </p>

                <div class="text-center mt-10">
                    <div class="w-full">
                        <div>
                            <button onclick="window.location.replace('{{ route('skills') }}')"
                                    class="py-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl"> Inizia il test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
