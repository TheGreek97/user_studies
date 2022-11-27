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
                    <!-- SECTION 1 -->
                    <div x-show="step === 0" id="section-0" class="text-xl text-left pt-3">
                        <div>
                            <p class="pt-4">
                                Data la tua conoscenza in ambito di sicurezza informatica, vorremmo porti qualche altra domanda un po' più tecnica. Se pensi di non saper rispondere ad una domanda, lasciala pure in bianco, oppure scrivi "Non so".
                            </p>
                            <img style="float: right; max-width: 50%;" src="{{url('assets/img/ids.webp')}}">
                            <div class="my-3">
                                Per implementare i comportamenti di sicurezza descritti nella scorsa sezione, è necessario collegare ad Alexa un dispositivo di protezione in grado di monitorare la rete chiamato IDS (Intrusion Defender System).
                            </div>

                            <div class="my-5">
                                Come suggerito dalla sezione precedente, questo dispositivo è in grado di monitorare la rete al quale è collegato, ascoltando tutte le comunicazioni in entrata e in uscita da ciascun dispositivo. Entrando più nello specifico, è in grado di rilevare diversi tipi di attacchi ed eventi sospetti, come, ad esempio:
                                <ol style="list-style: decimal; margin-left:1%; padding: 20px;">
                                    <li>Se qualcuno sta cercando di rendere inutilizzabile un dispositivo con un attacco DoS (Denial of Service)
                                    <li>Se un dispositivo è infetto da codice malevolo (es., virus, ransomware, etc.),
                                    <li>Se c'è un tentativo di furto di dati da uno dei dispositivi,
                                    <li>Se c'è un tentativo di autenticazione o un'autenticazione anomala su un dispositivo,
                                    <li>Se si verificano comunicazioni sospette all'esterno della rete (es., scansione delle porte del router),
                                    <li>Se si verificano comunicazioni sospette all'interno della rete (es., presenza di connessione insolite).</li>
                                </ol>
                            </div>
                            <div class="my-5">In risposta ad eventuali attacchi rilevati, Alexa può assumere misure difensive come:
                                <ul>
                                    <li>&#x2022; Gestire il traffico entrante e uscente, configurando un firewall a livello di rete (es., bloccare connessioni sospette, chiudere porte del router, etc.),</li>
                                    <li>&#x2022; Notificare l'utente del possibile pericolo tramite diversi canali (es., SMS, notifica su desktop, etc.),</li>
                                    <li>&#x2022; Cambiare parametri di configurazione specifici per ciascun dispositivo,</li>
                                    <li>&#x2022; Chiedere all'utente di cambiare le credenziali di accesso ad un dispositivo, </li>
                                    <li>&#x2022; Gestire backup dei dati sui dispositivi,</li>
                                    <li>&#x2022; Isolare o spegnere dispositivi della rete.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 2 -->
                    <div x-show="step === 1" id="section-1" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">1 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5"> <i>Data Exfiltration</i></div>
                            <p class="pb-2">
                                Alexa, tramite l'IDS può rilevare traffico sospetto entrante o uscente dalla rete o da un dispositivo in particolare. Questo potrebbe essere sintomo di attività in background che ruba i dati da un dispositivo (data exfiltration).<br>
                                Questo può avvenire osservando traffico insolito, ad esempio che avviene ad orari sospetti, da/verso località sospette, che riguarda grosse quantità di dati, etc. <br/>
                                Un dispositivo che un hacker potrebbe attaccare è lo smartwatch, in quanto carico di dati sensibili, che possono fornire informazioni molto private, come sapere quando chi lo indossa sta dormendo oppure no. <br/>
                                - Come completeresti la seguente regola ECA per proteggere lo smartwatch da furti di dati?
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA isola lo smartwatch e mandami una notifica sullo smartwatch."</span>
                            </div>
                            <textarea rows="10" name="question_1" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Motiva la tua risposta precedente, cercando di spiegare il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_1_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_1_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 3 -->
                    <div x-show="step === 2" id="section-2" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">2 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attività sospetta su una porta</div>
                            <p class="pb-2">
                                Alexa è in grado di riconoscere attività sospetta sulla rete. Ad esempio, un criminale potrebbe cercare di eseguire attacchi sfruttando una porta del router aperta, come la porta 22, utilizzata comunemente per il servizio SSH.<br>
                                Per proteggersi da un attacco del genere si può intervenire, ad esempio, chiudendo la porta 22 ed impostando una porta non standard (la 2222) per il servizio SSH.<br/>
                                - Come completeresti questa regola ECA per proteggerti da un attacco del genere?
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] <br/> ALLORA chiudi la porta 22 del router, e imposta la porta 2222 per il servizio SSH"</span>
                            </div>
                            <textarea rows="10" name="question_2" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Motiva la tua risposta precedente, cercando di spiegare il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_2_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_2_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 3 -->
                    <div x-show="step === 3" id="section-3" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">3 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Autenticazione illecita/Priviledge escalation</div>
                            <p class="pb-2">
                                Un malintenzionato potrebbe tentare di autenticarsi ed entrare in uno dei tuoi dispositivi tramite utilizzo di attacchi a forza bruta, oppure tramite priviledge escalation. Alexa può rilevare tentativi di accesso illeciti, oppure attività di autenticazione sospetta (es., rilevando connessioni da IP sconosciuti); in particolare, può riconoscere attività di login come amministratore. <br/>
                                Per proteggersi da un attacco del genere si può, ad esempio, cambiare la password di accesso ad un dispositivo. <br>
                                - Cosa diresti al sistema per proteggerti da attacchi del genere?
                                <br/> Prova a definire una o più regole ECA per proteggerti da attacchi simili. Sentiti libero di utilizzare il livello di dettaglio che ti sembra più efficace.
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, [...]"</span>
                            </div>
                            <textarea rows="10" name="question_3" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Motiva la tua risposta precedente, cercando di spiegare il tuo ragionamento:</span>
                            </p>
                            <textarea rows="3" name="question_3_rationale" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_3_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Loading section -->
                    <div x-show="step === 4" class="pt-1">
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
                        <div x-show="step !== 4" class="flex flex-wrap w-full" :class="{'space-x-6' : step > 0}">
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

            /*window.onbeforeunload = function(event)
            {
                return confirm("Confirm refresh");
            };
            */
            function load() {
                let last_section = 4
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

                        if (this.step !== last_section) {
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
