<x-app-layout>
    <x-slot name="slot">
       <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate to this study
                </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Dear participant, at the University of Bari we are evaluating a new prototype of an email client.
                </p>
                <p class="text-xl max-w-4xl text-left pt-4">
                    In this study, which will take about 15 minutes, we ask you to imagine being Andrea, a 28 years old guy who lives in Rome, Italy. <br>
                    Andrea uses various social networks, among which Instagram, Facebook, Twitter, and TikTok. <br>
                    Moreover, Andrea uses Ebay and Amazon to make online purchases with his Italian credit card. Andrea really loves music and goes to live concerts monthly. <br>
                    <br/>
                    Andrea works for an IT company and has agreed to test a new email client that the company has recently adopted. To test the new email client,
                    Andrea must interact with it, by READING ALL HIS EMAILS in the inbox and check that the links in them, if any, are working.
                </p>
                <div class="text-center mt-10">
                    <div class="w-full">
                        <div>
                            <button onclick="window.location.replace('{{ route('terms') }}')"
                                    class="py-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl"> Read the terms
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
