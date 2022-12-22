<x-app-layout>
    <x-slot name="slot">
       <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate to this study
                </div>
                <p class="text-xl max-w-4xl text-left pt-4">
<b>Condizione sperimentale: {{$condition}} </b>
                    <br>Dear participant, we are conducting a research at the University of Bari involving the
                    evaluation of an e-mail client.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    In this study, which will last about 20 minutes, we ask you to imagine being Andrea, a 28 years old guy from Rome, Italy. <br>
                    Andrea uses various social networks, among which Instagram, Facebook, Twitter, and TikTok. <br>
                    Moreover, Andrea uses eBay and Amazon to make online purchases. Andrea really loves music and goes to live concerts monthly. <br>
                    Andrea works at an IT company and has accepted to test a new email client that his company has recently employed.
<br/>To test this client, we ask you to put yourself in Andrea's shoes, by interacting with the new email client by reading ALL of his emails in the inbox and checking that all the links in them, if any, are working.
                </p><!--
                <p class="text-xl max-w-4xl text-left pt-4">
                    At the end of the study, you will be asked to fill in a questionnaire.
                </p>-->
                <div class="text-center mt-10">
                    <div class="w-full">
                        <div>
                            <button onclick="window.location.replace('{{ route('show', ['folder' => 'inbox']) }}')"
                                    class="py-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Start the study
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
