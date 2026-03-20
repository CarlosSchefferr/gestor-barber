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

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100">
            <div class="flex min-h-screen flex-col lg:flex-row">
                <aside
                    class="relative hidden overflow-hidden lg:block lg:w-[48%] xl:w-[46%]"
                    x-data="{
                        currentImage: 0,
                        images: [
                            '{{ asset('images/fundo.png') }}',
                            '{{ asset('images/fundo-2.jpg') }}',
                            '{{ asset('images/fundo-3.jpg') }}',
                            '{{ asset('images/fundo-4.png') }}',
                            '{{ asset('images/fundo-5.jpg') }}',
                            '{{ asset('images/fundo-6.jpg') }}'
                        ]
                    }"
                    x-init="setInterval(() => { currentImage = (currentImage + 1) % images.length }, 5000)"
                >
                    <template x-for="(image, index) in images" :key="index">
                        <div
                            class="absolute inset-0 bg-cover bg-center transition-opacity duration-700"
                            :style="'background-image: url(' + image + ')'"
                            :class="currentImage === index ? 'opacity-100' : 'opacity-0'"
                        ></div>
                    </template>
                    <div class="absolute inset-0 bg-black/20"></div>
                </aside>

                <main class="relative z-10 flex flex-1 items-center justify-center bg-white px-4 py-10 sm:px-8 lg:-ml-10 lg:rounded-l-[2.5rem] lg:px-12 lg:shadow-[-20px_0_40px_-28px_rgba(0,0,0,0.45)]">
                    <div class="w-full max-w-md">
                        <div class="mb-8 flex justify-center">
                            <a href="{{ url('/') }}" aria-label="Ir para o inicio">
                                <img
                                    src="{{ asset('images/logo.png') }}"
                                    alt="GestorBarber"
                                    class="h-20 w-auto"
                                >
                            </a>
                        </div>
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
