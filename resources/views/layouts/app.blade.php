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
                /*border-bottom: 1px dotted black;*/
                z-index: 1;
            }

            .tooltip .tooltiptext {
                visibility: hidden;
                border: solid #b80000 2px;
                color: #b80000;
                background-color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 6px 10px;
                position: absolute;
                z-index: 1;
            }

            .tooltip-balloon {
                width: max-content;
                max-width:35rem;
            }

            .tooltip .tooltiptext::after {
                content: "";
                position: absolute;
                bottom: 100%;
                left: 8%;
                margin-left: -5px;
                border-width: 10px;
                border-style: solid;
                border-color: transparent transparent #b80000 transparent;
                z-index: 1;
            }

            .visible {
                visibility: visible !important;
            }

            #tooltip_balloon{
                top: 110%;
                left: 25%;
                position: absolute;
                display: flex;
                align-items: center;
            }

            #tooltip_button{
                top: -30px;
                width: 550px;
                left: 102%;
            }

            .phishing_btn {
                color: #ffffff;
                text-decoration: none;
                display: block;
                padding: 14px 30px 15px;
                pointer-events: none;
                cursor: not-allowed;
            }
            .phishing_link {
                color: #b80000;
                text-decoration: none;
                margin:auto;
                display: block;
                cursor: not-allowed;
            }
            datalist {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                margin-bottom: 2rem;
            }

            @-webkit-keyframes shrink {
                0% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-200%) translateX(-120%) scale(0);
                    opacity: 0;
                }
            }
            @keyframes shrink {
                0% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-200%) translateX(-100%) scale(0);
                    opacity: 0;
                }
            }

            .modal-shrink {
                -webkit-animation: shrink 1.4s;
                animation: shrink 1.4s;
                animation-iteration-count:1;
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
