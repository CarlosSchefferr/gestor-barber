@auth
    @php
        $useSidebarLayout = auth()->user()->navigation_layout === 'sidebar';
    @endphp
@endauth

@if(!auth()->check() || empty($useSidebarLayout) || !$useSidebarLayout)
<nav x-data="{ open: false, adminOpen: false, userOpen: false }" class="navbar-main sticky top-0 z-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-2 lg:gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center transition hover:opacity-90">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto">
                </a>
                <div class="hidden h-6 w-px bg-zinc-700 lg:block"></div>

                @auth
                    <div class="hidden items-center gap-1 lg:flex">
                        <a href="{{ route('dashboard') }}" class="navbar-link {{ request()->routeIs('dashboard') ? 'navbar-link-active' : '' }}"><span>Dashboard</span></a>
                        <a href="{{ route('agendamentos.index') }}" class="navbar-link {{ request()->routeIs('agendamentos.*') ? 'navbar-link-active' : '' }}"><span>Agenda</span></a>
                        <div class="relative">
                            <button @click="adminOpen = !adminOpen" class="navbar-link {{ request()->routeIs('admin.*') || request()->routeIs('agenda.config.*') || request()->routeIs('clientes.*') || request()->routeIs('financeiro.*') ? 'navbar-link-active' : '' }}">
                                <i class="bi bi-person-fill-gear text-base" aria-hidden="true"></i>
                                <span>Administrativo</span>
                                <svg class="h-4 w-4 transition" :class="adminOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="adminOpen" @click.away="adminOpen = false" class="navbar-dropdown left-0 mt-2 w-64" style="display: none;">
                                <div class="p-2">
                                    <a href="{{ route('clientes.index') }}" class="navbar-dropdown-item {{ request()->routeIs('clientes.*') ? 'navbar-dropdown-item-active' : '' }}">
                                        <i class="bi bi-people-fill text-zinc-500 text-base" aria-hidden="true"></i>
                                        <span class="text-sm font-medium text-zinc-700">Clientes</span>
                                    </a>

                                    @if(Auth::user()->isOwner())
                                        <a href="{{ route('financeiro.index') }}" class="navbar-dropdown-item {{ request()->routeIs('financeiro.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <i class="bi bi-cash-coin text-zinc-500 text-base" aria-hidden="true"></i>
                                            <span class="text-sm font-medium text-zinc-700">Financeiro</span>
                                        </a>
                                        <a href="{{ route('admin.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.index') || request()->routeIs('admin.show') || request()->routeIs('admin.create') || request()->routeIs('admin.edit') ? 'navbar-dropdown-item-active' : '' }}">
                                            <i class="bi bi-person-fill-gear text-zinc-500 text-base" aria-hidden="true"></i>
                                            <span class="text-sm font-medium text-zinc-700">Usuários</span>
                                        </a>
                                        <a href="{{ route('admin.products.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.products.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <i class="bi bi-box-seam text-zinc-500 text-base" aria-hidden="true"></i>
                                            <span class="text-sm font-medium text-zinc-700">Produtos</span>
                                        </a>
                                        <a href="{{ route('admin.services.index') }}" class="navbar-dropdown-item {{ request()->routeIs('admin.services.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <i class="bi bi-tools text-zinc-500 text-base" aria-hidden="true"></i>
                                            <span class="text-sm font-medium text-zinc-700">Serviços</span>
                                        </a>
                                        <a href="{{ route('agenda.config.index') }}" class="navbar-dropdown-item {{ request()->routeIs('agenda.config.*') ? 'navbar-dropdown-item-active' : '' }}">
                                            <i class="bi bi-sliders2-vertical text-zinc-500 text-base" aria-hidden="true"></i>
                                            <span class="text-sm font-medium text-zinc-700">Configurações da agenda</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="navbar-btn-secondary">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="navbar-btn-primary">Registrar</a>
                    @endif
                @endguest

                @auth
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
                            </div>
                        </button>

                        <div x-show="userOpen" @click.away="userOpen = false" class="navbar-dropdown right-0 mt-2 w-64" style="display: none;">
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="navbar-dropdown-item"><span class="text-sm font-medium text-zinc-700">Meu perfil</span></a>
                                <a href="{{ route('profile.settings') }}" class="navbar-dropdown-item"><span class="text-sm font-medium text-zinc-700">Configuracoes</span></a>
                            </div>
                            <div class="border-t border-zinc-100 p-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="navbar-dropdown-item-danger w-full">
                                        <span class="text-sm font-medium text-red-600">Sair</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

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

        @auth
            <div x-show="open" class="border-t border-zinc-700/50 py-4 lg:hidden" style="display: none;">
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="navbar-mobile-link {{ request()->routeIs('dashboard') ? 'navbar-mobile-link-active' : '' }}"><span>Dashboard</span></a>
                    <a href="{{ route('agendamentos.index') }}" class="navbar-mobile-link {{ request()->routeIs('agendamentos.*') ? 'navbar-mobile-link-active' : '' }}"><span>Agenda</span></a>
                    <div class="mt-2 border-t border-zinc-700/50 pt-2">
                        <p class="px-4 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-500">Administrativo</p>
                        <a href="{{ route('clientes.index') }}" class="navbar-mobile-link {{ request()->routeIs('clientes.*') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-people-fill text-base" aria-hidden="true"></i><span>Clientes</span></a>
                        @if(Auth::user()->isOwner())
                            <a href="{{ route('financeiro.index') }}" class="navbar-mobile-link {{ request()->routeIs('financeiro.*') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-cash-coin text-base" aria-hidden="true"></i><span>Financeiro</span></a>
                            <a href="{{ route('admin.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.index') || request()->routeIs('admin.show') || request()->routeIs('admin.create') || request()->routeIs('admin.edit') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-person-fill-gear text-base" aria-hidden="true"></i><span>Usuários</span></a>
                            <a href="{{ route('admin.products.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.products.*') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-box-seam text-base" aria-hidden="true"></i><span>Produtos</span></a>
                            <a href="{{ route('admin.services.index') }}" class="navbar-mobile-link {{ request()->routeIs('admin.services.*') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-tools text-base" aria-hidden="true"></i><span>Serviços</span></a>
                            <a href="{{ route('agenda.config.index') }}" class="navbar-mobile-link {{ request()->routeIs('agenda.config.*') ? 'navbar-mobile-link-active' : '' }}"><i class="bi bi-sliders2-vertical text-base" aria-hidden="true"></i><span>Configurações da agenda</span></a>
                        @endif
                    </div>
                </div>
            </div>
        @endauth
    </div>
