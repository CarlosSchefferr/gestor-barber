<nav x-data="{ open: false, adminOpen: false, userOpen: false }" class="navbar-main sticky top-0 z-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo e Menu Principal -->
            <div class="flex items-center gap-2 lg:gap-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center transition hover:opacity-90">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto">
                </a>

                <!-- Separador -->
                <div class="hidden h-6 w-px bg-zinc-700 lg:block"></div>

                @auth
                    <!-- Menu Desktop -->
                    <div class="hidden items-center gap-1 lg:flex">
                        <a href="{{ route('dashboard') }}" class="navbar-link {{ request()->routeIs('dashboard') ? 'navbar-link-active' : '' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('agendamentos.index') }}" class="navbar-link {{ request()->routeIs('agendamentos.*') ? 'navbar-link-active' : '' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Agenda</span>
                        </a>

                        <a href="{{ route('clientes.index') }}" class="navbar-link {{ request()->routeIs('clientes.*') ? 'navbar-link-active' : '' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Clientes</span>
                        </a>

                        @if(Auth::user()->isOwner())
                            <!-- Dropdown Administracao -->
                            <div class="relative">
                                <button @click="adminOpen = !adminOpen" class="navbar-link {{ request()->routeIs('financeiro.*') || request()->routeIs('admin.*') ? 'navbar-link-active' : '' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>Administracao</span>
                                    <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': adminOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="adminOpen"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-1"
                                     @click.away="adminOpen = false"
                                     class="navbar-dropdown left-0 mt-2 w-56"
                                     style="display: none;">
                                    <div class="p-2">
                                        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Gestao</p>

                                        <a href="{{ route('financeiro.index') }}" class="navbar-dropdown-item {{ request()->routeIs('financeiro.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-zinc-700">Financeiro</span>
                                        </a>

                                        <a href="{{ route('admin.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.index') || request()->routeIs('admin.create') || request()->routeIs('admin.edit') || request()->routeIs('admin.show') ? 'navbar-dropdown-item-active' : '' }}">
                                            <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-zinc-700">Usuarios</span>
                                        </a>
                                    </div>

                                    <div class="border-t border-zinc-100 p-2">
                                        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Catalogo</p>

                                        <a href="{{ route('admin.services.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.services.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-zinc-700">Servicos</span>
                                        </a>

                                        <a href="{{ route('admin.products.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.products.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-zinc-700">Produtos</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Lado Direito -->
            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="navbar-btn-secondary">
                        Entrar
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="navbar-btn-primary">
                            Registrar
                        </a>
                    @endif
                @endguest

                @auth
                    <!-- User Menu -->
                    <div class="relative">
                        <button @click="userOpen = !userOpen" class="navbar-user-btn">
                            <div class="flex items-center gap-3">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-9 w-9 rounded-xl object-cover ring-2 ring-zinc-700" alt="avatar">
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-barber-500 to-barber-600 text-sm font-bold text-white ring-2 ring-zinc-700">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="hidden text-left sm:block">
                                    <p class="text-sm font-semibold text-zinc-100">{{ Str::limit(Auth::user()->name, 15) }}</p>
                                    <p class="text-xs text-zinc-400">{{ Auth::user()->isOwner() ? 'Proprietario' : 'Barbeiro' }}</p>
                                </div>
                                <svg class="h-4 w-4 text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>

                        <div x-show="userOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             @click.away="userOpen = false"
                             class="navbar-dropdown right-0 mt-2 w-64"
                             style="display: none;">

                            <!-- User Info Header -->
                            <div class="border-b border-zinc-100 p-4">
                                <div class="flex items-center gap-3">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-12 w-12 rounded-xl object-cover" alt="avatar">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-barber-500 to-barber-600 text-lg font-bold text-white">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex-1 overflow-hidden">
                                        <p class="truncate text-sm font-bold text-zinc-900">{{ Auth::user()->name }}</p>
                                        <p class="truncate text-xs text-zinc-500">{{ Auth::user()->email }}</p>
                                        @if(Auth::user()->isOwner())
                                            <span class="mt-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">
                                                Proprietario
                                            </span>
                                        @else
                                            <span class="mt-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                Barbeiro
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Menu Items -->
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="navbar-dropdown-item">
                                    <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-zinc-700">Meu perfil</span>
                                </a>

                                <a href="{{ route('profile.settings') }}" class="navbar-dropdown-item">
                                    <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-zinc-700">Configuracoes</span>
                                </a>
                            </div>

                            <!-- Logout -->
                            <div class="border-t border-zinc-100 p-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="navbar-dropdown-item-danger w-full">
                                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-red-600">Sair</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="open = !open" class="navbar-mobile-btn lg:hidden" aria-label="Abrir menu">
                        <svg x-show="!open" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="open" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endauth
            </div>
        </div>

        <!-- Mobile Menu -->
        @auth
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="border-t border-zinc-700/50 py-4 lg:hidden"
                 style="display: none;">

                <div class="space-y-1">
                    <!-- Menu Items -->
                    <a href="{{ route('dashboard') }}" class="navbar-mobile-link {{ request()->routeIs('dashboard') ? 'navbar-mobile-link-active' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('agendamentos.index') }}" class="navbar-mobile-link {{ request()->routeIs('agendamentos.*') ? 'navbar-mobile-link-active' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Agenda</span>
                    </a>

                    <a href="{{ route('clientes.index') }}" class="navbar-mobile-link {{ request()->routeIs('clientes.*') ? 'navbar-mobile-link-active' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Clientes</span>
                    </a>

                    @if(Auth::user()->isOwner())
                        <div class="my-3 border-t border-zinc-700/50"></div>
                        <p class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-zinc-500">Administracao</p>

                        <a href="{{ route('financeiro.index') }}" class="navbar-mobile-link {{ request()->routeIs('financeiro.*') ? 'navbar-mobile-link-active' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Financeiro</span>
                        </a>

                        <a href="{{ route('admin.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.index') ? 'navbar-mobile-link-active' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Usuarios</span>
                        </a>

                        <a href="{{ route('admin.services.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.services.*') ? 'navbar-mobile-link-active' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                            </svg>
                            <span>Servicos</span>
                        </a>

                        <a href="{{ route('admin.products.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.products.*') ? 'navbar-mobile-link-active' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span>Produtos</span>
                        </a>
                    @endif
                </div>
            </div>
        @endauth
    </div>
</nav>
