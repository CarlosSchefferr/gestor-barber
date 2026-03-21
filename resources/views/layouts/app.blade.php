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

        <style>
            /* Tooltip for icon-action component */
            .icon-action { position: relative; }
            .icon-action::after {
                content: attr(data-tooltip);
                position: absolute;
                bottom: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%) translateY(6px);
                background: rgba(0,0,0,0.75);
                color: #fff;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 12px;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.12s ease, transform 0.12s ease;
                z-index: 50;
            }
            .icon-action:hover::after, .icon-action:focus::after {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="v2-shell font-sans antialiased">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="v2-container py-6">
                    <div class="v2-panel px-6 py-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="v2-main flex-1">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

            <!-- Footer -->
            <footer class="border-t border-zinc-200 bg-white/80 backdrop-blur-sm">
                <div class="v2-container py-5">
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:justify-between">
                        <div class="flex items-center gap-5 text-sm text-zinc-500">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100">
                                    <svg class="h-3.5 w-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span id="brasilia-time" class="font-medium">{{ now()->setTimezone('America/Sao_Paulo')->format('H:i:s') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100">
                                    <svg class="h-3.5 w-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">{{ now()->setTimezone('America/Sao_Paulo')->format('d/m/Y') }}</span>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </footer>

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



                setInterval(updateBrasiliaTime, 1000);
                updateBrasiliaTime();
            </script>
        </div>
        @stack('scripts')
    </body>
</html>
