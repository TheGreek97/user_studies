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
                                Introduzione: Regole <br>Evento-Condizione-Azione (ECA)</h1>
                        </div>
                        <p>
                            Per specificare comportamenti personalizzati, le cosiddette regole "ECA" (Event Condition Action) sono le più utilizzate all'interno di software per la domotica, data la loro espressività e al contempo semplicità per gli utenti.&nbsp;
                        </p>
                        <div class="mt-3">Una regola ECA, come il nome suggerisce, è composta da&nbsp;<b>3 parti</b>:</div>
                        <div>
                            <ol>
                                <li>&#x2022; <b>EVENTO </b>- che cosa deve succedere per far attivare la regola?</li>
                                <li>&#x2022; <b>CONDIZIONE </b>- in che stato si deve trovare il sistema affinché la regola si attivi?</li>
                                <li>&#x2022; <b>AZIONE </b>- cosa succederà in seguito all'attivazione della regola?</li>
                            </ol>
                        </div>
                        <div class="mt-3">Possiamo differenziare tra EVENTO e CONDIZIONE semplicemente pensando all'aspetto del <i><b>tempo</b>:</i></div>
                        <div>
                            <ul>
                                <li>- Un EVENTO si verifica in uno specifico momento, <br>es.:&nbsp;<i>Comincia a piovere, </i>oppure&nbsp;<i>Suonano al campanello</i></li>
                                <li>- Una CONDIZIONE è uno stato dell'ambiente che persiste per un certo periodo di tempo, <br>es.: <i>Sta piovendo, </i>oppure&nbsp;<i>Qualcuno è alla porta</i></li>
                            </ul>
                        </div>
                        <br>
                        <div class="mt-3">
                            <div>Una regola ECA può essere scritta sostanzialmente come:&nbsp;</div>
                            <div><i>"SE succede qualcosa (E una specifica condizione è vera), ALLORA fai qualcosa"</i></div>
                        </div>
                        <div><i><br></i></div>
                        <div>Per capire meglio, prendiamo un esempio di regola ECA per evitare sprechi di elettricità in casa:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>esco dalla cucina</i></li>
                                <li><b>E </b><i>non è rimasto nessun altro in cucina</i></li>
                                <li><b>ALLORA </b><i>spegni la luce della cucina&nbsp;</i>&nbsp;</li>
                            </ol>
                        </div>
                        <div class="mt-3">Possiamo anche pensare a regole che non comprendano il punto 2, ovvero si attivano sempre in seguito a uno specifico evento, incondizionatamente:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>ho una emergenza medica</i></li>
                                <li><b>ALLORA </b><i>chiama i soccorsi</i></li>
                            </ol>
                            <div><br></div>
                        </div>
                        <div class="mt-3">Infine, possiamo anche voler definire comportamenti più complessi nella nostra smart home, comprendenti <u><i>più </i>condizioni</u> e/o<u> <i>più </i>azioni</u>&nbsp;come ad esempio un sistema per aprire le finestre automaticamente al nostro risveglio:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>mi sveglio</i></li>
                                <li> <b>E </b><i>è mattina, </i>e<i> non sta piovendo, </i>e<i> la temperatura è maggiore a 20°C</i></li>
                                <li> <b>ALLORA </b><i>apri le finestre della camera, </i>e<i> alza la tapparella della camera al 75%</i></li>
                            </ol>
                            <div><br></div>
                        </div>
                    </div>
                    <!-- SECTION 1 -->
                    <div x-show="step === 1" id="section-1" class="text-xl text-left pt-3">
                        <div><p class="w-full text-center">1 su <span class="num_slides"></span></p></div>
                        <div>
                            <p class="pt-3">
                               Ora ti chiediamo di immedesimarti in Alice, una programmatrice informatica con la passione per la domotica.&nbsp;
                            </p>
                            <div>
                                Alice lavora per un'importante azienda informatica e, perciò, rischia giornalmente di essere vittima di attacchi informatici da parte di eventuali criminali con l'intento di danneggiare la sua azienda.
                                <div>Se da un lato i dispositivi intelligenti all'interno di casa sua le permettono di avere più comodità, dall'altro la espongono maggiormente ad attacchi.&nbsp;</div>
                                <div>Perciò, vuole mettere in sicurezza la sua casa da possibili attacchi informatici ed acquista <i class="font-bold">Intrusion Defender</i>,
                                    un <span>dispositivo di sicurezza in grado di monitorare la rete di casa e proteggerla da minacce esterne.&nbsp;</span></div>
                                <div><i>Intrusion&nbsp;</i><i>Defender </i>è in grado di monitorare la rete al quale è collegato, ascoltando tutte le comunicazioni in entrata e in uscita da ciascun dispositivo. </div>
                            </div>
                            <div class="my-5">
                                In particolare è in grado di rilevare diversi tipi di attacchi ed eventi sospetti, come, ad esempio:
                                <ul>
                                    <li>&#x2022; Se un dispositivo è infetto da codice malevolo (es., virus, ransomware, etc.),</li>
                                    <li>&#x2022; Se qualcuno sta cercando di rendere inutilizzabile un dispositivo con un attacco DoS (Denial of Service),</li>
                                    <li>&#x2022; Se si verificano comunicazioni sospette all'interno della rete (es., presenza di connessione insolite),</li>
                                    <li>&#x2022; Se si verificano comunicazioni sospette provenienti dall'esterno della rete (es., scansione delle porte),</li>
                                    <li>&#x2022; Se c'è un tentativo di esfiltrazione di dati da uno dei dispositivi,</li>
                                    <li>&#x2022; Se c'è un tentativo di autenticazione o un'autenticazione anomala su un dispositivo.</li>
                                </ul>
                            </div>
                            <div class="my-5">In risposta ad eventuali attacchi rilevati, <i>Intrusion Defender</i> permette di assumere misure difensive come, ad esempio:
                                <ul>
                                    <li>&#x2022; Notificare l'utente del possibile pericolo tramite diversi canali (es., SMS, notifica su desktop, etc.),</li>
                                    <li>&#x2022; Chiedere all'utente di cambiare le credenziali di accesso ad un dispositivo,&nbsp;</li>
                                    <li>&#x2022; Gestire il traffico entrante e uscente, configurando un firewall a livello di rete (es., bloccare IP, chiudere porte del router, etc.),</li>
                                    <li>&#x2022; Cambiare parametri di configurazione specifici per ciascun dispositivo,</li>
                                    <li>&#x2022; Gestire backup dei dati sui dispositivi,</li>
                                    <li>&#x2022; Isolare o spegnere dispositivi della rete.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 2 -->
                    <div x-show="step === 2" id="section-2" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">2 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <p class="pb-3">Qualcuno, come un hacker, potrebbe scansionare la tua rete in cerca di punti di accesso (es. porte del router).
                                <br>Il dispositivo di sicurezza Intrusion Defender è in grado di riconoscere eventuali scansioni e può essere usato per mettere in sicurezza la rete (es. bloccando le porte del router non essenziali).
                                <br><span class="font-bold">Come vorresti configurare il sistema per proteggerti da un attacco del genere?</span>
                                <br>Prova a definire una regola ECA per proteggerti da eventuali attacchi simili. Sentiti libero di utilizzare il livello di dettaglio che ti sembra più adeguato.
                            </p>
                            <textarea rows="10" name="question_1" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 3 -->
                    <div x-show="step === 3" id="section-3">
                        <p class="text-2xl w-full text-center">3 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <p class="pb-3">Qualcuno (es., un hacker) potrebbe scansionare la tua rete in cerca di punti di accesso (es. porte del router).
                                <br>Il dispositivo di sicurezza Intrusion Defender è in grado di riconoscere eventuali scansioni e può essere usato per mettere in sicurezza la rete (es. bloccando le porte del router non essenziali).
                                <br><span class="font-bold">Come vorresti configurare il sistema per proteggerti da un attacco del genere?</span>
                                <br>Prova a definire una regola ECA per proteggerti da eventuali attacchi simili. Sentiti libero di utilizzare il livello di dettaglio che ti sembra più adeguato.
                            </p>
                            <textarea rows="10" name="explanation_feedback" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- SECTION 4 -->
                    <div x-show="step === 3" id="section-3">
                        <p class="text-2xl w-full text-center">3 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <p class="pb-3">Qualcuno (es., un hacker) potrebbe scansionare la tua rete in cerca di punti di accesso (es. porte del router).
                                <br>Il dispositivo di sicurezza Intrusion Defender è in grado di riconoscere eventuali scansioni e può essere usato per mettere in sicurezza la rete (es. bloccando le porte del router non essenziali).
                                <br><span class="font-bold">Come vorresti configurare il sistema per proteggerti da un attacco del genere?</span>
                                <br>Prova a definire una regola ECA per proteggerti da eventuali attacchi simili. Sentiti libero di utilizzare il livello di dettaglio che ti sembra più adeguato.
                            </p>
                            <textarea rows="10" name="explanation_feedback" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!-- Loading section -->
                    <div x-show="step === 6" class="pt-1">
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
                        <div x-show="step !== 6" class="flex flex-wrap w-full" :class="{'space-x-6' : step > 0}">
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

            let min_length = 20  // minimo 20 caratteri nelle risposte

            function load() {
                let last_section = 6
                $(".num_slides").each (function(){
                    $(this).html(last_section)
                })
                form_html.show();
                return {
                    step: 0,
                    previous(e) {
                        if (this.step > 0)
                            this.step--;

                        alert_modal.hide();

                        $(window).scrollTop(0);

                        if (this.step < 5) {
                            next_btn.text("Successivo");
                        }
                    },
                    next(e) {
                        let check = true;
                        let input_field = $('textarea[name=question_' + (this.step-1) + ']')[0]
                        if (input_field !== undefined) {
                            input_field.placeholder = `Scrivi qui... (almeno ${min_length} caratteri)`
                            let answer = input_field.value
                            console.log(answer)
                            if (answer === "" || (answer !== undefined && answer.length < min_length)) {
                                check = false;
                                input_field.classList.remove('border-gray-300');
                                input_field.classList.add('border-red-500');
                                input_field.onchange = () => {
                                    input_field.classList.remove('border-red-500');
                                    input_field.classList.add('border-gray-300');
                                };
                            } else check = (check && true)
                        }
                        if (!check) {
                            alert_modal.show();
                            alert_modal.fadeTo("fast", 1, function () {
                                setTimeout(function () {
                                    alert_modal.fadeTo("fast", 0, function () {
                                        alert_modal.hide();
                                    });
                                }, 10000);
                            });
                        }

                        if (this.step !== last_section && check) { //!==5
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
