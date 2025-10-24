<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('images/logo.png') }}">
        <meta name="msapplication-TileColor" content="#c96f1f">
        <meta name="msapplication-TileImage" content="{{ asset('images/logo.png') }}">
        <meta name="theme-color" content="#c96f1f">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50" style="background-image: url('{{ asset('images/fundo.png') }}'); background-size: cover; background-position: center;">

            <div class="w-full sm:max-w-md mt-4 px-6 py-6 bg-white text-black shadow-sm overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
