<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
    <nav class="bg-gray-50 dark:bg-gray-700">
        <div class="container flex flex-row-reverse flex-wrap max-w-screen-xl py-3 mx-auto md:px-6" style="flex-direction: row-reverse">
            <div class="flex items-center">
                <ul class="flex flex-row mt-0 mr-6 space-x-8 text-sm font-medium">
                    <li>
                        <a href="{{route('login')}}" class="text-gray-900 dark:text-white hover:underline" aria-current="page">Accedi</a>
                    </li>
                    <li>
                        <a href="{{route('register')}}" class="text-gray-900 dark:text-white hover:underline">Registrati</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
    </body>
</html>
