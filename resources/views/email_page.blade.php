<?php
//if (\Illuminate\Support\Facades\Auth::id() % 2 == 0)
    $random_warning_explanation = "warning_explanation_1";
/*else
    $random_warning_explanation = "warning_explanation_2";
*/
?>
<x-app-layout>
    <x-slot name="slot">
        <div
            class="flex h-screen bg-gray-100 dark:bg-gray-900"
            :class="{ 'overflow-hidden': isSideMenuOpen }">
            <!-- Desktop sidebar -->
            <aside
                class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
                <div class="py-4 text-gray-500 dark:text-gray-400">
                    <a
                        class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200"
                        href="#"
                    >
                        {{ config('app.name', 'E-Mail Client') }}
                    </a>
                    <div class="px-6 my-6">
                        <button data-modal-toggle="compose_modal"
                                class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                        >
                            Compose
                            <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg"
                                                                       class="h-4 w-4"
                                                                       fill="none" viewBox="0 0 24 24"
                                                                       stroke="currentColor"
                                                                       stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg></span>
                        </button>
                        <!-- Compose modal -->
                        <div id="compose_modal" tabindex="-1" aria-hidden="true"
                             class="bg-black bg-opacity-50 hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
                            <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
                                <!-- Modal content -->
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                    <button type="button"
                                            class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                                            data-modal-toggle="compose_modal">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                    <div class="py-6 px-6 lg:px-8">
                                        <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">New
                                            message</h3>
                                        <form class="space-y-6" action="#"> <!-- send message controller -->
                                            <div>
                                                <label for="to_email"
                                                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">To</label>
                                                <input type="email" name="to_email" id="to_email"
                                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                       placeholder="name@company.com" required>
                                            </div>
                                            <div>
                                                <label for="subject"
                                                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Subject</label>
                                                <input type="text" name="subject" id="subject"
                                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                       placeholder="Lorem ipsum dolor sit amet..." required>
                                            </div>
                                            <div>
                                                <textarea rows="4" name="content" id="content"
                                                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                          required></textarea>
                                            </div>
                                            <button type="submit"
                                                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                Send
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="mt-6">
                        <li class="relative px-6 py-3">
                            @if($folder === 'inbox')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                href="{{ route('show', ['folder' => 'inbox']) }}"
                                @if($folder === 'inbox')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>
                                </svg>
                                <span class="ml-4">Inbox</span>
                            </a>
                        </li>
                    </ul>
                    <ul>
                        <li class="relative px-6 py-3">
                            @if($folder === 'sent')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                href="{{ route('show', ['folder' => 'sent']) }}"
                                @if($folder === 'sent')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <span class="ml-4">Sent</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if($folder === 'drafts')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                href="{{ route('show', ['folder' => 'drafts']) }}"
                                @if($folder === 'drafts')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="ml-4">Drafts</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if($folder === 'trash')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                href="{{ route('show', ['folder' => 'trash']) }}"
                                @if($folder === 'trash')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="ml-4">Trash</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <!-- Mobile sidebar -->
            <!-- Backdrop -->
            <div
                x-show="isSideMenuOpen"
                x-transition:enter="transition ease-in-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in-out duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
            ></div>
            <aside
                class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
                x-show="isSideMenuOpen"
                x-transition:enter="transition ease-in-out duration-150"
                x-transition:enter-start="opacity-0 transform -translate-x-20"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in-out duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform -translate-x-20"
                @click.away="closeSideMenu"
                @keydown.escape="closeSideMenu"
            >
                <div class="py-4 text-gray-500 dark:text-gray-400">
                    <a
                        class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200"
                        href="#"
                    >
                        Email Client
                    </a>
                    <div class="mx-6 my-6">
                        <button
                            class="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
                        >
                            Compose
                            <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg"
                                                                       class="h-4 w-4"
                                                                       fill="none" viewBox="0 0 24 24"
                                                                       stroke="currentColor"
                                                                       stroke-width="2">
  <path stroke-linecap="round" stroke-linejoin="round"
        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
