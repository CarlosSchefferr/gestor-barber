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

        @stack('styles')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 py-6 pb-16">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

            <!-- Footer -->
            <footer class="bg-gradient-to-r from-barber-900 via-barber-black to-black text-white mt-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <!-- Logo (Esquerda) -->
                        <div class="flex items-center mb-4 md:mb-0">
                            <span class="text-xl font-bold text-white">Gestor Barber</span>
                        </div>

                        <!-- Informações de Tempo e Localização (Direita) -->
                        <div class="flex items-center space-x-6 text-sm text-gray-400">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span id="brasilia-time">{{ now()->setTimezone('America/Sao_Paulo')->format('H:i:s') }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ now()->setTimezone('America/Sao_Paulo')->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span id="user-location">Carregando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>

            <!-- Script para atualizar o horário em tempo real e localização -->
            <script>
                function updateBrasiliaTime() {
                    const now = new Date();
                    const brasiliaTime = new Date(now.toLocaleString("en-US", {timeZone: "America/Sao_Paulo"}));
                    const timeString = brasiliaTime.toLocaleTimeString('pt-BR', {
                        hour12: false,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    document.getElementById('brasilia-time').textContent = timeString;
                }

                // Função para obter a localização do usuário (sem chamadas externas para evitar CORS)
                // Esta versão usa apenas a geolocalização do navegador para indicar presença,
                // mas não faz reverse-geocoding remoto. Evita bloqueios por CORS.
                function getUserLocation() {
                    const el = document.getElementById('user-location');
                    if (!el) return;

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                // Apenas indicar que a localização foi detectada localmente
                                el.textContent = 'Localização detectada';
                            },
                            function(error) {
                                console.log('Erro de geolocalização:', error);
                                el.textContent = 'Localização';
                            },
                            {
                                enableHighAccuracy: false,
                                timeout: 5000,
                                maximumAge: 300000 // 5 minutos
                            }
                        );
                    } else {
                        el.textContent = 'Localização';
                    }
                }

                // Atualizar horário a cada segundo
                setInterval(updateBrasiliaTime, 1000);
                updateBrasiliaTime();

                // Obter localização do usuário
                getUserLocation();
            </script>
        </div>
        @stack('scripts')
    </body>
</html>
