<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-20 h-20 mx-auto text-green-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-center font-bold my-3 text-3xl w-full">
                        Sorry, it seems like you've already taken this test!
                    </div>
                    <div class="text-xl max-w-2xl text-left pt-3">
                        This test can only be taken once by each individual, therefore you cannot take it again! Thank you!
                        <br>
                        <br>
                        Goodbye!<br>
                        <div style="font-style: italic">
                        IVU Lab, University of Bari, Italy, <br>
                        University of Cagliari, Italy, <br>
                        CYS group, King's College London, UK
                        </div>
                        <div class="mt-6 flex flex-row justify-center" >
                            <img src="{{asset("/assets/img/UNIBA_logo.svg")}}" alt="university of Bari logo" style="max-width: 25%">
                            <img src="{{asset("/assets/img/UNICA_logo.png")}}" alt="university of Cagliari logo" style="max-width: 25%">
                            <img src="{{asset("/assets/img/KCL_logo.png")}}" alt="king's college london logo" style="max-width: 20%">
                            <img src="{{asset("/assets/img/IVUx-Logo.svg")}}" alt="ivu lab logo" style="max-width: 25%">
                        </div>
                    </div>
                </div>
                {{-- <form method="POST" style="padding: 50px;" action="{{ route('logout') }}">
                    @csrf

                    <!--<div class="text-center mt-10">
                        <div class="w-full">
                            <div>
                                <button type="submit" id="logout"
                                        class="py-3 w-64 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                --}}
            </div>
        </div>
    </x-slot>
</x-app-layout>
