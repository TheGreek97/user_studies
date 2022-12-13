<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div id="alert"
                 class="p-4 mb-4 text-sm text-red-700 bg-red-100 border-red-300 border rounded-lg absolute top-5 z-50 shadow-xl"
                 style="display: none; opacity: 0;" role="alert">
                <span class="font-medium">Per favore, rispondi a tutte le domande.</span>
            </div>
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <form action="{{ Request::url() }}" method="POST" x-data="load()" id="form" style="display:none;">
                    @csrf

                    <div>
                        <h1 x-show="step !== 0 && step !== 6"
                            class="text-3xl font-bold text-center mb-4 cursor-pointer">Livello di conoscenze informatiche e sicurezza</h1>
                    </div>

                    <!-- SECTION 1 -->
                    <div x-show="step === 1" id="section-1">
                        <p class="text-2xl w-full text-center">&nbsp;</p>
                        <div class="my-5">
                            <p class="font-bold pb-1">In una scala da 1 a 5, come definiresti il tuo livello di conoscenze in Informatica?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Scarso)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Esperto)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="level_informatics" value="1" min="1" max="5"
                                       step="1"
                                       name="level_informatics" class="w-full">
                                <datalist id="level_informatics">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-5">
                                <p class="font-bold pb-1">In una scala da 1 a 5, come definiresti il tuo livello di conoscenze in Sicurezza Informatica?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Scarso)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Esperto)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="level_cybersecurity" value="1" min="1" max="5"
                                       step="1"
                                       name="level_cybersecurity" class="w-full">
                                <datalist id="level_cybersecurity">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-5">
                            <p class="font-bold pb-1">In una scala da 1 a 5, quanto fai utilizzo di dispositivi smart all'interno di ambienti intelligenti?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Mai)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Abitualmente)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="level_iot" value="1" min="1" max="5"
                                       step="1"
                                       name="level_iot" class="w-full">
                                <datalist id="level_iot">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                    </div>
                    <!-- SECTION 2 -->
                    <div x-show="step === 2" id="section-2">
                        <p class="text-2xl w-full text-center">&nbsp;</p>
                        <div>
                            Ora ti porremo delle domande tecniche per provare a capire il tuo livello di conoscenze su alcuni argomenti.
                            <br> Non ti preoccupare se non conosci la risposta ad una o più domande: in tal caso indica l'opzione "Non so".
                            <br> Ti chiediamo gentilmente di rispondere al meglio delle tue conoscenze e non utilizzare aiuti esterni, in quanto
                            questo potrebbe inficiare negativamente sui risultati dello studio.
                            <br> Ci teniamo, infine, a ricordarti che il test è anonimo e che non utilizzeremo in alcun modo il tuo indirizzo email dopo la fine del test.
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Cosa denota la scritta “https://” all'inizio di un URL, rispetto a
                                http:// (senza la “s”)?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_1" type="radio" value="That the site has special high definition" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Che il sito ha un'alta definizione speciale</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_2" type="radio" value="That information entered into the site is encrypted" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Che i dati inseriti dall'utente all'interno sito sono criptati</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_3" type="radio" value="That the site is the newest version available" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Che il sito è aggiornato alla versione più recente disponibile</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_4" type="radio" value="That the site is not accessible to certain computers" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Che il sito non è accessibile da certi computer</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_5" type="radio" value="None of the above" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Nessuna delle risposte precedenti</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_6" type="radio" value="Not sure" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Non so </label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Quale dei seguenti è un esempio di attacco di “phishing”?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-1" type="radio" value="Sending someone an email that contains a malicious link that is disguised to look like an email from someone the person knows" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Mandare una mail contenente un link malevolo ad una persona ignara, affinché la mail sembri provenire da una persona che conosce
                                </label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-2" type="radio" value="Creating a fake website that looks nearly identical to a real website in order to trick users into entering their login information" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Creare un sito web fasullo quasi identico ad un sito reale, con lo scopo di ingannare gli utenti e fargli inserire le loro credenziali di login
                                </label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-3" type="radio" value="Sending someone a text message that contains a malicious link that is disguised to look like a notification that the person has won a contest" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Mandare un messaggio a qualcuno contente un link malevolo, affinché sembri una notifica riportante che l'utente in questione è vincitore di un contest
                                </label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-4" type="radio" value="All of the above" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Tutte le risposte precedenti
                                </label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-5" type="radio" value="Not sure" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Un insieme di computer collegati e utilizzati da criminali per rubare informazioni si chiama... </p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_1" type="radio" value="Botnet" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_2" type="radio" value="Rootkit" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Rootkit</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_3" type="radio" value="DDoS" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">DDoS</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_4" type="radio" value="Operating system" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Sistema Operativo</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_5" type="radio" value="Not sure" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">
                                Alcuni siti web e servizi online utilizzano un processo di sicurezza chiamato "Autenticazione a 2 fattori".
                                Quali delle seguenti immagini è un esempio di autenticazione a due fattori?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_1" type="radio" value="0" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_1" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_1.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_2" type="radio" value="1" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_2" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_2.webp') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_3" type="radio" value="2" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_3" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_3.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_4" type="radio" value="3" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_4" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_4.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_5" type="radio" value="4" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Nessuna delle precedenti</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_6" type="radio" value="5" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Quale tra le seguenti password è la più sicura?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_1" type="radio" value="Boat123" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Barca123</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_2" type="radio" value="WTh!5Z" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">WTh!5Z</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_3" type="radio" value="into*48" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">into*48</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_4" type="radio" value="123456" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">123456</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_5" type="radio" value="Not sure" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so
                                </label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1"> I cyber-criminali talvolta accedono in modo illecito nel computer di un utente e effettuano una criptazione dei suoi dati e file personali.
                                L'utente non può accedere a questi dati a meno che non paghi i criminali affinché decriptino i file.
                                Questa pratica è chiamata...</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_1" type="radio" value="Botnet" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_2" type="radio" value="Ransomware" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Ransomware</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_3" type="radio" value="Driving" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Driving</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_4" type="radio" value="Spam" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Spam</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_5" type="radio" value="None of the above" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Nessuna delle precedenti</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_6" type="radio" value="Not sure" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">
                                La "navigazione in incognito" è una funzione di molti browser web che permette agli utenti di accedere a pagine web senza che le loro informazioni
                                (come la cronologia) sia memorizzata dal browser stesso.
                                Un fornitore di servizi Internet (es., Telecom, Fastweb, etc.) può vedere le attività online di un loro cliente quando questi
                                utilizza la navigazione in incognito?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_1" type="radio" value="Yes" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Sì</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_2" type="radio" value="No" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_3" type="radio" value="Not sure" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">
                                Spegnere la funzionalità GPS sul tuo smartphone previene qualsiasi tracciamento della posizione del tuo telefono.
                            </p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_1" type="radio" value="True" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Vero</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_2" type="radio" value="False" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Falso</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_3" type="radio" value="Not sure" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">
                                Se una rete Wi-Fi pubblica (come in un aeroporto o un bar) richiede una password per accedere,
                                generalmente è sicuro utilizzare tale rete per attività sensibili come online banking (es., immettere dati della carta di credito)?
                            </p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_1" type="radio" value="Yes, it is safe" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"> Sì, è sicuro</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_2" type="radio" value="No, it is not safe" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No, non è sicuro</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_3" type="radio" value="Not sure" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">
                                Che genere di rischio di sicurezza può essere minimizzato utilizzando una VPN (Virtual Private Network)?
                            </p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_1" type="radio" value="Use of insecure Wi-Fi networks" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Uso di reti Wi-Fi non sicure</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_2" type="radio" value="Key-logging" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Key-logging (registrazione della tastiera)</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_3" type="radio" value="De-anonymization by network operators"
                                       name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">De-anonimizzazione da parte di operatori di rete</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_4" type="radio" value="Phishing attacks" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Attacchi di phishing
                                </label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_5" type="radio" value="Not sure" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Non so
                                </label>
                            </div>
                        </div>
                    </div>
                    <div x-show="step === 3" class="pt-1">
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
                        <div x-show="step !== 3" class="flex flex-row w-full" :class="{'space-x-6' : step > 0}">
                            <div x-show="step > 1">
                                <button type="button" id="back" @click="previous()"
                                        class="py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                    Precedente
                                </button>
                            </div>
                            <div x-show="step > 0" class="flex-1"></div>
                            <div :class="{'flex-1 w-full': step === 0}">
                                <button type="button" id="next" @click="next()"
                                        :class="{'w-full' : step === 0, 'w-64' : step > 0}"
                                        class="py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Successivo
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            let last_section = 3
            function load() {
                $("#form").show();
                return {
                    step: 1,
                    previous(e) {
                        if (this.step > 1)
                            this.step--;

                        $("#alert").hide();

                        $(window).scrollTop(0);

                        if (this.step < 5) {
                            $("#next").text("Next");
                        }
                    },
                    next(e) {
                        let check = true;
                        switch (this.step) {
                            case 1:
                                $("#section-1 :input").each(function () {
                                    check = ($(this).val() != "" && check);
                                    if ($(this).val() == "") {
                                        $(this).addClass('border-red-500');
                                        $(this).change(_ => $(this).removeClass('border-red-500'));
                                    } else
                                        $(this).removeClass('border-red-500');
                                });
                                break;
                            case 2:
                                for (let i = 1; i <= 10; i++) {
                                    let input = 'input[name=cyber_' + i + ']';
                                    if (!$(input).is(':checked')) {
                                        check = false;
                                        $(input).parent().removeClass('border-gray-300');
                                        $(input).parent().addClass('border-red-500');
                                        $(input).change(_ => {
                                            $(input).parent().removeClass('border-red-500');
                                            $(input).parent().addClass('border-gray-300');
                                        });
                                    } else
                                        check = (check && true);
                                }
                                break;
                            case 4:
                                if (!$("input[name=have_read_warning]").is(':checked')) {
                                    check = false;
                                    $("input[name=have_read_warning]").removeClass('border-gray-300');
                                    $("input[name=have_read_warning]").addClass('border-red-500');
                                    $('input[name=have_read_warning]').change(_ => {
                                        $("input[name=have_read_warning]").removeClass('border-red-500');
                                        $("input[name=have_read_warning]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);
                                break;
                            case 5:
                                if (!$("input[name=gender]").is(':checked')) {
                                    check = false;
                                    $("input[name=gender]").removeClass('border-gray-300');
                                    $("input[name=gender]").addClass('border-red-500');
                                    $('input[name=gender]').change(_ => {
                                        $("input[name=gender]").removeClass('border-red-500');
                                        $("input[name=gender]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                if ($('input[name=age]').val() < 14 || $('input[name=age]').val() > 100) {
                                    check = false;
                                    $("input[name=age]").removeClass('border-gray-300');
                                    $("input[name=age]").addClass('border-red-500');
                                    $('input[name=age]').change(_ => {
                                        $("input[name=age]").removeClass('border-red-500');
                                        $("input[name=age]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                if ($('input[name=num_hours_day_internet]').val() < 0) {
                                    check = false;
                                    $("input[name=num_hours_day_internet]").removeClass('border-gray-300');
                                    $("input[name=num_hours_day_internet]").addClass('border-red-500');
                                    $('input[name=num_hours_day_internet]').change(_ => {
                                        $("input[name=num_hours_day_internet]").removeClass('border-red-500');
                                        $("input[name=num_hours_day_internet]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                break;
                        }

                        if (!check) {
                            $("#alert").show();
                            $("#alert").fadeTo("fast", 1, function () {
                                setTimeout(function () {
                                    $("#alert").fadeTo("fast", 0, function () {
                                        $("#alert").hide();
                                    });
                                }, 10000);
                            });

                        }

                        if (this.step !== last_section && check) { //!==5
                            this.step++;
                            $("#alert").hide();
                        }

                        $(window).scrollTop(0);

                        if (this.step === last_section-1) {
                            $("#next").text("Sezione successiva");
                        }

                        if (this.step === last_section) {
                            $("#form").submit();
                        }
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
