<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="text-gray-900 antialiased" style="font-family: 'Courier Prime', monospace;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background-color: #FFEFF9;">
            <div>
                <a href="/">
                    <img
                        src="{{ asset('images/logo-pure-glossy.png') }}"
                        alt="Pure Glossy"
                        class="w-64 h-auto object-contain"
                    />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 shadow-md overflow-hidden rounded-3xl"
                style="background-color: #FFF9FC; border: 1px solid #F38ECA; box-shadow: 0 8px 25px rgba(243, 142, 202, 0.15);">

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
