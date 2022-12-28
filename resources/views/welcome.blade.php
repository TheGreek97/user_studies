<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate to this test.
                </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Dear participant, at the University of Bari we are evaluating a new prototype of email client.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    In this study, which will take about 15 minutes, we ask you to imagine being Andrea, a 28 years old guy who lives in Rome, Italy. <br>
                    Andrea uses various social networks, among which Instagram, Facebook, Twitter, and TikTok. <br>
                    Moreover, Andrea uses Ebay and Amazon to make online purchases with his italian credit card. Andrea really loves music and goes to live concerts monthly. <br>
                    Andrea works at an IT company and has accepted to test a new email client that his company has recently adopted. To test the new email client,
                    Andrea must interact with it, by READING ALL HIS EMAILS in the inbox and, for those that can be considered important, check that all the links are working.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    At the end of the study, you will be asked to fill in a questionnaire.
                </p>
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
