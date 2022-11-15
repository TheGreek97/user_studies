<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate to this test.
                </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Dear participant, we are conducting a research at the University of Bari "A. Moro" involving the
                    evaluation of an e-mail client and, more specifically, to check that the visualisation of each e-mail is correct.

                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    We ask you to open each e-mail and, if there is a link in it, to click on it to check that it works. Each time you read an email you will asked to answer three quick questions.

                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    When you have read all the 10 emails, you will have to fill out a final questionnaire. Please, make sure you fill it out completely before closing the test.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    For any information, please feel free to contact <a href="mailto:francesco.greco@uniba.it">francesco.greco@uniba.it</a>
                </p>
                <div class="text-center mt-10">
                    <div class="w-full">
                        <div>
                            <button onclick="window.location.replace('{{ route('show', ['folder' => 'inbox']) }}')"
                                    class="py-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Start the test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
