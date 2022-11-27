<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">

            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl z-20l" style="max-width: 90%">
                <form action="{{ Request::url() }}" method="POST" x-data="load()" id="form" style="display:none;">
                @csrf
                    <!-- SECTION 0 -->
                    <div x-show="step === 0" id="section-0" class="text-xl text-left pt-3">
                        <h1 class="text-3xl font-bold text-center mb-4 cursor-pointer">
                            Commenti finali
                        </h1>
                        <div>
                            <p class="pt-4">
                                Grazie per essere arrivato fino alla fine dello studio. Vorremo sapere alcuni tuoi pareri prima di terminare.
                            </p>
                        </div>
                        <div class="my-5">
                            <p class="pb-2">
                                - Ci sono altri attacchi che vorresti che Alexa fosse in grado di rilevare nella smart home?  Nel caso, quali?
                            </p>
                            <textarea rows="5" name="other_events" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>

                            <p class="pt-12 pb-2">
                                - Ci sono altre azioni che vorresti che Alexa fosse in grado di fare per proteggere la smart home da attacchi? Nel caso, quali?
                            </p>
                            <textarea rows="3" name="other_actions" placeholder="Scrivi qui..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" ></textarea>
                        </div>
                    </div>

                    <!-- SECTION 1 -->
                    <div x-show="step === 1" id="section-1" class="text-xl text-left pt-3">
                        <p class="pt-4 pb-2">
                            (Opzionale) Pensi che ci possano essere modi alternativi per definire i comportamenti che hai espresso con regole ECA?<br/>
                            <br/>
                            Ad esempio, senza utilizzare regole ECA, c'è un altro modo in cui preferiresti dire ad Alexa che intendi difendere le telecamere di sicurezza da attacchi che le possono far smettere di funzionare (attacco <i>DoS</i>)?
                        </p>
                        <textarea rows="10" name="alternatives" placeholder="Scrivi qui..."
                                  class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                    </div>

                    <!-- Loading section -->
                    <div x-show="step === 2" class="pt-1">
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
                        <div x-show="step !== 2" class="flex flex-wrap w-full" :class="{'space-x-6' : step > 0}">
                            <div x-show="step > 0">
                                <button type="button" id="back" @click="previous()"
                                        class="shrink py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                    Indietro
                                </button>
                            </div>
                            <div x-show="step >= 0" class="flex-1"></div>
                            <div>
                                <button type="button" id="next" @click="next()" style="min-width: 190px"
                                        class="flex-initial w-72 py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Ultima domanda
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            let form_html = $("#form")
            let next_btn = $("#next")

            function load() {
                let last_section = 2
                $(".num_slides").each (function(){
                    $(this).html(last_section-1)
                })
                form_html.show();
                return {
                    step: 0,
                    previous(e) {
                        if (this.step > 0)
                            this.step--;

                        $(window).scrollTop(0);

                        if (this.step < last_section-1) {
                            next_btn.text("Ultima domanda");
                        }
                    },
                    next(e) {

                        if (this.step !== last_section) {
                            this.step++;
                        }

                        $(window).scrollTop(0);

                        if (this.step === last_section-1) {
                            next_btn.text("Termina studio");
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
