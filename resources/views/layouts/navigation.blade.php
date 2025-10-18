<nav x-data="{ open: false }" class="bg-gradient-to-r from-barber-900 via-barber-black to-black text-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="text-lg font-semibold tracking-wide">Gestor Barber</div>
                </a>

                @auth
                    <div class="hidden lg:flex items-center gap-4">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-barber-800 {{ request()->routeIs('dashboard') ? 'bg-barber-800' : '' }}">Painel</a>
                        <a href="{{ route('agendamentos.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-barber-800 {{ request()->routeIs('agendamentos.*') ? 'bg-barber-800' : '' }}">Agendamentos</a>
                        <a href="{{ url('/clientes') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-barber-800">Clientes</a>
                        <a href="{{ url('/financeiro') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-barber-800">Financeiro</a>
                    </div>
                @endauth
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <div class="hidden sm:flex items-center">
                        <a href="{{ route('agendamentos.create') }}" class="px-3 py-2 rounded-md bg-barber-500 hover:bg-barber-600 text-sm font-medium">Novo Agendamento</a>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-transparent hover:text-barber-300 focus:outline-none transition ease-in-out duration-150">
                                    <div class="mr-2">{{ Auth::user()->name }}</div>
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-8 h-8 rounded-full object-cover" alt="avatar">
                                    @else
                                        <div class="w-8 h-8 bg-barber-500 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                                        </div>
                                    @endif
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    Perfil
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        Sair
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth
                @guest
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-barber-800">Entrar</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-3 py-2 rounded-md bg-barber-500 hover:bg-barber-600 text-sm font-medium">Registrar</a>
                        @endif
                    </div>
                @endguest

                <!-- Mobile menu button -->
                @auth
                    <div class="-mr-2 flex items-center lg:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-barber-300 hover:bg-barber-800 focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    @auth
        <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
            <div class="pt-2 pb-3 space-y-1 px-4">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium">Painel</a>
                <a href="{{ route('agendamentos.index') }}" class="block px-3 py-2 rounded-md text-base font-medium">Agendamentos</a>
                <a href="{{ url('/clientes') }}" class="block px-3 py-2 rounded-md text-base font-medium">Clientes</a>
                <a href="{{ url('/financeiro') }}" class="block px-3 py-2 rounded-md text-base font-medium">Financeiro</a>
            </div>

            <div class="pt-4 pb-1 border-t border-barber-800 px-4">
                <div class="font-medium text-base">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-barber-200">{{ Auth::user()->email }}</div>

                <div class="mt-3 space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="w-full text-left px-3 py-2 rounded-md">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</nav>
