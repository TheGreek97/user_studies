<!DOCTYPE html>
<html :class="{'theme-dark': dark}" x-data="data()" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'E-Mail Client') }}</title>

        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"/>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .tooltip {
                position: relative;
                display: inline-block;
                border-bottom: 1px dotted black;
            }

            .tooltip .tooltiptext {
                visibility: hidden;
                width: 310px;
                background-color: #b80000;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 6px 10px;
                position: absolute;
                z-index: 1;
                top: -50px;
                left: 105%;
            }

            #tooltip_image{
                top: 40px;
                width: 460px;
                left: 103%;
            }

            #tooltip_dear{
                top: -30px;
                width: 518px;
            }

            #tooltip_link{
                top: -47px;
                width: 529px;
                left: 103%;
            }

            #tooltip_button{
                top: -30px;
                width: 550px;
                left: 102%;
            }
            #tooltip_grammar{
                top: -60px;
                width: 260px;
                left: 101%;
            }
            .tooltip:hover .tooltiptext {
                visibility: visible;
            }

            datalist {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                margin-bottom: 2rem;
            }
        </style>
        <!-- Styles -->
        @livewireStyles
        @livewireScripts
        <script src="{{url('./assets/js/init-alpine.js')}}"></script>
        <script src="https://unpkg.com/flowbite@1.5.1/dist/flowbite.js"></script>
        <script type="text/javascript">
            function disableBack() { window.history.forward(); }
            setTimeout("disableBack()", 0);
            window.onunload = function () { null };
            (function () {
                window.onpageshow = function(event) {
                    if (event.persisted) {
                        window.history.forward();
                    }
                };
            })();
        </script>
    <body class="font-sans antialiased">
        {{ $slot }}
    </body>
</html>
