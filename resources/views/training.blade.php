<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-3xl font-bold text-left mb-5 cursor-pointer">Training</div>
                @foreach($sections as $section)
                    {!! $section !!}
                <p class="text-gray-700 mb-6">
                </p>
                @endforeach
                <div class="mt-6 flex justify-center items-center">
                    <button onclick="window.location.href = '{{ route('setPostPhase') }}';"
                        class="py-3 w-1/3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