</nav>
@else
<div
    x-data="{
        mobileOpen: false,
        userOpen: false,
        collapsed: {{ auth()->user()->sidebar_collapsed ? 'true' : 'false' }},
        tooltip: { show: false, text: '', x: 0, y: 0 },
        init() {
            const saved = localStorage.getItem('sidebar-collapsed');
            if (saved !== null) {
                this.collapsed = saved === '1';
            }
            this.syncSidebarWidth();
        },
        syncSidebarWidth() {
            document.documentElement.style.setProperty('--sidebar-width', this.collapsed ? '5.5rem' : '17.5rem');
            localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
        },
        toggleSidebar() {
            this.collapsed = !this.collapsed;
            this.syncSidebarWidth();
        },
        showTip(text, el, evt) {
            if (!this.collapsed) return;
            this.tooltip.text = text;
            const aside = el.closest('aside');
            if (aside) {
                const r = aside.getBoundingClientRect();
                this.tooltip.x = r.right + 12;
            } else {
                this.tooltip.x = (evt?.clientX ?? 0) + 12;
            }
            this.tooltip.y = (evt?.clientY ?? 0);
            this.tooltip.show = true;
        },
        moveTip(evt) {
            if (!this.tooltip.show) return;
            this.tooltip.y = evt.clientY;
        },
        hideTip() {
            this.tooltip.show = false;
        }
    }"
    class="sidebar-layout"
