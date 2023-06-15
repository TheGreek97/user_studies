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
                        Hope to see you next time!
                    </div>
                    <div class="text-xl max-w-2xl text-left pt-3">
                        Since you did not accept the informed consent, you cannot proceed to the study. <br>
                        If you performed this action by mistake, or changed your mind, <a href="#" style="text-decoration: underline; color:#1c64f2" onclick="window.location.replace('{{route('welcome')}}')">click here to go back </a> to the previous page.
                        <br><br>
                        Goodbye!<br>
                        The team of IVU Lab, University of Bari, Italy
                        <div class="mt-6" style="display: flex;justify-content: center;">
                            <img src="/assets/img/logo_ivu.png" alt="lab logo" style="max-width: 35%">
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
