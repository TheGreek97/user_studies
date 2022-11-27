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
                            Nelle cosiddette smart home ("case intelligenti") ci sono diversi dispositivi che comunicano fra loro per diversi scopi, quali aumentare la qualità della vita degli abitanti della casa, ridurre sprechi energetici, aumentare la sicurezza.
                        </p>
                        <p>
                            In una smart home, gli utenti devono avere la possibilità di definire il comportamento dei loro dispositivi (luci, videocamere, elettrodomestici, etc.), ad esempio per rispondere a comandi vocali (es., "Alexa, accendi la luce nel soggiorno") o per eseguire automaticamente specifiche azioni (es., chiudere le finestre in caso di pioggia).
                        </p>
                        <p>
                            Per specificare comportamenti personalizzati, le cosiddette regole "ECA" (Event-Condition-Action, ovvero "Evento-Condizione-Azione") sono le più utilizzate all'interno di software per la domotica, data la loro espressività e al contempo semplicità per gli utenti.
                        </p>
                        <div class="mt-3">Una regola ECA, come il nome suggerisce, è composta da&nbsp;<b>3 parti</b>:</div>
                        <div>
                            <ol>
                                <li>&#x2022; <b>EVENTO </b>- che cosa deve succedere per far attivare la regola?</li>
                                <li>&#x2022; <b>CONDIZIONE (o STATO) </b>- in che stato si deve trovare il sistema affinché la regola si attivi?</li>
                                <li>&#x2022; <b>AZIONE </b>- cosa succederà in seguito all'attivazione della regola?</li>
                            </ol>
                        </div>
                        <div class="mt-3">
                            <div>Una regola ECA può essere scritta sostanzialmente come:&nbsp;</div>
                            <div><i>"SE succede qualcosa (E una specifica condizione è vera), ALLORA fai qualcosa"</i></div>
                        </div>
                        <div class="mt-3">Possiamo differenziare tra EVENTO e CONDIZIONE semplicemente pensando all'aspetto del <i><b>tempo</b>:</i></div>
                        <div>
                            <ul>
                                <li>- Un EVENTO si verifica in uno specifico momento, <br>es.:&nbsp;<i>Comincia a piovere, </i>oppure&nbsp;<i>Smette di piovere</i></li>
                                <li>- Una CONDIZIONE persiste nel tempo, <br>es.: <i>Sta piovendo, </i>oppure&nbsp;<i>Non sta piovendo</i></li>
                            </ul>
                        </div>
                        <br>

                        <div><i><br></i></div>
                        <div>Per capire meglio, prendiamo un esempio di regola ECA per evitare sprechi di elettricità in casa:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>esco dalla cucina</i></li>
                                <li><b>E </b><i>non c'è nessuno in cucina</i></li>
                                <li><b>ALLORA </b><i>spegni la luce della cucina&nbsp;</i>&nbsp;</li>
                            </ol>
                        </div>
                        <div class="mt-3">Possiamo anche pensare a regole che non comprendano il punto 2, ovvero si attivano sempre in seguito a uno specifico evento, incondizionatamente:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>scoppia un incendio</i></li>
                                <li><b>ALLORA </b><i>attiva l'impianto antincendio</i></li>
                            </ol>
                            <div><br></div>
                        </div>
                        <div class="mt-3">Infine, possiamo anche voler definire comportamenti più complessi nella nostra smart home, comprendenti <u><i>più </i>condizioni</u> e/o<u> <i>più </i>azioni</u>&nbsp;
                            come ad esempio un sistema per aprire le finestre automaticamente al nostro risveglio, accertandosi che ci siano le giuste condizioni per farlo:</div>
                        <div>
                            <ol class="text-center mt-2">
                                <li><b>SE </b><i>mi sveglio</i></li>
                                <li> <b>E </b><i>è mattina, </i>e<i> non sta piovendo, </i>e<i> la temperatura è maggiore a 20°C</i></li>
                                <li> <b>ALLORA </b><i>apri le finestre della camera, </i>e<i> alza la tapparella</i></li>
                            </ol>
                            <br>
                            <div class="mt-4">
                                Come vedi, le regole ECA possono davvero essere un potente strumento per esprimere comportamenti anche complessi in maniera semplice.
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 1 -->
                    <div x-show="step === 1" id="section-1" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">1 su <span class="num_slides"></span></p>
                        <div>
                            <p class="pt-4">
                                Immagina di essere Marco, un avvocato con la passione per la domotica. All'interno della sua casa, Marco ha diversi dispositivi intelligenti: diverse luci smart (presenti sia all'interno che all'esterno della casa), un televisore smart, un computer, un robot aspirapolvere, tre finestre automatiche, una lavatrice smart, e due telecamere di sicurezza per controllare l'ingresso della casa ed il suo studio. Marco ha uno smartphone, che utilizza sia per lavoro che per la sua vita privata ed uno smartwatch ad esso collegato, che può anche contare i suoi passi, monitorare il sonno ed il battito cardiaco, etc. Infine, possiede un router per la connessione ad Internet.
                            </p>
                            <img style="float: right; max-width: 50%;" src="{{url('assets/img/alexa.jpg')}}">
                            <div class="my-3">
                                Per comandare i suoi oggetti smart, Marco utilizza <b>Alexa</b>, il famoso assistente vocale di Amazon che gli permette, ad esempio, di spegnere e accendere le luci, regolarne l'intensità, programmare i lavaggi della sua lavatrice, visualizzare i video delle telecamere di sicurezza, e molto altro.
                            </div>
                            <div class="my-3">
                                Recentemente, Marco ha sentito che Alexa permetterà, in un prossimo aggiornamento, di definire vocalmente regole Evento-Condizione-Azione per gestire il comportamento degli oggetti intelligenti all'interno della casa. Essendo appassionato di domotica, Marco trova la notizia entusiasmante, dato che sarà in grado di definire comportamenti personalizzati del tipo:
                            </div>
                            <div class="my-3">
                                <i>"Alexa, SE piove ALLORA chiudi la finestra della cucina"</i>
                            </div>
                            <div class="mt-5">
                                Sentendo parlare sempre più spesso di attacchi informatici, Marco decide di voler aumentare la sicurezza del suo impianto domotico; d'altronde, essendo un avvocato, ritiene di essere una figura ancora più suscettibile ad attacchi mirati rispetto ad altri.<br>
                                Per proteggersi da attacchi informatici, Marco installa un'estensione di Alexa che permette di controllare tutte le attività che si verificano sulla rete di casa. Con questo programma, Alexa è in grado di controllare le connessioni in entrata e in uscita da ciascun dispositivo.
                            </div>
                            <div class="my-5">
                                In particolare è in grado di rilevare diversi tipi di attacchi ed eventi sospetti, come, ad esempio:
                                <ol style="list-style: decimal; margin-left:1%; padding: 20px;">
                                    <li>Qualcuno cerca di sovraccaricare un dispositivo; questo tipo di attacco (chiamato "attacco DoS") ha lo scopo di rendere il dispositivo temporaneamente inutilizzabile, sovraccaricandolo di richieste Internet;</li>
                                    <li>Un virus infetta un dispositivo; un virus è un programma malevolo che può compromettere sia il funzionamento di un dispositivo, che la privacy dell'utente (ad es., può rubare file e password). </li>
                                    <li>Qualcuno cerca di rubare dati privati da un dispositivo; un furto di dati può portare alla diffusione di dati sensibili dell'utente (come credenziali o file privati), video di sorveglianza (provenienti da una telecamera di sicurezza), e persino la mappa di casa (se il bersaglio è il robot aspirapolvere).</li>
                                    <li>Un hacker entra in maniera illecita nella rete o in un dispositivo; un utente non autorizzato può infatti provare ad accedere ad un dispositivo con lo scopo di danneggiarlo o rubare dati. </li>
                                    <li>Ci sono possibili minacce provenienti dall'esterno della rete; questo può indicare tentativi di attacco o di accesso alla rete, come ad esempio una scansione della rete in cerca di vulnerabilità e punti di accesso.</li>
                                    <li>Si verificano connessioni sospette all'interno della rete, oppure attività sospetta (es., ad orari insoliti).</li>
                                </ol>
                            </div>
                            <div class="my-5">Oltre a rilevare questi eventuali attacchi, Alexa ora può anche permetterti di proteggerti da tali attacchi in diverse maniere:
                                <ul>
                                    <li>&#x2022; Mandarti notifiche del possibile pericolo (sullo smartphone, SMS, e-mail, etc.);</li>
                                    <li>&#x2022; Chiederti di cambiare le credenziali per accedere ad un dispositivo;</li>
                                    <li>&#x2022; Bloccare eventuali connessioni sospette, chiudere i punti di accesso del router non essenziali;</li>
                                    <li>&#x2022; Creare copie sicure dei dati di un dispositivo;</li>
                                    <li>&#x2022; Isolare o spegnere del tutto un dispositivo.</li>
                                </ul>
                            </div>
                            <div>
                                Ora ti porremo delle domande a cui ti chiediamo di rispondere in maniera creativa. Ci teniamo a ricordarti che non esistono risposte giuste o sbagliate, perciò rispondi semplicemente nella maniera che ti sembra più sensata!
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 2 -->
                    <div x-show="step === 2" id="section-2" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">2 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attacchi <i>DoS</i></div>
                            <p class="pb-2">
                                Alexa è in grado di riconoscere attacchi che possono limitare il funzionamento di un dispositivo.<br>
                                Un criminale, infatti, può lanciare un attacco del genere (in gergo chiamato "DoS") per intasare un dispositivo con dati inutili e causarne un blocco temporaneo, rendendolo inutilizzabile.<br>
                                Per proteggersi, si può intervenire, ad esempio, isolando il dispositivo, bloccandone le comunicazioni in entrata.<br>
                                Una telecamera di sicurezza in casa di Marco potrebbe essere un bersaglio per malintenzionati con l'intento di violare il perimetro della casa.<br>
                                - Come completeresti questa regola ECA per proteggere le telecamere di sicurezza da attacchi simili?
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE rilevi un attacco alle telecamere di sicurezza [...]"</span>
                            </div>
                            <textarea rows="10" name="question_1" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
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
                    <div x-show="step === 3" id="section-3" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">3 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Protezione da Virus</div>
                            <p class="pb-2">
                                Un hacker potrebbe voler inserire del codice malevolo, come un virus, su un dispositivo nella smart home di Marco. Alexa è in grado di riconoscere eventuale codice malevolo all'interno della rete e può mettere in sicurezza i suoi dispositivi, ad esempio, mettendo in quarantena i file infetti.
                                <br>
                                Cosa potrebbe dire Marco ad Alexa per proteggere la sua smart TV da virus? Completa la seguente regola ECA:
                            </p>
                            <div class="my-4 text-center font-bold text-xl">
                                <span>"Alexa, SE [...] ALLORA metti in quarantena i file sulla smart TV e fai una copia di backup dei dati"</span>
                            </div>
                            <textarea rows="10" name="question_2" placeholder="Scrivi qui... (almeno 20 caratteri)"
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                <span>- Cerca di motivare la tua risposta precedente, spiegando il tuo ragionamento:</span>
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
                    <!-- SECTION 4 -->
                    <div x-show="step === 4" id="section-4" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">4 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Furti di dati</div>
                            <p class="pb-2">
                                Alexa può analizzare le comunicazioni nella rete e capire se ci sono possibili furti di dati. Ad esempio, si può accorgere che le telecamere di sicurezza stanno inviando i loro dati verso un dispositivo sconosciuto; questo significherebbe che qualcuno di non autorizzato riesce a visualizzare i video di sorveglianza delle telecamere di Marco.
                                <br>
                                Un altro dispositivo che un hacker potrebbe attaccare è il robot aspirapolvere: infatti, questo dispositivo raccoglie dati che possono essere utilizzati per ricostruire una mappa della casa di Marco ed eventualmente aiutare un ladro a pianificare furti con scasso.
                                <br>
                                - Come completeresti la seguente regola ECA per proteggere il robot aspirapolvere da furti di dati?
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
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_3_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 5 -->
                    <div x-show="step === 5" id="section-5" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">5 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Accessi illeciti</div>
                            <p class="pb-2">
                                Un malintenzionato potrebbe tentare di entrare in uno dei dispositivi smart di Marco provando ad accedervi provando svariate combinazioni di username e password. Alexa può rilevare questi tentativi di accesso illeciti ed impedire che un hacker riesca a trovare la combinazione giusta di username e password per accedere ai dati di un dispositivo.
                                <br/>Un modo per proteggersi da un attacco simile potrebbe essere bloccare i tentativi di autenticazione dopo che un utente sbaglia le credenziali tante volte di fila.
                                <br/>- Cosa potrebbe dire Marco ad Alexa per proteggersi da attacchi del genere? Prova a definire una regola ECA (o anche più, se lo ritieni necessario):
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
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_4_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 6 -->
                    <div x-show="step === 6" id="section-6" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">6 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Scansione della rete</div>
                            <p class="pb-2">
                                 Il router di casa garantisce a Marco una connessione ad Internet, ma potrebbe sfortunatamente avere qualche vulnerabilità dovuta ad una configurazione non corretta. Qualcuno, probabilmente un hacker, potrebbe scansionare la rete di casa in cerca di punti di accesso.
                                <br/>Alexa è in grado di riconoscere scansioni di questo tipo e può mettere in sicurezza la rete di Marco chiudendo, ad esempio, i punti di accesso del router non essenziali per l'accesso ad Internet.
                                <br/>- Cosa può dire Marco ad Alexa per proteggersi da attacchi del genere? Prova a definire una regola ECA (o anche più, se lo ritieni necessario):
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
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
                            </p>
                            <textarea rows="3" name="question_5_alt" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 7 -->
                    <div x-show="step === 7" id="section-7" class="text-xl text-left pt-3">
                        <p class="text-2xl w-full text-center">7 su <span class="num_slides"></span></p>
                        <div class="my-5">
                            <div class="font-bold mb-5">Attività insolite</div>
                            <p class="pb-2">
                                Marco è solito andare a letto entro mezzanotte, e quindi non controlla mai i suoi dispositivi dalle 00:00 alle 07:30, orario in cui si sveglia abitualmente. Alexa è in grado di riconoscere attività insolite sui dispositivi di casa: ad esempio, un accesso alle finestre per aprirle in piena notte, mentre Marco dorme (e per giunta in inverno!), può essere considerata un'attività sospetta e in quanto tale potenzialmente pericolosa.
                                <br/>- Cosa può dire Marco ad Alexa per proteggersi da attacchi del genere? Prova a definire una regola ECA (o anche più, se lo ritieni necessario):
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
                                <span>- (Opzionale) Riguardo questo attacco, ti vengono in mente altri modi per dire ad Alexa come proteggere la casa da attacchi simili? (ad esempio, regole con azioni diverse, più di una regola, etc.)
                                    Motiva eventualmente la tua risposta.
                                </span>
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
