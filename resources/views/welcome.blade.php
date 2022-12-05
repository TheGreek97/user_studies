<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate to this test.
                </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Dear participant, we are conducting a research at the University of Bari involving the
                    evaluation of an e-mail client.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    In this study, you have to interact with an email client to assess its usability.
                    In doing this, please READ ALL THE EMAILS and, for those that you consider important, check that the links are all working.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    At the end of the study, you will have to fill in a questionnaire.
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
