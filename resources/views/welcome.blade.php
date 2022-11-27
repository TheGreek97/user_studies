<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="text-lg p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Grazie per la tua disponibilità a partecipare
                </div>
                <p class="max-w-4xl text-left pt-4">
                    Gentile partecipante, stiamo conducendo una ricerca presso l'Università degli studi di Bari, a cui ti chiediamo di partecipare rispondendo ai quesiti che ti verranno posti in questo questionario. <br>
                    In particolare, la presente ricerca ha l'obiettivo di indagare i modelli mentali degli utenti nella definizione di regole ad alto livello (cioè senza scrivere codice) per proteggere un ambiente intelligente da attacchi informatici.
                    Intendiamo mantenere questo questionario anonimo. L'indirizzo e-mail inserito per partecipare al test è necessario solo temporaneamente e sarà cancellato dal nostro database in fase di raccolta e analisi dati con un processo di anonimizzazione.
                </p>

                <div class="mt-6 ">Il questionario avrà una durata variabile da <span style="text-underline: black; font-weight: bold">20 a 40 minuti</span> circa.</div>

                <div class="mt-6 ">Per informazioni contattare <a href="mailto:francesco.greco@uniba.it">francesco.greco@uniba.it</a></div>

                <div class="mt-8 h2 font-bold">CONSENSO INFORMATO</div>
                <p class="max-w-4xl text-left pt-4">
                    In questa schermata vogliamo spiegarti perché riteniamo che tu possa prendere parte a questo studio e che cosa dovrai fare se tu decidessi di partecipare. Ti preghiamo di leggere attentamente quanto riportato di seguito e di decidere in assoluta libertà se partecipare a questo studio.
                </p>

                <div class="mt-8 h2 font-bold">Qual è lo scopo di questo studio?</div>
                <p class="max-w-4xl text-left pt-4">
                    La presente ricerca ha l'obiettivo di indagare come gli utenti con diversi livelli di esperienza di cybersecurity e utilizzo di dispositivi IoT ragionano ad alto livello per definire comportamenti atti a difendere uno smart environment, ad esempio una casa con dispositivi intelligenti.
                </p>

                <div class="mt-8 h2 font-bold">La partecipazione allo studio è obbligatoria?</div>
                <p class="max-w-4xl text-left pt-4">
                    La tua partecipazione è completamente libera. Inoltre, se in un qualsiasi momento, tu dovessi cambiare idea e volessi ritirarsi dalla procedura, sei liber* di farlo.
                </p>

                <div class="mt-8 h2 font-bold">Cosa succederà se dovessi decidere di partecipare allo studio?</div>
                <p class="max-w-4xl text-left pt-4">
                    La procedura richiede che tu risponda a delle domande su un questionario online. La durata complessiva di compilazione del questionario è di X minuti circa.
                </p>

                <div class="mt-8 h2 font-bold">Quali sono i tuoi diritti?</div>
                <p class="max-w-4xl text-left pt-4">
                    Come ti abbiamo già detto, la tua partecipazione a questo studio è del tutto libera.
                    Se lo vorrai, tu potrai ritirarti dallo studio in qualsiasi momento senza dover dare alcuna spiegazione.
                    Questo modulo serve a garantire che tu abbia ricevuto un'informazione completa e che abbia dato liberamente il tuo consenso a partecipare allo studio.
                    Se dovessi decidere di partecipare allo studio, potrai contattare il Responsabile dello studio per qualsiasi informazione.
                </p>
                <div class="text-center mt-10">
                    <div class="w-full">
                        <div>
                            <button onclick="window.location.replace('{{ route('skills') }}')"
                                    class="py-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl"> Accetta le condizioni ed inizia il test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
