<x-guest-layout>
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div class="flex flex-row justify-center">
                <img class="float-left object-scale-down pb-1" style="max-height: 6rem; width: auto; margin:0" src="/assets/img/uniba.png" alt="lab logo">
            </div>
            <div class="w-full sm:max-w-2xl p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose">
                {!! $terms !!}
            </div>
            <div class="text-center mt-10 p-2">
                <div class="w-full">
                    <button onclick="window.location.replace('{{ route('show', ['folder' => 'inbox']) }}')"
                            class="p-3 w-full text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Start the study
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