>
    <div class="sidebar-mobile-topbar md:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
            <span class="text-sm font-semibold tracking-wide text-zinc-100">Gestor Barber</span>
        </a>
        <button type="button" @click="mobileOpen = true" class="rounded-xl border border-zinc-700 bg-zinc-900/70 p-2 text-zinc-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div x-show="mobileOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 md:hidden" @click="mobileOpen = false" style="display: none;"></div>

    <aside class="sidebar-modern fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300"
           :class="[
               collapsed ? 'w-[5.5rem]' : 'w-72',
               mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
           ]">
        <div class="sidebar-brand border-b border-zinc-800/80 px-4 py-5" :class="collapsed ? 'px-3' : 'px-5'">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo do sistema" class="h-14 w-14 shrink-0 rounded-xl bg-white/95 p-1.5 shadow-sm object-contain">
            </a>
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4">
            <nav class="space-y-1.5">
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}"
                   :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                   @mouseenter="showTip('Dashboard', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                >
                    <i class="bi bi-house-door-fill text-lg shrink-0" aria-hidden="true"></i>
                    <span x-show="!collapsed" x-transition>Dashboard</span>
                </a>
                <a href="{{ route('agendamentos.index') }}"
                   class="sidebar-link {{ request()->routeIs('agendamentos.*') ? 'sidebar-link-active' : '' }}"
                   :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                   @mouseenter="showTip('Agenda', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                >
                    <i class="bi bi-calendar3 text-lg shrink-0" aria-hidden="true"></i>
                    <span x-show="!collapsed" x-transition>Agenda</span>
                </a>
                <a href="{{ route('clientes.index') }}"
                   class="sidebar-link {{ request()->routeIs('clientes.*') ? 'sidebar-link-active' : '' }}"
                   :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                   @mouseenter="showTip('Clientes', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                >
                    <i class="bi bi-people-fill text-lg shrink-0" aria-hidden="true"></i>
                    <span x-show="!collapsed" x-transition>Clientes</span>
                </a>
                @if(auth()->user()->isOwner())
                    <a href="{{ route('financeiro.index') }}"
                       class="sidebar-link {{ request()->routeIs('financeiro.*') ? 'sidebar-link-active' : '' }}"
                       :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                       @mouseenter="showTip('Financeiro', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-cash-coin text-lg shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Financeiro</span>
                    </a>
                    <a href="{{ route('admin.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.index') || request()->routeIs('admin.show') || request()->routeIs('admin.create') || request()->routeIs('admin.edit') ? 'sidebar-link-active' : '' }}"
                       :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                       @mouseenter="showTip('Usuários', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-person-fill-gear text-lg shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Usuários</span>
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'sidebar-link-active' : '' }}"
                       :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                       @mouseenter="showTip('Produtos', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-box-seam text-lg shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Produtos</span>
                    </a>
                    <a href="{{ route('admin.services.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'sidebar-link-active' : '' }}"
                       :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                       @mouseenter="showTip('Serviços', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-tools text-lg shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Serviços</span>
                    </a>
                    <a href="{{ route('agenda.config.index') }}"
                       class="sidebar-link {{ request()->routeIs('agenda.config.*') ? 'sidebar-link-active' : '' }}"
                       :class="collapsed ? 'justify-center px-2' : 'justify-start px-3.5'"
                       @mouseenter="showTip('Configurações da agenda', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-sliders2-vertical text-lg shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Configurações da agenda</span>
                    </a>
                @endif
            </nav>
        </div>

        <div class="border-t border-zinc-800/80 p-3">
            <div class="mb-2 flex items-center gap-3 rounded-2xl bg-zinc-900/80 p-2.5">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-10 w-10 rounded-xl object-cover" alt="avatar">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-barber-500 to-barber-600 text-sm font-bold text-white">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <div x-show="!collapsed" x-transition class="min-w-0">
                    <p class="truncate text-sm font-semibold text-zinc-100">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-zinc-400">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="grid gap-2">
                <a href="{{ route('profile.settings') }}"
                   class="sidebar-foot-btn"
                   :class="collapsed ? 'justify-center px-2' : 'justify-start px-3'"
                   @mouseenter="showTip('Configuracoes', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                >
                    <i class="bi bi-gear-fill text-base shrink-0" aria-hidden="true"></i>
                    <span x-show="!collapsed" x-transition>Configuracoes</span>
                </a>
                <button type="button" @click="toggleSidebar()"
                        class="sidebar-foot-btn"
                        :class="collapsed ? 'justify-center px-2' : 'justify-start px-3'"
                        @mouseenter="showTip(collapsed ? 'Expandir menu' : 'Minimizar menu', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                >
                    <span class="flex items-center gap-2">
                        <i x-show="!collapsed" x-cloak class="bi bi-layout-sidebar-inset-reverse text-base shrink-0 transition" aria-hidden="true"></i>
                        <i x-show="collapsed" x-cloak class="bi bi-layout-sidebar-inset text-base shrink-0 transition" aria-hidden="true"></i>
                        <span x-show="!collapsed">Minimizar menu</span>
                    </span>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="sidebar-foot-btn sidebar-foot-btn-danger w-full"
                            :class="collapsed ? 'justify-center px-2' : 'justify-start px-3'"
                            @mouseenter="showTip('Sair', $el, $event)" @mousemove="moveTip($event)" @mouseleave="hideTip()"
                    >
                        <i class="bi bi-box-arrow-right text-base shrink-0" aria-hidden="true"></i>
                        <span x-show="!collapsed" x-transition>Sair</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Tooltip (fixo, não é cortado por overflow do menu) -->
        <div x-show="collapsed && tooltip.show"
             x-cloak
             class="fixed z-[9999] rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-xs font-semibold text-zinc-100 shadow-xl"
             :style="`left:${tooltip.x}px; top:${tooltip.y}px; transform: translateY(-50%);`"
        >
            <span x-text="tooltip.text"></span>
        </div>
    </aside>
</div>
@endif