</svg></span>
                        </button>
                    </div>
                    <ul class="mt-6">
                        <li class="relative px-6 py-3">
                            @if($folder === 'inbox')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                @if($folder === 'inbox')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                                href="{{ route('show', ['folder' => 'inbox']) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>
                                </svg>
                                <span class="ml-4">Inbox</span>
                            </a>
                        </li>
                    </ul>
                    <ul>
                        <li class="relative px-6 py-3">
                            @if($folder === 'sent')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                @if($folder === 'sent')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                                href="{{ route('show', ['folder' => 'sent']) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <span class="ml-4">Sent</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            @if($folder === 'drafts')
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <a
                                @if($folder === 'drafts')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                                href="{{ route('show', ['folder' => 'drafts']) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="ml-4">Drafts</span>
                            </a>
                        </li>
                        <li class="relative px-6 py-3">
                            <a
                                @if($folder === 'trash')
                                class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                                @else
                                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @endif
                                href="#"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="ml-4">Trash</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <div class="flex flex-col flex-1 w-full">
                <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
                    <div
                        class="container flex items-center justify-between h-full px-6 mx-auto text-blue-600 dark:text-blue-300"
                    >
                        <!-- Mobile hamburger -->
                        <button
                            class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-blue"
                            @click="toggleSideMenu"
                            aria-label="Menu"
                        >
                            <svg
                                class="w-6 h-6"
                                aria-hidden="true"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>
                        <!-- Search input -->
                        <div class="flex justify-center flex-1 lg:mr-32 opacity-0">
                            <div
                                class="relative w-full max-w-xl mr-6 focus-within:text-blue-500"
                            >
                                <div class="absolute inset-y-0 flex items-center pl-2">
                                    <svg
                                        class="w-4 h-4"
                                        aria-hidden="true"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                </div>
                                <input
                                    class="w-full pl-8 pr-2 text-sm text-gray-700 placeholder-gray-600 bg-gray-100 border-0 rounded-md dark:placeholder-gray-500 dark:focus:shadow-outline-gray dark:focus:placeholder-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:placeholder-gray-500 focus:bg-white focus:border-blue-300 focus:outline-none focus:shadow-outline-blue form-input"
                                    type="text"
                                    placeholder="Search for projects"
                                    aria-label="Search"
                                />
                            </div>
                        </div>
                        <ul class="flex items-center flex-shrink-0 space-x-6">
                            <li class="relative">
                                <button
                                    class="align-middle rounded-full focus:shadow-outline-blue focus:outline-none"
                                    x-ref="button"
                                    x-on:click="toggleProfileMenu()"
                                    aria-label="Account"
                                    aria-haspopup="true"
                                >
                                    <img
                                        class="object-cover w-8 h-8 rounded-full"
                                        src="https://ui-avatars.com/api/?background=1C64F2&color=fff&name={{\Illuminate\Support\Facades\Auth::user()->name}}"
                                        alt=""
                                        aria-hidden="true"
                                    />
                                </button>
                                <template x-if="isProfileMenuOpen">
                                    <ul
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        @mousedown.outside="closeProfileMenu($refs.button)"
                                        class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
                                        aria-label="submenu"
                                    >
                                        <li class="flex">
                                            <form method="POST"
                                                  class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                                  action="{{ route('logout') }}">
                                                @csrf
                                                <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                                   href="#"
                                                   onclick="event.preventDefault();this.closest('form').submit();">
                                                    <svg
                                                        class="w-4 h-4 mr-3"
                                                        aria-hidden="true"
                                                        fill="none"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                                        ></path>
                                                    </svg>
                                                    <span>Log out</span>
                                                </a>
                                            </form>
                                        </li>
                                    </ul>
                                </template>
                            </li>
                        </ul>
                    </div>
                </header>
                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">
                        @if(!isset($selected_email))
                            <h2
                                class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200 flex flex-row align-middle content-center"
                            >
                                <div class="pr-1">
                                    @switch($folder)
                                        @case('inbox')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>
                                        </svg>
                                        @break
                                        @case('sent')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        @break
                                        @case('drafts')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        @break
                                        @case('trash')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-full" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                                        <tbody
                                            class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                                        >
                                        @foreach(\Illuminate\Support\Facades\DB::table('emails')
                                                 ->where('type', $folder)
                                                 ->orderBy('date', 'desc')
                                                 ->get() as $email)
                                            @if($email->warning_type !== \Illuminate\Support\Facades\Auth::user()->warning_type && $email->warning_type !== null)
                                                @continue
                                            @endif
                                            @if(count(DB::table('useremailquestionnaire')->where('user_id', \Illuminate\Support\Facades\Auth::id())->where('email_id', $email->id)->get()) > 0)
                                                <tr class="text-gray-700 cursor-not-allowed bg-gray-300">
                                            @else
                                                <tr class="text-gray-700 cursor-pointer hover:bg-gray-200 hover:dark:bg-gray-600 dark:text-gray-400"
                                                    @if($email->warning_type === "popup_email")
                                                    onclick="open_warning('{{ route('show', ['folder' => $folder,'id' => $email->id]) }}', {{ $email->id }}, '{!! $email->$random_warning_explanation !!}')"
                                                    @else
                                                    onclick="window.location.href = '{{ route('show', ['folder' => $folder,'id' => $email->id]) }}'"
                                                    @endif
                                                >
                                                    @endif
                                                    <td class="px-4 py-4 text-sm">
                                                        <div class="flex items-center truncate font-semibold">
                                                            <p>{{ $email->from_name }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 py-4">
                                                        <p class="text-ellipsis font-bold text-gray-600 dark:text-gray-300 truncate text-sm">
                                                            {{ str_replace("{user_name}", \Illuminate\Support\Facades\Auth::user()->name, $email->subject) }}
                                                        </p>
                                                        <p class="text-gray-600 dark:text-gray-400 overflow-hidden truncate text-xs">
                                                            {{ str_replace("{user_name}", \Illuminate\Support\Facades\Auth::user()->name, $email->preview_text) }}
                                                        </p>
                                                    </td>
                                                    <td class="px-4 py-4 text-xs font-bold truncate">
                                                        {{ \Carbon\Carbon::parse($email->date)->format('d M')}}
                                                    </td>
                                                </tr>
                                                @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div
                                class="w-full rounded-lg shadow-xs my-10 p-3 bg-white dark:bg-gray-800 overflow-y-hidden">
                                <div class="w-full flex flex-row space-x-2 pb-2">
                                    <a data-tooltip-target="tooltip-back" data-tooltip-placement="bottom"
                                       class="cursor-pointer hover:bg-gray-200 rounded-full p-2"
                                       href="{{ route('show', ['folder' => $folder]) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
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
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
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
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                                                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
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
                                                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <div id="tooltip-forward" role="tooltip"
                                             class="text-xs inline-block absolute invisible z-10 py-1 px-2 font-medium text-white bg-gray-700 rounded-lg shadow-sm opacity-0 transition-opacity duration-100 tooltip dark:bg-gray-700">
                                            Forward
                                        </div>
                                    </a>
                                </div>
                                <h2 class="my-4 text-xl font-bold text-gray-700 dark:text-gray-200">
                                    {{ str_replace("{user_name}", \Illuminate\Support\Facades\Auth::user()->name, $selected_email->subject) }}
                                </h2>
                                <div class="flex flex-row mb-4">
                                    <div class="pt-0.5">
                                        <img class="object-cover w-8 h-8 rounded-full"
                                             src="https://ui-avatars.com/api/?name={{$selected_email->from_name}}"
                                             alt=""
                                             aria-hidden="true">
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
                                        {!! \Carbon\Carbon::parse($selected_email->date)->format('d M Y') . "<br>" . \Carbon\Carbon::parse($selected_email->date)->format('H:m:s') !!}
                                    </div>
                                </div>
                                <div id="email_content" class="px-10 py-4 dark:bg-white">
                                    {!! str_replace("{user_name}", \Illuminate\Support\Facades\Auth::user()->name, $selected_email->content) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </main>
            </div>
        @if(!isset($selected_email))
            <!-- Warning attivo apertura -->
                <div id="warning_open" tabindex="-1" aria-hidden="true"
                     class="hidden bg-black bg-opacity-50 overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
                    <input type="hidden" value="" id="warning_id-1">
                    <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
                        <!-- Modal content -->
                        <div class="relative rounded-lg shadow" style="background-color: #b80000;">
                            <!-- Modal header -->
                            <div class="flex justify-between items-start px-5 py-6 rounded-t border-b">
                                <h2 class="text-2xl font-bold text-white flex flex-row align-middle content-center">
                                    <div class="pr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-full w-8" viewBox="0 0 20 20"
                                             fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>Possible scam in the email</div>
                                </h2>
                            </div>
                            <!-- Modal body -->
                            <div class="p-6 space-y-6">
                                <p class="text-base leading-relaxed text-white" id="warning_text">
                                </p>
                            </div>
                            <hr class="bg-white mx-auto" style="width: 96%;">
                            <!-- Modal footer -->
                            <div class="flex items-center p-6 justify-between rounded-b">
                                <button id="button-advanced" type="button"
                                        class="text-white underline font-medium text-sm text-center">Advanced
                                </button>
                                <button id="button_hide_modal" type="button"
                                        class="bg-white hover:bg-gray-200 rounded-lg text-sm font-medium px-5 py-2.5"
                                        style="color: #b80000;">Go back to the safe zone
                                </button>
                            </div>
                            <div id="div-advanced" class="hidden text-white px-6 pb-6">
                                This email has a <b>fraudulent purpose</b> and may <b>steal your personal data</b>.
                                Click
                                <button id="warning_unsafe_link" class="text-white underline">here</button>
                                (not safe) to read it.
                            </div>
                        </div>
                    </div>
                </div>
        @else
            @if($selected_email->warning_type == "popup_link")
                <!-- Warning click link -->
                    <div id="warning_open" tabindex="-1" aria-hidden="true"
                         class="hidden bg-black bg-opacity-50 overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
                        <input type="hidden" value="" id="warning_id-2">
                        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
                            <!-- Modal content -->
                            <div class="relative rounded-lg shadow" style="background-color: #b80000;">
                                <!-- Modal header -->
                                <div class="flex justify-between items-start px-5 py-6 rounded-t border-b">
                                    <h2 class="text-2xl font-bold text-white flex flex-row align-middle content-center">
                                        <div class="pr-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-full w-8"
                                                 viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                      clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>Possible scam site</div>
                                    </h2>
                                </div>
                                <!-- Modal body -->
                                <div class="p-6 space-y-6">
                                    <p class="text-base leading-relaxed text-white" id="warning_text">
                                        <!-- warning text variable here -->
                                        Your personal data <b>may be stolen</b>. This site has been labeled as <b>highly
                                            dangerous</b>.
                                        We recommend that you <b>return to the safe zone</b>.
                                    </p>
                                </div>
                                <hr class="bg-white mx-auto" style="width: 96%;">
                                <!-- Modal footer -->
                                <div class="flex items-center p-6 justify-between rounded-b">
                                    <button id="button-advanced" type="button"
                                            class="text-white underline font-medium text-sm text-center">Advanced
                                    </button>
                                    <button id="button_hide_modal" type="button"
                                            class="bg-white hover:bg-gray-200 rounded-lg text-sm font-medium px-5 py-2.5"
                                            style="color: #b80000;">Go back to the safe zone
                                    </button>
                                </div>
                                <div id="div-advanced" class="hidden text-white px-6 pb-6">
                                    This site is a <b>scam</b> with the purpose of <b>stealing your personal data</b>.
                                    You can proceed from <a
                                        id="warning_unsafe_link" href="{{ route('next_step', $selected_email->id) }}"
                                        target="_blank"
                                        class="text-white underline">here</a> on <span id="warning_link"
                                                                                       class="font-bold"></span>.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>


            @if(isset($selected_email))
                @if($selected_email->warning_type == null || $selected_email->warning_type == "popup_email")
                $("#email_content").find('a').each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        $.ajax({
                            url: ("{{ route('warning_log') }}?email_id={{$selected_email->id}}&warning_type={{$selected_email->warning_type}}&msg=Cliccato link nella mail&url=" + window.location.href).replace(/%20/g, '+'),
                            type: 'GET',
                            dataType: 'json',
                            complete: function (data) {
                                window.location.href = '{{route('next_step') . '/' . $selected_email->id}}';
                            },
                            async: false
                        });
                    });
                });
                $('a').not("#email_content *").each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        window.location.href = '{{ route('next_step', $selected_email->id) }}?nolink';
                    });
                });
            @endif
            @if($selected_email->warning_type == "popup_link")
                $("#email_content").find('a').each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        let url = new URL($(this).attr('href'));
                        open_warning(url.hostname, {{ $selected_email->id }}, "{!! $selected_email->$random_warning_explanation !!}");
                    });
                });
                $('a').not("#email_content *").each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        window.location.href = '{{ route('next_step', $selected_email->id) }}?nolink';
                    });
                });
            @elseif($selected_email->warning_type == "browser_native")
                $("#email_content").find('a').each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        let url = new URL($(this).attr('href'));
                        window.location.href = '{{ route('warning_browser') }}?url=' + encodeURI(url) + '&backurl=' + encodeURI('{{ url('/nextstep', $selected_email->id)  }}') + '&email_id={{ $selected_email->id }}';
                    });
                });
                $('a').not("#email_content *").each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        window.location.href = '{{ route('next_step', $selected_email->id) }}?nolink';
                    });
                });
            @elseif($selected_email->warning_type == "base_passive")
                banner = $("#passive_banner"); // TODO implement passive banners (if needed)
                banner.show();
                banner.innerHTML = {{$selected_email->$random_warning_explanation}}
            @elseif($selected_email->warning_type == "tooltip")
                let message_sent = [];
                $(".tooltip").mouseover(function () {
                    if (!message_sent.includes($(this).attr('data'))) {
                        message_sent.push($(this).attr('data'));
                        $.ajax({
                            url: ("{{ route('warning_log') }}?email_id={{ $selected_email->id }}&warning_type=tooltip&msg=Tooltip " + $(this).attr("data") + " mostrato&url=" + window.location.href).replace(/%20/g, '+'),
                            type: 'GET',
                            dataType: 'json'
                        });
                    }
                });
                $("#phishing_link, #tooltip_link").on("click", () => {
                    $.ajax({
                        url: ("{{ route('warning_log') }}?email_id={{$selected_email->id}}&warning_type=tooltip&msg=Cliccato link nella tooltip&url=" + window.location.href).replace(/%20/g, '+'),
                        type: 'GET',
                        dataType: 'json',
                        complete: function (data) {
                            window.location.href = '{{route('next_step') . '/' . $selected_email->id}}';
                        },
                        async: false
                    });
                });
                $('a').not("#email_content *").each(function (e) {
                    $(this).on('click', (e) => {
                        e.preventDefault();
                        window.location.href = '{{ route('next_step', $selected_email->id) }}?nolink';
                    });
                });
                @endif
            @endif

            const targetEl = document.getElementById('warning_open');
            const modal = new Modal(targetEl);

            function open_warning(url, email_id, warning_text) {
                $("#warning_text").html(warning_text);
                @if(!isset($selected_email))
                    const warning_type = "popup_email";
                @else
                    @if($selected_email->warning_type == "popup_link")
                        const warning_type = "popup_link";
                        $("#warning_link").text(url);
                    @endif
                @endif
                $.ajax({
                    url: ("{{ route('warning_log') }}?email_id=" + email_id + "&warning_type=" + warning_type + "&msg=Warning mostrato&url=" + window.location.href).replace(/%20/g, '+'),
                    type: 'GET',
                    dataType: 'json'
                });
                $("#warning_unsafe_link").on('click', (e) => {
                    e.preventDefault();
                    $.ajax({
                        url: ("{{ route('warning_log') }}?email_id=" + email_id + "&warning_type=" + warning_type + "&msg=Warning bypassato&url=" + window.location.href).replace(/%20/g, '+'),
                        type: 'GET',
                        dataType: 'json',
                        complete: function (data) {
                            @if(\Illuminate\Support\Facades\Auth::user()->warning_type == 'popup_email')
                                window.location.href = '{{route('show')}}/inbox/' + email_id;
                            @else
                                window.location.href = '{{route('next_step')}}/' + email_id;
                            @endif
                        },
                        async: false
                    });
                });
                $("#button-advanced").on('click', () => {
                    if ($("#div-advanced").is(":hidden")) {
                        $("#button-advanced").text("Hide advanced");
                        $("#div-advanced").show();
                        $.ajax({
                            url: ("{{ route('warning_log') }}?email_id=" + email_id + "&warning_type=" + warning_type + "&msg=Cliccato pulsante 'Advanced'&url=" + window.location.href).replace(/%20/g, '+'),
                            type: 'GET',
                            dataType: 'json'
                        });
                    } else {
                        $("#button-advanced").text("Advanced");
                        $("#div-advanced").hide();
                        $.ajax({
                            url: ("{{ route('warning_log') }}?email_id=" + email_id + "&warning_type=" + warning_type + "&msg=Cliccato pulsante 'Hide advanced'&url=" + window.location.href).replace(/%20/g, '+'),
                            type: 'GET',
                            dataType: 'json'
                        });
                    }
                });
                $("#button_hide_modal").on('click', () => {
                    $.ajax({
                        url: ("{{ route('warning_log') }}?email_id=" + email_id + "&warning_type=" + warning_type + "&msg=Cliccato pulsante 'Go back to the safe zone'&url=" + window.location.href).replace(/%20/g, '+'),
                        type: 'GET',
                        dataType: 'json'
                    });
                    $("#button-advanced").text("Advanced");
                    $("#div-advanced").hide();
                    $("#button_hide_modal").off("click");
                    $("#button-advanced").off("click");
                    $("#warning_unsafe_link").off("click");
                    window.location.href = '{{route('next_step')}}/' + email_id + '?nolink';
                    modal.hide();
                });
                modal.show();
            }
        </script>
    </x-slot>
</x-app-layout>
