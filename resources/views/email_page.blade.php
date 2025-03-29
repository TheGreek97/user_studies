<?php
use Illuminate\Support\Facades\Auth;
?>
<x-app-layout>
    <x-slot name="slot">
        <div style="position: sticky; top: 0; left: 0; z-index: 40;"
            class="p-6 shadow-lg bg-gray-700 text-white flash-element">
            @if (session('post_phase'))
                <p>
                    <span class="font-semibold">GOAL:</span> Please review all the emails in your inbox once again and
                    now answer based on the knowledge you've gained during the training.
                </p>
            @else
                <p>
                    <span class="font-semibold">GOAL:</span> For each email, determine if it is a phishing attempt and
                    rate your confidence on a scale of 1 to 7.
                </p>
            @endif
        </div>

        <!-- Task presentation modal -->
        <div id="task-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            style="z-index: 99; background: rgba(0,0,0,30%);"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    @if (session('post_phase'))
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                GOAL:
                            </h3>
                        </div>
                        <!-- Modal body -->
                        <div class="p-4 md:p-5 space-y-4">
                            <p class="text-base leading-relaxed text-gray-900 dark:text-gray-400">
                                Please review all the emails in your inbox once again and now answer based on the
                                knowledge you've gained during the training.
                            </p>
                        </div>
                    @else
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                GOAL:
                            </h3>
                        </div>
                        <!-- Modal body -->
                        <div class="p-4 md:p-5 space-y-4">
                            <p class="text-base leading-relaxed text-gray-900 dark:text-gray-400">
                                Please carefully read each email in the inbox below. <br>
                                For every email, you need to:
                                <br>- Determine whether you believe it is a <b>phishing attempt</b> or not.
                                <br>- Rate how <b>confident</b> you are in your decision on a scale of 1 to 7.<br>
                            </p>
                            <p>Note that the number of phishing emails among the others is random (i.e., you might see only phishing or only genuine emails). </p>
                        </div>
                    @endif
                    <!-- Modal footer -->
                    <div class="flex items-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600"
                        style="flex-direction: row-reverse">
                        <button id="close-task-modal-btn" data-modal-hide="static-modal" type="button"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Start</button>
                    </div>
                </div>
            </div>
        </div>


        @if (isset($startStudy))
            <script type="module">
                { // show the modal
                    // set the modal menu element
                    const $targetEl = document.getElementById('task-modal')

                    // options with default values
                    const options = {
                        placement: 'bottom-right',
                        backdrop: 'static',
                        backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                        closable: true,
                        onHide: () => {
                            console.log('modal is hidden');
                        }
                    };

                    // instance options object
                    const instanceOptions = {
                        id: 'modalEl',
                        override: true
                    };
                    /*
                     * $targetEl: required
                     * options: optional
                     */
                    const modal = new Modal($targetEl, options, instanceOptions);

                    function shrinkModalToNavbar() {
                        $targetEl.firstElementChild.classList.add("modal-shrink") // add the animation to the modal
                        setTimeout(() => modal.hide(), 1100) // wait 2 second for the animation to complete, then hide the modal
                    }

                    modal.show()
                    document.getElementById('close-task-modal-btn').onclick = shrinkModalToNavbar
                }
            </script>
        @endif

        <div class="flex bg-gray-100 dark:bg-gray-900 overflow-x-hidden" :class="{ 'overflow-hidden': isSideMenuOpen }"  style="height: calc(100vh - 77px);">
            <!-- Desktop sidebar -->
            <aside class="z-20 hidden w-56 overflow-y-auto bg-white dark:bg-gray-800 custom:block flex-shrink-0">
                <div class="py-4 text-gray-500 dark:text-gray-400">
                    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">
                        {{ config('app.name', 'E-Mail Client') }}
                    </a>
                    <div class="px-6 my-6">
                        <button data-modal-toggle="compose_modal"
                            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                            Compose
                            <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg></span>
                        </button>
                    </div>
                    <ul class="mt-6">
                        <li class="relative px-6 py-3">
                            @if ($folder === 'inbox')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a href="{{ route('emails', ['folder' => 'inbox']) }}"
                                @if ($folder === 'inbox') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                </svg>
                                <span class="ml-4">Inbox (21)</span>
                            </a>
                        </li>
                    </ul>
                    <ul>
                        <li class="relative px-6 py-3">
                            @if ($folder === 'sent')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a href="{{ route('emails', ['folder' => 'sent']) }}"
                                @if ($folder === 'sent') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span class="ml-4">Sent (0)</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if ($folder === 'draft')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a href="{{ route('emails', ['folder' => 'draft']) }}"
                                @if ($folder === 'draft') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="ml-4">Drafts (0)</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if ($folder === 'trash')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a href="{{ route('emails', ['folder' => 'trash']) }}"
                                @if ($folder === 'trash') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="ml-4">Trash (0)</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <!-- Mobile sidebar -->
            <!-- Backdrop -->
            <div x-show="isSideMenuOpen"
                 x-transition:enter="transition ease-in-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in-out duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-25 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
            </div>
            <aside
                class="fixed inset-y-0 z-20 flex-shrink-0 w-56 mt-16 overflow-y-auto bg-white dark:bg-gray-800 custom:hidden"
                x-show="isSideMenuOpen"
                x-transition:enter="transition ease-in-out duration-150"
                x-transition:enter-start="opacity-0 transform -translate-x-20"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in-out duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform -translate-x-20"
                @click.away="closeSideMenu"
                @keydown.escape="closeSideMenu">
                <div class="py-4 text-gray-500 dark:text-gray-400">
                    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">
                        Email Client
                    </a>
                    <div class="mx-6 my-6">
                        <button
                            class="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                            Compose
                            <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg></span>
                        </button>
                    </div>
                    <ul class="mt-6">
                        <li class="relative px-6 py-3">
                            @if ($folder === 'inbox')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a @if ($folder === 'inbox') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif
                                href="{{ route('emails', ['folder' => 'inbox']) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                </svg>
                                <span class="ml-4">Inbox</span>
                            </a>
                        </li>
                    </ul>
                    <ul>
                        <li class="relative px-6 py-3">
                            @if ($folder === 'sent')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a @if ($folder === 'sent') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif
                                href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span class="ml-4">Sent</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if ($folder === 'draft')
                                <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"></span>
                            @endif
                            <a @if ($folder === 'draft') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif
                                href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="ml-4">Drafts</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            <a @if ($folder === 'trash') class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @endif
                                href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="ml-4">Trash</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <div class="flex flex-col flex-1 w-full">
                <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
                    <div style="min-width:99%;"
                        class="container flex justify-between w-full h-full px-6 ml-3 text-blue-600 dark:text-blue-300">
                        <!-- Mobile hamburger -->
                        <button
                            class="p-1 mr-5 -ml-1 rounded-md custom:hidden block focus:outline-none focus:shadow-outline-blue"
                            @click="toggleSideMenu" aria-label="Menu">
                            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <!-- Search input -->
                        <!--div class="flex justify-center flex-1 lg:mr-32 opacity-0">
                            <div class="relative w-full max-w-xl mr-6 focus-within:text-blue-500">
                                <div class="absolute inset-y-0 flex items-center pl-2">
                                    <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input
                                    class="w-full pl-8 pr-2 text-sm text-gray-700 placeholder-gray-600 bg-gray-100 border-0 rounded-md dark:placeholder-gray-500 dark:focus:shadow-outline-gray dark:focus:placeholder-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:placeholder-gray-500 focus:bg-white focus:border-blue-300 focus:outline-none focus:shadow-outline-blue form-input"
                                    type="text" placeholder="Search for projects" aria-label="Search" />
                            </div>
                        </div>
                        -->
                        <ul class="flex items-center flex-shrink-0 space-x-6">
                            <li class="relative">
                                <button class="align-middle rounded-full focus:shadow-outline-blue focus:outline-none"
                                    x-ref="button" x-on:click="toggleProfileMenu()" aria-label="Account"
                                    aria-haspopup="true">
                                    <img class="object-cover w-8 h-8 rounded-full"
                                        src="https://ui-avatars.com/api/?background=1C64F2&color=fff&name={{ \Illuminate\Support\Facades\Auth::user()->name }}"
                                        alt="" aria-hidden="true" />
                                </button>
                            </li>
                        </ul>
                    </div>
                </header>

                <main class="overflow-y-auto" style="height:100%; padding: 0 2%">
                    <div class="px-0 md:px-6 mx-5 md:mx-auto grid mb-10">
                        @if (!isset($selected_email))
                            <h2
                                class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200 flex flex-row align-middle content-center">
                                <div class="pr-1">
                                    @switch($folder)
                                        @case('inbox')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                            </svg>
                                        @break

                                        @case('sent')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                        @break

                                        @case('draft')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        @break

                                        @case('trash')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        @break
                                    @endswitch
                                </div>
                                <div class="capitalize">{{ $folder }}</div>
                            </h2>
                            <!-- New Table -->
                            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-10">
                                <div class="w-full overflow-x-auto">
                                    <table class="w-full">
                                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                            @foreach ($emails ?? [] as $email)
                                                @php
                                                    // Checks whether the current email has already been opened
                                                    $currentOpened = DB::table('useremailquestionnaire')
                                                                        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                                                                        ->where('email_id', $email->id)
                                                                        ->exists();

                                                     // Checks whether the previous email has already been opened
                                                    $prevOpened = $loop->first ? true : false;
                                                    if (!$loop->first) {
                                                        // Recupera l'email precedente dall'array/collezione
                                                        $prevEmail = $emails[$loop->index - 1];
                                                        $prevOpened = DB::table('useremailquestionnaire')
                                                                        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                                                                        ->where('email_id', $prevEmail->id)
                                                                        ->exists();
                                                    }

                                                    // Email is clickable if it has NOT been opened and if (it is the first one or the previous one is opened)
                                                    $clickable = !$currentOpened && $prevOpened;
                                                @endphp
                                                @if (!$clickable)
                                                    @if (!$prevOpened)
                                                        <tr class="text-gray-700 cursor-not-allowed bg-gray-50" title="Please answer the emails in order">
                                                    @else
                                                        <tr class="text-gray-700 cursor-not-allowed bg-gray-300"  title="You have already answered to this email">
                                                    @endif
                                                @else
                                                    <tr class="text-gray-700 cursor-pointer hover:bg-gray-200 hover:dark:bg-gray-600 dark:text-gray-400 border-2 border-blue-800"
                                                        @if ($email->type === 'inbox')
                                                            onclick="window.location.href = '{{ route('emails', ['folder' => $folder, 'id' => $email->id]) }}'"
                                                        @endif
                                                    >
                                                @endif
                                                <td class="px-4 py-4 text-sm">
                                                    <div class="flex items-center truncate font-semibold">
                                                        <p>{{ $email->from_name }}</p>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 py-4">
                                                    <p
                                                        class="text-ellipsis font-bold text-gray-600 dark:text-gray-300 truncate text-sm">
                                                        {{ $email->subject }}
                                                    </p>
                                                    <p
                                                        class="text-gray-600 dark:text-gray-400 overflow-hidden truncate text-xs">
                                                        {{ $email->preview_text }}
                                                    </p>
                                                </td>
                                                <td class="px-4 py-4 text-xs font-bold truncate">
                                                    {{ date('d M', strtotime($email->date)) }}
                                                </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div style="width:71%" class="h-auto rounded-lg shadow-xs my-10 p-3 bg-white dark:bg-gray-800 overflow-y-hidden sm:mr-0 w-full md:w-auto md:mr-[280px]">
                                <!-- Contenuti del primo div -->
                                <div class="flex flex-row space-x-2 pb-2">
                                    <a data-tooltip-target="tooltip-back" data-tooltip-placement="bottom"
                                        class="cursor-pointer hover:bg-gray-200 rounded-full p-2"
                                        href="{{ route('emails', ['folder' => $folder]) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        <div id="tooltip-back" role="tooltip"
                                            class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Go back
                                        </div>
                                    </a>
                                    <a data-tooltip-target="tooltip-spam" data-tooltip-placement="bottom"
                                        class="cursor-pointer hover:bg-gray-200 rounded-full p-2" href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <div id="tooltip-spam" role="tooltip"
                                            class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Report Spam
                                        </div>
                                    </a>
                                    <a data-tooltip-target="tooltip-delete" data-tooltip-placement="bottom"
                                        class="cursor-pointer hover:bg-gray-200 rounded-full p-2" href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <div id="tooltip-delete" role="tooltip"
                                            class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Delete
                                        </div>
                                    </a>
                                    <a data-tooltip-target="tooltip-reply" data-tooltip-placement="bottom"
                                        class="cursor-pointer hover:bg-gray-200 rounded-full p-2" href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        <div id="tooltip-reply" role="tooltip"
                                            class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Reply
                                        </div>
                                    </a>
                                    <a data-tooltip-target="tooltip-forward" data-tooltip-placement="bottom"
                                        class="cursor-pointer hover:bg-gray-200 rounded-full p-2" href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            style="transform: scale(-1,1)" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        <div id="tooltip-forward" role="tooltip"
                                            class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Forward
                                        </div>
                                    </a>
                                </div>
                                <h2 class="my-4 text-xl font-bold text-gray-700 dark:text-gray-200">
                                    {{ $selected_email->subject }}
                                </h2>
                                <div class="flex flex-row mb-4">
                                    <div class="pt-0.5">
                                        <img class="object-cover w-8 h-8 rounded-full"
                                            src="https://ui-avatars.com/api/?name={{ $selected_email->from_name }}"
                                            alt="" aria-hidden="true">
                                    </div>
                                    <div class="ml-3">
                                        <div class="flex flex-row items-center">
                                            <h3 class="text-sm font-semibold dark:text-white">
                                                {{ $selected_email->from_name }}
                                            </h3>
                                            <span class="ml-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                {{ $selected_email->from_email }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                            to me</h4>
                                    </div>
                                    <div
                                        class="flex-1 pt-0.5 font-semibold text-right text-gray-500 text-xs align-middle dark:text-gray-400">
                                        {!! date('d M Y', strtotime($selected_email->date)) . '<br>' . date('H:i', strtotime($selected_email->date)) !!}
                                    </div>
                                </div>
                                <!--IFRAME-->
                                <div id="email_content" class="px-1 pt-4 dark:bg-white" style="padding-bottom: 2.5rem; max-width: 1000px;">
                                    <iframe id="email_frame" style="width: 100%; border: none;"
                                        srcdoc="{!! htmlspecialchars($htmlContent ?? '', ENT_QUOTES, 'UTF-8') !!}"><!--height: 60vh;-->
                                    </iframe>
                                </div>
                                <script>
                                    const emailContent = document.getElementById('email_content');
                                    const emailFrame = document.getElementById('email_frame');
                                    const referenceWidth = 800; // larghezza di riferimento del contenuto
                                    const extraSpace = 60; // spazio extra da aggiungere in fondo

                                    function adjustIframeSize() {
                                      const currentWidth = emailContent.clientWidth;
                                      const scale = currentWidth / referenceWidth;

                                      const iframeDocument = emailFrame.contentDocument || emailFrame.contentWindow.document;
                                      if (iframeDocument && iframeDocument.body) {
                                        // Imposta inizialmente il contenuto centrato
                                        iframeDocument.body.style.transform = `scale(${scale})`;
                                        iframeDocument.body.style.transformOrigin = 'top center'; // All'inizio è centrato
                                        iframeDocument.body.style.width = referenceWidth + 'px';
                                        iframeDocument.body.style.margin = '0 auto';

                                        // Rimuove lo scroll impostando overflow hidden
                                        iframeDocument.body.style.overflow = 'hidden';

                                        // Calcola l'altezza necessaria in base al contenuto scalato e aggiungi spazio extra
                                        const contentHeight = iframeDocument.body.scrollHeight;
                                        emailFrame.style.height = (contentHeight * scale + extraSpace) + 'px';

                                        // Cambia il transform-origin a top-left quando la finestra è ridimensionata
                                        if (currentWidth < referenceWidth) {
                                          iframeDocument.body.style.transformOrigin = 'top left';
                                        } else {
                                          iframeDocument.body.style.transformOrigin = 'top center'; // Torna a center se la larghezza è sufficiente
                                        }
                                      }
                                    }

                                    // Richiama la funzione al caricamento e al ridimensionamento della finestra
                                    window.addEventListener('resize', adjustIframeSize);
                                    window.addEventListener('load', adjustIframeSize);
                                  </script>


                            </div>
                            <style>
                                /* Stile di base: applicato a tutte le viewport */
                                .cssVersionDiv {
                                    background-color: #ffffff;            /* bg-white */
                                    border: 2px solid #3f83f8;            /* border-2 e border-blue-500 */
                                    border-radius: 8px;                     /* rounded-lg */
                                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                                                0 2px 4px -2px rgba(0, 0, 0, 0.1); /* shadow-md */
                                    padding: 16px;                          /* p-4 (16px) */
                                    width: 27%;                            /* w-full */
                                    height: auto;                           /* h-auto */
                                    position: fixed;                       /* Posizionamento di default */
                                    top: 15%;
                                    left: auto;
                                    right: 2.5%;

                                    max-height: 80vh;
                                    z-index: 41;                            /* z-20 */
                                    overflow-y:auto;
                                }

                                /* Modalità dark: se un elemento genitore ha la classe "dark" */
                                .dark .cssVersionDiv {
                                background-color: #1a1c23;              /* dark:bg-gray-800 */
                                }

                                /* Breakpoint small (sm: min-width 640px)
                                @media (min-width: 640px) {
                                .cssVersionDiv {
                                    width: 90%;
                                    position: relative;
                                    left: 50%;
                                    transform: translateX(-50%);
                                }
                                }*/

                                /* Breakpoint medium (md: min-width 768px)
                                @media (min-width: 768px) {
                                .cssVersionDiv {
                                    width: 265px;
                                    height: 665px;
                                    position: fixed;
                                    right: 20px;
                                    top: 150px;
                                    left: auto;
                                    transform: none;
                                }
                                }*/

                            </style>
                            <div class="cssVersionDiv">
                                {{-- class="w-full sm:w-[90%] md:w-[265px] h-auto md:h-[665px] border-2 border-blue-500 rounded-lg shadow-md p-4
                                        bg-white dark:bg-gray-800 z-20
                                        sm:relative sm:left-1/2 sm:-translate-x-1/2
                                        md:fixed md:right-5 md:top-50 md:left-auto md:translate-x-0" --}}


                                <h3 class="md:text-2xl font-bold text-gray-800">Evaluate this email</h3>
                                <div x-data="{ phishing: '' }" class="space-y-3 my-6">
                                    <p class="md:text-lg text-gray-900 dark:text-white font-semibold">Do you think this
                                        email is a phishing attempt?</p>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" x-model="phishing" name="phishing" value="yes"
                                            class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded
                                                focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800
                                                dark:bg-gray-700 dark:border-gray-600">
                                        <span class="md:text-lg text-gray-900 dark:text-white">Yes</span>
                                    </label>

                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" x-model="phishing" name="phishing" value="no"
                                            class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded
                                                focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800
                                                dark:bg-gray-700 dark:border-gray-600">
                                        <span class="md:text-lg text-gray-900 dark:text-white">No</span>
                                    </label>
                                </div>

                                <!-- Vertical Range Input -->
                                <div class="my-6">
                                    <p class="md:text-lg text-gray-900 dark:text-white font-semibold ">Rate your
                                        confidence in your decision:</p>
                                    <div class="flex flex-col items-start space-y-3">
                                        <!-- Label for "Not confident at all" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="1"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="text-lg text-gray-900 dark:text-white">1</span>
                                            <span class="italic ml-2 w-full md:text-lg text-gray-900 dark:text-white" style="font-style: italic"> Not
                                                confident at all</span>
                                        </label>

                                        <!-- Label for "2" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="2"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">2</span>
                                        </label>

                                        <!-- Label for "3" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="3"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">3</span>
                                        </label>

                                        <!-- Label for "4" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="4"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">4</span>
                                        </label>

                                        <!-- Label for "5" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="5"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">5</span>
                                        </label>

                                        <!-- Label for "6" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="6"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">6</span>
                                        </label>

                                        <!-- Label for "Completely confident" -->
                                        <label class="flex items-center space-x-2 cursor-pointer w-full">
                                            <input type="radio" name="confidence" value="7"
                                                class="rounded-full w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="md:text-lg text-gray-900 dark:text-white">7</span>
                                            <span style="font-style: italic"
                                                class="ml-2 italic w-full md:text-lg text-gray-900 dark:text-white"> Completely
                                                confident</span>
                                        </label>
                                    </div>

                                </div>

                                <div class="text-center mt-6">
                                    <form method="POST" id="myForm"
                                        action="{{ route('save-email-classification') }}">
                                        @csrf
                                        <input type="hidden" name="emailId" value="test@example.com">
                                        <input type="hidden" name="confidence" value="7">
                                        <input type="hidden" name="phishing" value="true">
                                        <input type="hidden" name="time_spent">

                                        <button id="submit_btn"
                                            class="py-3 w-full md:text-xl text-white bg-blue-500 rounded-2xl">Submit
                                            your response</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </main>
            </div>

        </div>

        <div style="position: sticky; top: 0; left: 0; z-index: 99; height: 5px;"
        class="shadow-lg bg-gray-700 text-white">
        <x-modal name="error-modal" id="errorModal" title="Compile all the questions!" :show="false">
            <div class="p-4 rounded-lg relative text-center">
                <p class="text-2xl font-semibold text-red-700 pb-8">Evaluate the email !</p>
                <p id="modalMessage" class="text-lg text-gray-800 pb-8"></p>
                <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="too-fast-modal" id="too-fast-modal" title="Compile all the questions slowly!" :show="false"
            x-data="{ show: false }" x-show="show" @open-modal.window="show = true">
            <div class="p-4 rounded-lg relative text-center">
                <p class="text-2xl font-semibold text-red-700 pb-8">You're going too fast!</p>
                <p class="text-lg text-gray-800 pb-8">
                    Please slow down and carefully observe the email before answering. <br>
                </p>
                <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
            </div>
        </x-modal>
        </div>
        <script>
            @if (isset($selected_email))

                let startTime = Date.now();

                document.getElementById("submit_btn").addEventListener("click", function(event) {
                    event.preventDefault();

                    // Get the selected phishing radio button value
                    let phishingRadio = document.querySelector('input[name="phishing"]:checked');
                    let phishing = phishingRadio ? phishingRadio.value : null;

                    // Get the selected confidence radio button value
                    let confidenceRadio = document.querySelector('input[name="confidence"]:checked');
                    let confidence = confidenceRadio ? confidenceRadio.value : null;

                    let modalMessage = document.getElementById("modalMessage");
                    let emailId = {{ $selected_email->id }};

                    let errors = [];

                    // An email should be classified after at least 10 seconds
                    let elapsedTime = (Date.now() - startTime) / 1000;
                    if (elapsedTime < 8) {
                        window.dispatchEvent(new CustomEvent('open-modal', {
                            detail: 'too-fast-modal'
                        }));
                        return;
                    }

                    // Validate phishing selection
                    if (!phishing) {
                        errors.push("Please select an answer for 'Do you think this email is a phishing attempt?'");
                    }

                    // Validate confidence selection
                    if (!confidence) {
                        errors.push("Please select a confidence level between 1 and 7.");
                    }

                    // Show errors if any
                    if (errors.length > 0) {
                        const modalEvent = new CustomEvent('open-modal', {
                            detail: 'error-modal',
                        });
                        window.dispatchEvent(modalEvent);
                        modalMessage.innerHTML = errors.join("<br>");
                    } else {
                        // Submit the form data
                        let formData = new FormData();
                        formData.append("phishing", phishing);
                        formData.append("confidence", confidence);
                        formData.append("emailId", emailId);

                        let form = document.getElementById("myForm");
                        form.querySelector("input[name='phishing']").value = phishing;
                        form.querySelector("input[name='confidence']").value = confidence;
                        form.querySelector("input[name='emailId']").value = emailId;
                        form.querySelector("input[name='time_spent']").value = elapsedTime;

                        form.submit();
                    }
                });
            @endif
        </script>
        <script>
            document.getElementById('email_frame').onload = function() {
                let iframe = document.getElementById('email_frame').contentWindow.document;
                let links = iframe.querySelectorAll("a");

                links.forEach(link => {
                    link.addEventListener("click", function(event) {
                        event.preventDefault(); // Impedisce la navigazione
                    });
                });
            };
        </script>
    </x-slot>
</x-app-layout>
