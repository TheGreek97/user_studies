<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div id="alert"
                 class="p-4 mb-4 text-sm text-red-700 bg-red-100 border-red-300 border rounded-lg absolute top-5 z-50 shadow-xl"
                 style="display: none; opacity: 0;" role="alert">
                <span class="font-medium">Per favore, rispondi a tutte le domande.</span>
            </div>
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl z-20l" style="max-width: 90%">
                <form action="{{ Request::url() }}" method="POST" x-data="load()" id="form" style="display:none;">
                @csrf
                <!--- SECTION 0 --->
                    <div x-show="step === 0" id="section-0" class="text-xl max-w-2xl text-left pt-3">
                        <div>
                            <h1 class="text-3xl font-bold text-center mb-4 cursor-pointer">
                                Conoscenze preliminari per lo studio</h1>
                        </div>
                        <p>
                            Nelle case intelligenti oggigiorno possono essere installati dispositivi intelligenti capaci di migliorare la qualità della vita degli abitanti della casa, ridurre sprechi energetici, aumentare la sicurezza. Esempi di dispositivi intelligenti sono luci intelligenti, tapparelle intelligenti, robot aspirapolvere, termostati intelligenti, telecamere per la video sorveglianza.
                        </p>
                        <p>
                            Tali dispositivi sono anche controllabili attraverso assistenti vocali come Amazon Alexa, per esempio, per accedere le luci, senza usare gli interruttori, gli utenti possono dire "Alexa, accendi luce soggiorno".
                        </p>
                        <p>
                            Nuove funzionalità di Alexa presto consentiranno di definire il comportamento TRA oggetti intelligenti cosicché la loro attivazione possa dipendere non da un comando di un utente ma dallo stato di un altro oggetto. Per esempio, un utente potrebbe volere chiudere le tapparelle quando il sensore pioggia rileva la pioggia, ad esempio dicendo "Alexa, chiudi tutte le tapparella se inizia a piovere". In altre parole, per specificare comportamenti tra oggetti, gli utenti potrebbero definire le  cosiddette regole "ECA" ("Evento-Condizione-Azione").
                        </p>
                        <div class="mt-3">Una regola ECA è composta da&nbsp;<b>3 parti</b>:</div>
                        <div>
                            <ol>
                                <li>&#x2022; <b>EVENTO </b>- che cosa deve succedere per far attivare la regola? (es., inizia a piovere)</li>
                                <li>&#x2022; <b>CONDIZIONE </b>- condizione (opzionale) per dettagliare la rilevazione di un evento (es., pioggia forte)</li>
                                <li>&#x2022; <b>AZIONE </b>- cosa succederà in seguito all'attivazione della regola? (es., chiudi tapparelle)</li>
                            </ol>
                        </div>

                        <div class="mt-3">
                            D'altro canto, possiamo anche voler definire comportamenti più complessi nella nostra smart home, comprendenti più condizioni e/o più azioni come ad esempio un sistema per aprire le finestre automaticamente al nostro risveglio:
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>inizia a piovere a condizione forte o in maniera abbondante</i></li>
                                <li> <b>ALLORA </b><i>chiudi le tapparelle e spegni la lavatrice</i></li>
                            </ol>
                            <br>
                        </div>
                    </div>
                    <!-- SECTION 1 -->
                    <div x-show="step === 1" id="section-1" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">1 su <span class="num_slides"></span></p>
                        <div>
                            <p class="pt-4">
                                Ora ti chiediamo di metterti nei panni di Andrea, avvocato con la passione per la domotica. All'interno della sua casa, Andrea ha diversi dispositivi intelligenti: 10 luci smart (presenti sia all'interno che all'esterno della casa), un televisore smart, un computer, un robot aspirapolvere, 3 finestre motorizzate, 1 lavatrice smart, e 2 telecamere di sicurezza per controllare l'ingresso della casa ed il suo studio. Andrea ha uno smartphone che utilizza sia per lavoro che per la sua vita privata ed uno smartwatch ad esso collegato, che può anche contare i suoi passi, monitorare il sonno ed il battito cardiaco, etc..
                            </p>
                            <img style="float: right; max-width: 50%;" src="{{url('assets/img/alexa.jpg')}}">
                            <div class="my-3">
                                Per comandare i suoi oggetti smart Andrea usa Alexa, ad esempio dicendo "Alexa, accendi luce soggiorno".
                            </div>
                            <div class="my-3">
                                Recentemente, Andrea ha sentito parlare delle ultime due novità che Alexa ha introdotto. La prima riguarda la possibilità di definire vocalmente regole Evento-Condizione-Azione per gestire il comportamento tra gli oggetti intelligenti della casa, ad esempio:
                                <i>"Alexa, SE piove ALLORA chiudi le tapparelle".</i>
                            </div>
                            <div class="mt-5">
                                La seconda novità riguarda una protezione offerta da Alexa a tutti i dispositivi connessi alla rete di casa: Alexa ora monitora la connessione di tutti i dispositivi collegati al router di casa per rilevare attacchi contro di essi. Esempi di attacchi che Alexa è in grado di rilevare sono:
                            </div>
                            <div class="my-3">
                                <ol style="list-style: decimal; margin-left:1%; padding: 20px;">
                                    <li>Qualcuno cerca di sovraccaricare un dispositivo con richieste dall'esterno per renderlo temporaneamente inutilizzabile;</li>
                                    <li>Un malware è stato caricato su un dispositivo; </li>
                                    <li>Qualcuno cerca di rubare dati privati da un dispositivo (p.e., le registrazioni delle telecamere di video sorveglianza); </li>
                                    <li>Un hacker prende il controllo di un dispositivo con lo scopo di danneggiarlo o rubare dati.</li>
                                </ol>
                            </div>
                            <div class="my-5"> Oltre a rilevare questi attacchi, Alexa ti consente anche di proteggere i tuoi dispositivi intelligenti in diversi modi:
                                <ul>
                                    <li>&#x2022; Inviando notifiche del possibile pericolo (sullo smartphone, SMS, e-mail, etc.);</li>
                                    <li>&#x2022; Chiederti di cambiare le credenziali per accedere ad un dispositivo;</li>
                                    <li>&#x2022; Bloccare eventuali connessioni sospette, chiudere i punti di accesso del router non essenziali;</li>
                                    <li>&#x2022; Creare copie sicure dei dati di un dispositivo;</li>
                                    <li>&#x2022; Isolare o spegnere del tutto un dispositivo.</li>
                                </ul>
                            </div>
                            <div>
                                Ora ti chiediamo di rispondere ad alcune domande. Ci teniamo a ricordarti che non esistono risposte giuste o sbagliate, perciò rispondi semplicemente nella maniera che ti sembra più sensata.
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 2 -->
                    <div x-show="step === 2" id="section-2" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">2 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacco n. 1</div>
                            <p class="pb-2">
                                Una telecamera di sicurezza in casa di Andrea potrebbe essere un bersaglio per malintenzionati.
                                <br/><br/>
                                - Completa la seguente regola definendo l'azione che secondo te deve essere eseguita per difendersi da questo attacco.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE rilevi un attacco alle telecamere di sicurezza ALLORA [...]"</span>
                            </div>
                            <textarea rows="10" name="question_1" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_1_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                    Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_1_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 3 -->
                    <div x-show="step === 3" id="section-3" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">3 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacco n. 2</div>
                            <p class="pb-2">
                                La smart TV che Andrea ha in soggiorno potrebbe essere infettata da un virus.
                                <br>
                                - Completa la seguente regola definendo l'evento che secondo te deve essere rilevato per difendersi da questo attacco.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA metti "in quarantena" i file sulla smart TV e fai una copia di backup dei dati"</span>
                            </div>
                            <textarea rows="10" name="question_2" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_2_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_2_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 4 -->
                    <div x-show="step === 4" id="section-4" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">4 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacco n. 3</div>
                            <p class="pb-2">
                                Il robot aspirapolvere di Andrea potrebbe essere attaccato con lo scopo di rubare dati e, ad esempio, ricostruire una mappa della casa.
                                <br>
                                - Completa la seguente regola definendo l'azione che secondo te deve essere eseguita per difenderti da questo attacco.
                                <br>
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE rilevi un furto di dati dal robot aspirapolvere, ALLORA [...]"</span>
                            </div>
                            <textarea rows="10" name="question_3" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_3_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_3_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 5 -->
                    <div x-show="step === 5" id="section-5" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">5 su <span class="num_slides"></span></p>
                        <div class="my-5">
                                <div class="font-bold mb-5">Attacco n. 4</div>
                            <p class="pb-2">
                                Un malintenzionato potrebbe tentare di entrare in uno dei dispositivi smart di Andrea provando ad accedervi con svariate combinazioni di username e password.

                                <br/>- Prova a definire una regola ECA (o più) per proteggerti da questo attacco.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA [...]"</span>
                            </div>
                            <textarea rows="10" name="question_4" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_4_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_4_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 6 -->
                    <div x-show="step === 6" id="section-6" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">6 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacco n. 5</div>
                            <p class="pb-2">
                                Il router di Andrea potrebbe essere scansionato da un hacker in cerca di vulnerabilità e punti di accesso alla rete di casa.
                                <br/>- Prova a definire una regola ECA (o più) per proteggerti da questo attacco.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA [...]"</span>
                            </div>
                            <textarea rows="10" name="question_5" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_5_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_5_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 7 -->
                    <div x-show="step === 7" id="section-7" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">7 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacco n. 6</div>
                            <p class="pb-2">
                                Alexa è in grado di riconoscere attività insolite o sospette sui dispositivi di casa, come, ad esempio, l'apertura delle finestre in piena notte, mentre Andrea dorme.
                                <br/>- Prova a definire una regola ECA (o più) per proteggerti da questo attacco.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA [...]"</span>
                            </div>
                            <textarea rows="10" name="question_6" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_6_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                - (Opzionale) Riguardo l'attacco precedente, ti vengono in mente altri modi per dire ad Alexa come proteggerti da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.). <br>
                                Eventualmente, motiva la tua risposta.
                            </p>
                            <textarea rows="3" name="question_6_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Loading section -->
                    <div x-show="step === 8" class="pt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-12 h-12 mx-auto stroke-green-500 animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <div class="text-center text-green-500 font-bold my-3 text-xl w-full">
                            Caricamento
                        </div>
                    </div>
                    <div class="text-center mt-14 mb-10">
                        <div x-show="step !== 8" class="flex flex-wrap w-full" :class="{'space-x-6' : step > 0}">
                            <div x-show="step > 0">
                                <button type="button" id="back" @click="previous()"
                                        class="shrink py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                    Indietro
                                </button>
                            </div>
                            <div x-show="step > 0" class="flex-1"></div>
                            <div :class="{'flex-1 w-full': step === 0}">
                                <button type="button" id="next" @click="next()"
                                        :class="{'flex-1 w-full' : step === 0, 'flex-initial w-64' : step > 0}"
                                        class=" py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Avanti
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            let alert_modal = $("#alert")
            let form_html = $("#form")
            let next_btn = $("#next")

            function load() {
                let last_section = 8
                $(".num_slides").each (function(){
                    $(this).html(last_section-1)
                })
                form_html.show();
                return {
                    step: 0,
                    previous(e) {
                        if (this.step > 0)
                            this.step--;

                        alert_modal.hide();

                        $(window).scrollTop(0);

                        if (this.step < last_section-1) {
                            next_btn.text("Successivo");
                        }
                    },
                    next(e) {
                        let check_input = function (field) {
                            let min_length = 20  // minimo 20 caratteri nelle risposte
                            let check = true
                            if (field !== undefined) {
                                field.placeholder = `Scrivi qui... (almeno ${min_length} caratteri)`
                                let answer = field.value
                                if (answer === "" || (answer !== undefined && answer.length < min_length)) {
                                    check = false;
                                    field.classList.remove('border-gray-300');
                                    field.classList.add('border-red-500');
                                    field.onchange = () => {
                                        field.classList.remove('border-red-500');
                                        field.classList.add('border-gray-300');
                                    }
                                } else {
                                    check = (check && true)
                                }
                            }
                            return check
                        }

                        let input_field = $('textarea[name=question_' + (this.step-1) + ']')[0]
                        let input_field_rationale = $('textarea[name=question_' + (this.step-1) + '_rationale]')[0]

                        let check = check_input(input_field) & check_input(input_field_rationale)
                        if (! check) {
                            alert_modal.show();
                            alert_modal.fadeTo("fast", 1, function () {
                                setTimeout(function () {
                                    alert_modal.fadeTo("fast", 0, function () {
                                        alert_modal.hide();
                                    });
                                }, 10000);
                            });
                        }

                        if (this.step !== last_section && check) {
                            this.step++;
                            alert_modal.hide();
                        }

                        $(window).scrollTop(0);

                        if (this.step === last_section-1) {
                            next_btn.text("Sezione successiva");
                        }

                        if (this.step === last_section) {
                            form_html.submit();
                        }
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
