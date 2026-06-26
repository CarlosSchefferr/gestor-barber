<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $agendaConfig->nome_barbearia }} - Agende seu Horário</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f9fafb;
        }

        /* ===== Header gradiente ===== */
        .barber-header {
            background: linear-gradient(90deg, #121a2a 50%, #303d56 100%);
        }

        /* ===== Carrossel ===== */
        .carousel-track {
            display: flex;
            gap: 26px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scroll-snap-type: x mandatory;
            padding-bottom: 4px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .carousel-track::-webkit-scrollbar { display: none; }
        .carousel-card { scroll-snap-align: start; flex: 0 0 235px; }

        .dot {
            width: 10px; height: 10px; border-radius: 9999px;
            background: #d4d4d8; cursor: pointer; transition: all .3s ease;
        }
        .dot.active { background: #155dfc; width: 22px; }

        /* ===== Chat estilo WhatsApp ===== */
        .chat-bg {
            background-color: #c9c9c9;
            background-image: url('/images/fundochat.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .chat-scroll { overflow-y: auto; scroll-behavior: smooth; }
        .chat-scroll::-webkit-scrollbar { width: 6px; }
        .chat-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 3px; }

        /* ===== Marquee de fotos (rolagem lateral automática) ===== */
        .marquee { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent); mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent); }
        .marquee-track { display: flex; gap: 14px; width: max-content; animation: marquee 32s linear infinite; }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) { .marquee-track { animation: none; } .marquee { overflow-x: auto; } }

        /* ===== Builder / cart ===== */
        .builder-scroll { max-height: 70vh; overflow-y: auto; }
        [x-cloak] { display: none !important; }

        .bubble-bot {
            background: #ffffff;
            border-radius: 0 10px 10px 10px;
            box-shadow: 0 1px 1px rgba(0,0,0,.08);
            animation: pop .25s ease-out;
        }
        .bubble-user {
            background: #dcf8c6;
            border-radius: 10px 0 10px 10px;
            box-shadow: 0 1px 1px rgba(0,0,0,.08);
            animation: pop .25s ease-out;
        }
        @keyframes pop { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body x-data="barbeariaApp({
        configUrl: @js(route('public.agendamento.config', $agendaConfig->public_token)),
        submitUrl: @js(route('public.agendamento.submit', $agendaConfig->public_token)),
        chatStartUrl: @js(route('public.chat.start', $agendaConfig->public_token)),
        chatMessageUrl: @js(route('public.chat.message', $agendaConfig->public_token)),
        chatCustomerUrl: @js(route('public.chat.proposal.customer', $agendaConfig->public_token)),
        chatConfirmUrl: @js(route('public.chat.confirm', $agendaConfig->public_token)),
        professionalsUrl: @js(route('public.agendamento.professionals', $agendaConfig->public_token)),
        datesUrl: @js(route('public.agendamento.dates', $agendaConfig->public_token)),
        timesUrl: @js(route('public.agendamento.times', $agendaConfig->public_token)),
        prepareUrl: @js(route('public.chat.prepare', $agendaConfig->public_token)),
     })">

    <div class="flex flex-col lg:flex-row min-h-screen w-full">

        {{-- ============================================================= --}}
        {{-- LADO ESQUERDO: Landing da barbearia                           --}}
        {{-- ============================================================= --}}
        <main class="flex-1 min-w-0 lg:h-screen lg:overflow-y-auto">

            {{-- Cabeçalho --}}
            <header class="barber-header px-8 py-6">
                <h1 class="text-[30px] font-bold text-white leading-tight" x-text="config.nome_barbearia || '{{ $agendaConfig->nome_barbearia }}'"></h1>
                <div class="mt-4 space-y-2 text-white/95">
                    <template x-if="config.endereco">
                        <p class="flex items-center gap-2 text-base">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                            <span x-text="config.endereco"></span>
                        </p>
                    </template>
                    <p class="flex items-center gap-2 text-base">
                        <svg class="w-[18px] h-[18px] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 11h-4V7h2v4h2v2z"/></svg>
                        <span x-text="horarioLabel"></span>
                    </p>
                </div>
            </header>

            {{-- Indicadores --}}
            <section class="px-8 py-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    {{-- Clientes atendidos --}}
                    <div class="bg-white border border-[#cccccc] rounded-[10px] p-5">
                        <div class="w-[42px] h-[42px] rounded-full bg-blue-500 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <p class="text-[24px] font-bold text-black leading-none" x-text="formatNumber(config.indicadores?.clientes_atendidos ?? 0)"></p>
                        <p class="text-[11px] text-[#484848] mt-2">Clientes atendidos</p>
                    </div>
                    {{-- Serviços executados --}}
                    <div class="bg-white border border-[#cccccc] rounded-[10px] p-5">
                        <div class="w-[42px] h-[42px] rounded-full bg-green-600 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3z"/></svg>
                        </div>
                        <p class="text-[24px] font-bold text-black leading-none" x-text="formatNumber(config.indicadores?.servicos_executados ?? 0)"></p>
                        <p class="text-[11px] text-[#484848] mt-2">Serviços executados</p>
                    </div>
                    {{-- Média de avaliações --}}
                    <div class="bg-white border border-[#cccccc] rounded-[10px] p-5">
                        <div class="w-[42px] h-[42px] rounded-full bg-yellow-500 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </div>
                        <p class="text-[24px] font-bold text-black leading-none" x-text="config.indicadores?.media_avaliacoes ? config.indicadores.media_avaliacoes.toFixed(1).replace('.', ',') : '—'"></p>
                        <p class="text-[11px] text-[#484848] mt-2">Média de avaliações</p>
                    </div>
                </div>
            </section>

            {{-- Abas --}}
            <nav class="px-8 flex items-center gap-8 border-b border-[#dfdfdf]">
                <template x-for="aba in abas" :key="aba.id">
                    <button @click="abaAtiva = aba.id; resetCarousel()"
                        class="relative py-4 text-base font-semibold transition"
                        :class="abaAtiva === aba.id ? 'text-[#155dfc]' : 'text-[#4a5565] hover:text-zinc-800'">
                        <span x-text="aba.label"></span>
                        <span x-show="abaAtiva === aba.id" class="absolute left-0 -bottom-px h-0.5 w-full bg-[#155dfc]"></span>
                    </button>
                </template>
            </nav>

            {{-- Conteúdo da aba --}}
            <section class="px-8 py-6">
                <div class="flex items-center justify-between gap-3 border-b-2 border-[#155dfc] pb-3">
                    <h2 class="text-[20px] sm:text-[24px] font-bold text-black truncate" x-text="tituloAba"></h2>
                </div>

                {{-- Loading --}}
                <div x-show="carregandoConfig" class="py-20 text-center text-zinc-400 text-sm">Carregando...</div>

                {{-- Lista vazia --}}
                <div x-show="!carregandoConfig && itensAba.length === 0" class="py-20 text-center text-zinc-400 text-sm" x-text="vazioLabel"></div>

                {{-- Card / carrossel (serviços e produtos) --}}
                <div x-show="!carregandoConfig && abaAtiva !== 'barbeiros' && itensAba.length > 0"
                    class="mt-6 bg-white border border-[#e8e8e8] rounded-[10px] p-6 relative">

                    <div class="carousel-track" x-ref="track" @scroll.debounce.100ms="updateDot()">
                        <template x-for="(item, idx) in itensAba" :key="abaAtiva + '-' + idx">
                            <div class="carousel-card">
                                {{-- ===== SERVIÇOS ===== --}}
                                <template x-if="abaAtiva === 'servicos'">
                                    <div class="rounded-[10px] overflow-hidden border border-[#8f8f8f] bg-white h-full flex flex-col">
                                        <div class="h-[210px] bg-gradient-to-br from-zinc-800 to-zinc-900 flex items-center justify-center relative overflow-hidden">
                                            <svg class="w-12 h-12 text-zinc-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2z"/></svg>
                                            <template x-if="imagemCard(item.imagem, idx)">
                                                <img :src="imagemCard(item.imagem, idx)" x-on:error="$el.style.display='none'" alt="" class="absolute inset-0 w-full h-full object-cover">
                                            </template>
                                        </div>
                                        <div class="p-4 flex flex-col flex-1">
                                            <h3 class="text-[16px] font-bold text-black" x-text="item.nome"></h3>
                                            <p class="text-[12px] text-[#484848] mt-1" x-text="item.duracao_label ? 'Duração: ' + item.duracao_label : ''"></p>
                                            <div class="mt-3 flex items-center justify-between">
                                                <span class="text-[20px] font-extrabold text-[#1538fc]" x-text="item.preco_label"></span>
                                                <button @click="abrirMontador(item.id, null)" class="inline-flex items-center gap-1 rounded-full bg-[#155dfc] text-white text-xs font-bold px-3 py-1.5 hover:bg-[#1148d8] transition" title="Agendar">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    Agendar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- ===== PRODUTOS ===== --}}
                                <template x-if="abaAtiva === 'produtos'">
                                    <div class="rounded-[10px] overflow-hidden border border-[#8f8f8f] bg-white h-full flex flex-col">
                                        <div class="h-[210px] bg-zinc-100 flex items-center justify-center relative overflow-hidden">
                                            <svg class="w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <template x-if="item.imagem">
                                                <img :src="item.imagem" x-on:error="$el.style.display='none'" alt="" class="absolute inset-0 w-full h-full object-cover">
                                            </template>
                                        </div>
                                        <div class="p-4 flex flex-col flex-1">
                                            <h3 class="text-[16px] font-bold text-black" x-text="item.nome"></h3>
                                            <p class="text-[12px] text-[#484848] mt-1" x-text="item.marca || ''"></p>
                                            <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                                <span class="text-[20px] font-extrabold text-[#1538fc]" x-text="item.preco_label"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- ===== BARBEIROS ===== --}}
                                <template x-if="abaAtiva === 'barbeiros'">
                                    <div class="rounded-[10px] overflow-hidden border border-[#8f8f8f] bg-white h-full flex flex-col items-center text-center p-6">
                                        <div class="w-24 h-24 rounded-full bg-zinc-200 overflow-hidden flex items-center justify-center mb-4" :style="item.avatar ? `background-image:url('${item.avatar}');background-size:cover;background-position:center` : ''">
                                            <template x-if="!item.avatar">
                                                <span class="text-3xl font-bold text-zinc-500" x-text="(item.nome || '?').charAt(0).toUpperCase()"></span>
                                            </template>
                                        </div>
                                        <h3 class="text-[16px] font-bold text-black" x-text="item.nome"></h3>
                                        <p class="text-[12px] text-[#484848] mt-1" x-text="item.cargo"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Botão voltar --}}
                    <button x-show="itensAba.length > 1" @click="scrollPrev()"
                        class="hidden sm:flex absolute top-1/2 -translate-y-1/2 left-3 w-10 h-10 rounded-full bg-white shadow-lg border border-zinc-200 items-center justify-center hover:bg-blue-50 transition">
                        <svg class="w-5 h-5 text-[#155dfc]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    {{-- Botão avançar --}}
                    <button x-show="itensAba.length > 1" @click="scrollNext()"
                        class="hidden sm:flex absolute top-1/2 -translate-y-1/2 right-3 w-10 h-10 rounded-full bg-white shadow-lg border border-zinc-200 items-center justify-center hover:bg-blue-50 transition">
                        <svg class="w-5 h-5 text-[#155dfc]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Dots (serviços/produtos) --}}
                <div x-show="!carregandoConfig && abaAtiva !== 'barbeiros' && numDots > 1" @resize.window.debounce.150ms="recalcDots()" class="flex justify-center gap-2 mt-5">
                    <template x-for="idx in numDots" :key="'dot-' + idx">
                        <div class="dot" :class="dotAtivo === (idx - 1) ? 'active' : ''" @click="scrollTo(idx - 1)"></div>
                    </template>
                </div>

                {{-- ===== Navegador de profissionais (Figma): passar de um a outro e ver serviços ===== --}}
                <div x-show="!carregandoConfig && abaAtiva === 'barbeiros' && barbeiros.length > 0" class="mt-6" x-cloak>
                    <template x-if="barbeiros[barbeiroAtivo]">
                        <div class="bg-white border border-[#e8e8e8] rounded-[14px] overflow-hidden">
                            {{-- Cabeçalho do profissional com navegação --}}
                            <div class="flex items-center gap-4 p-5 border-b border-zinc-100">
                                <button @click="barbeiroPrev()" :disabled="barbeiros.length < 2" class="w-10 h-10 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center flex-shrink-0 disabled:opacity-40" aria-label="Anterior">
                                    <svg class="w-5 h-5 text-[#155dfc]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <div class="flex-1 flex flex-col items-center text-center min-w-0">
                                    <div class="w-16 h-16 rounded-full bg-zinc-200 overflow-hidden flex items-center justify-center flex-shrink-0 mb-2" :style="barbeiros[barbeiroAtivo].avatar ? `background-image:url('${barbeiros[barbeiroAtivo].avatar}');background-size:cover;background-position:center` : ''">
                                        <template x-if="!barbeiros[barbeiroAtivo].avatar"><span class="text-2xl font-bold text-zinc-500" x-text="(barbeiros[barbeiroAtivo].nome || '?').charAt(0).toUpperCase()"></span></template>
                                    </div>
                                    <h3 class="text-lg font-bold text-zinc-900 truncate max-w-full" x-text="barbeiros[barbeiroAtivo].nome"></h3>
                                    <p class="text-sm text-zinc-500" x-text="barbeiros[barbeiroAtivo].cargo"></p>
                                </div>
                                <button @click="barbeiroNext()" :disabled="barbeiros.length < 2" class="w-10 h-10 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center flex-shrink-0 disabled:opacity-40" aria-label="Próximo">
                                    <svg class="w-5 h-5 text-[#155dfc]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            {{-- Serviços do profissional (cards com imagem, igual à referência) --}}
                            <div class="p-5">
                                <h4 class="text-[18px] font-bold text-zinc-900 mb-4">Conheça seus serviços</h4>
                                <div class="flex gap-4 overflow-x-auto pb-2 carousel-track">
                                    <template x-for="(serv, sidx) in (barbeiros[barbeiroAtivo].servicos || [])" :key="'bs'+serv.id">
                                        <button @click="abrirMontador(serv.id, barbeiros[barbeiroAtivo].id)" class="group relative flex-shrink-0 w-[210px] rounded-2xl overflow-hidden border border-zinc-200 hover:shadow-lg hover:border-[#155dfc]/40 transition text-left">
                                            <div class="h-[170px] bg-gradient-to-br from-zinc-800 to-zinc-900 relative overflow-hidden flex items-center justify-center">
                                                <svg class="w-10 h-10 text-zinc-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2z"/></svg>
                                                <template x-if="imagemCard(null, sidx)"><img :src="imagemCard(null, sidx)" x-on:error="$el.style.display='none'" alt="" class="absolute inset-0 w-full h-full object-cover"></template>
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                                    <p class="text-white text-sm font-bold leading-tight" x-text="serv.nome"></p>
                                                    <p class="text-white/80 text-[11px] mt-0.5" x-text="serv.duracao_label"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between px-3 py-2.5 bg-white">
                                                <span class="text-[17px] font-extrabold text-[#1538fc]" x-text="serv.preco_label"></span>
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-[#155dfc] group-hover:gap-2 transition-all">Agendar
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                            </div>
                                        </button>
                                    </template>
                                    <p x-show="!(barbeiros[barbeiroAtivo].servicos || []).length" class="text-sm text-zinc-400 py-6">Este profissional ainda não tem serviços configurados.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    {{-- Indicador de posição --}}
                    <div class="flex justify-center gap-2 mt-4" x-show="barbeiros.length > 1">
                        <template x-for="(b, idx) in barbeiros" :key="'bd'+idx">
                            <div class="dot" :class="barbeiroAtivo === idx ? 'active' : ''" @click="barbeiroAtivo = idx"></div>
                        </template>
                    </div>
                </div>
            </section>
        </main>

        {{-- ============================================================= --}}
        {{-- LADO DIREITO: Chat (estilo WhatsApp)                          --}}
        {{-- ============================================================= --}}
        <aside class="w-full lg:w-[450px] flex-shrink-0 flex flex-col lg:h-screen border-l border-zinc-200 bg-white">

            {{-- Cabeçalho do chat --}}
            <div class="bg-[#075e54] px-4 py-3 flex items-center gap-3 flex-shrink-0">
                <div class="w-11 h-11 rounded-full bg-white/90 flex items-center justify-center overflow-hidden flex-shrink-0">
                    <template x-if="config.logo">
                        <img :src="config.logo" alt="" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!config.logo">
                        <svg class="w-7 h-7 text-[#075e54]" fill="currentColor" viewBox="0 0 24 24"><path d="M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2z"/></svg>
                    </template>
                </div>
                <div class="min-w-0">
                    <p class="text-white font-semibold text-[18px] leading-tight truncate" x-text="config.nome_barbearia || '{{ $agendaConfig->nome_barbearia }}'"></p>
                    <p class="text-white/80 text-[13px] leading-tight" x-text="aiTyping ? 'digitando...' : 'Online'"></p>
                </div>
            </div>

            {{-- Mensagens --}}
            <div class="chat-bg chat-scroll flex-1 p-4 space-y-2 min-h-[300px]" x-ref="chat" role="log" aria-live="polite" aria-label="Conversa do agendamento">
                <template x-for="(msg, idx) in messages" :key="idx">
                    <div class="flex" :class="msg.tipo === 'cliente' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] px-3 py-2" :class="msg.tipo === 'cliente' ? 'bubble-user' : 'bubble-bot'">
                            <p class="text-[14px] text-[rgba(30,30,30,0.9)] leading-snug whitespace-pre-line" x-html="formatarMensagem(msg.texto, msg.tipo)"></p>
                            <p class="text-[10px] text-[#878585] text-right mt-1" x-text="msg.hora"></p>
                        </div>
                    </div>
                </template>

                {{-- Indicador de digitação --}}
                <div x-show="aiTyping" class="flex justify-start">
                    <div class="bubble-bot px-4 py-3 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-zinc-400 animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 rounded-full bg-zinc-400 animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 rounded-full bg-zinc-400 animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>

            {{-- ============ Painel estruturado (desativado: cliente escolhe digitando) ============ --}}
            <div x-show="false" class="bg-white/95 border-t border-zinc-200 px-3 py-2 max-h-44 overflow-y-auto" x-cloak>
                {{-- Serviços --}}
                <div x-show="ui.services && ui.services.length" class="flex flex-wrap gap-2 py-1">
                    <template x-for="s in (ui.services || [])" :key="'s'+s.id">
                        <button @click="enviarRapido(s.nome)" :disabled="aiBusy" class="rounded-full border border-[#075e54]/30 bg-[#075e54]/5 px-3 py-1.5 text-xs font-medium text-[#075e54] hover:bg-[#075e54]/10 disabled:opacity-50">
                            <span x-text="s.nome"></span><span class="text-zinc-500" x-text="s.preco_label ? ' · ' + s.preco_label : ''"></span>
                        </button>
                    </template>
                </div>
                {{-- Profissionais --}}
                <div x-show="ui.professionals && ui.professionals.length" class="flex flex-wrap gap-2 py-1">
                    <button x-show="ui.professionals && ui.professionals.length > 1" @click="enviarRapido('Qualquer profissional disponível')" :disabled="aiBusy" class="rounded-full border border-zinc-300 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-100 disabled:opacity-50">Qualquer profissional</button>
                    <template x-for="p in (ui.professionals || [])" :key="'p'+p.id">
                        <button @click="enviarRapido('Com ' + p.nome)" :disabled="aiBusy" class="rounded-full border border-zinc-300 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-100 disabled:opacity-50" x-text="p.nome"></button>
                    </template>
                </div>
                {{-- Datas --}}
                <div x-show="ui.dates && ui.dates.length" class="flex flex-wrap gap-2 py-1">
                    <template x-for="d in (ui.dates || [])" :key="'d'+d.data">
                        <button @click="enviarRapido('Dia ' + d.label)" :disabled="aiBusy" class="rounded-full border border-zinc-300 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-100 disabled:opacity-50" x-text="d.label"></button>
                    </template>
                </div>
                {{-- Horários --}}
                <div x-show="ui.times && ui.times.length" class="flex flex-wrap gap-2 py-1">
                    <template x-for="t in (ui.times || [])" :key="'t'+t.time+t.professional_id">
                        <button @click="enviarRapido('Às ' + t.time)" :disabled="aiBusy" class="rounded-full border border-[#155dfc]/30 bg-[#155dfc]/5 px-3 py-1.5 text-xs font-semibold text-[#155dfc] hover:bg-[#155dfc]/10 disabled:opacity-50" x-text="t.time"></button>
                    </template>
                </div>
            </div>

            {{-- ============ Card de proposta / confirmação (modo IA) ============ --}}
            <div x-show="proposal" class="bg-white border-t border-zinc-200 p-4" x-cloak>
                <div class="rounded-2xl border border-zinc-200 overflow-hidden">
                    <div class="bg-[#075e54] px-4 py-2"><p class="text-white text-sm font-semibold">Resumo do agendamento</p></div>
                    <div class="p-4 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-500">Serviço</span><span class="font-semibold text-zinc-800" x-text="proposal?.servico"></span></div>
                        <div class="flex justify-between"><span class="text-zinc-500">Profissional</span><span class="font-semibold text-zinc-800" x-text="proposal?.profissional"></span></div>
                        <div class="flex justify-between"><span class="text-zinc-500">Data</span><span class="font-semibold text-zinc-800" x-text="proposal?.data_label"></span></div>
                        <div class="flex justify-between"><span class="text-zinc-500">Horário</span><span class="font-semibold text-zinc-800"><span x-text="proposal?.inicio"></span> – <span x-text="proposal?.fim"></span></span></div>
                        <div class="flex justify-between" x-show="proposal?.preco_label"><span class="text-zinc-500">Valor</span><span class="font-bold text-[#155dfc]" x-text="proposal?.preco_label"></span></div>
                    </div>

                    {{-- Formulário seguro de dados pessoais (não vai para a IA) --}}
                    <div x-show="!customerSaved" class="border-t border-zinc-200 p-4 space-y-2 bg-zinc-50">
                        <p class="text-xs text-zinc-500">Para confirmar, preencha seus dados (usados apenas para o agendamento):</p>
                        <input type="text" x-model="customer.nome" placeholder="Nome completo" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#075e54]">
                        <input type="email" x-model="customer.email" placeholder="E-mail" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#075e54]">
                        <input type="text" x-model="customer.telefone" placeholder="Telefone / WhatsApp" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#075e54]">
                        <p x-show="proposalError" x-text="proposalError" class="text-xs text-red-600"></p>
                        <button @click="salvarCliente()" :disabled="proposalBusy" class="w-full rounded-xl bg-[#075e54] text-white text-sm font-semibold py-2.5 disabled:opacity-60">
                            <span x-text="proposalBusy ? 'Salvando...' : 'Continuar'"></span>
                        </button>
                    </div>

                    {{-- Confirmação explícita --}}
                    <div x-show="customerSaved" class="border-t border-zinc-200 p-4 space-y-2 bg-zinc-50">
                        <p x-show="proposalError" x-text="proposalError" class="text-xs text-red-600"></p>
                        <button @click="confirmarBooking()" :disabled="proposalBusy" class="w-full rounded-xl bg-[#155dfc] text-white text-sm font-bold py-2.5 disabled:opacity-60">
                            <span x-text="proposalBusy ? 'Confirmando...' : 'Confirmar agendamento'"></span>
                        </button>
                        <button @click="cancelarProposta()" :disabled="proposalBusy" class="w-full text-xs text-zinc-500 hover:text-zinc-700 py-1">Escolher outro horário</button>
                    </div>
                </div>
            </div>

            {{-- ============ Rodapé modo IA ============ --}}
            <div x-show="mode === 'ai'" class="bg-[#e4e4e4] p-3 flex-shrink-0" x-cloak>
                <div class="flex gap-2">
                    <input type="text" x-model="aiInput" :disabled="aiBusy" maxlength="1000" placeholder="Digite uma mensagem..." @keydown.enter="enviarMensagem()"
                        class="flex-1 rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none disabled:opacity-60" aria-label="Mensagem">
                    <button @click="enviarMensagem()" :disabled="aiBusy || !aiInput.trim()" class="w-10 h-10 rounded-full bg-[#075e54] flex items-center justify-center flex-shrink-0 disabled:opacity-50" aria-label="Enviar">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
                <div class="flex justify-center mt-1.5 px-1">
                    <button @click="mode = 'tradicional'; iniciarTradicional()" class="text-[10px] text-[#075e54] font-medium hover:underline">Agendar sem IA</button>
                </div>
            </div>

            {{-- ============ Rodapé carregando ============ --}}
            <div x-show="mode === 'loading'" class="bg-[#e4e4e4] p-4 flex-shrink-0 text-center text-xs text-zinc-500">Conectando atendimento...</div>

            {{-- ============ Rodapé modo tradicional (fallback) ============ --}}
            <div x-show="mode === 'tradicional'" class="bg-[#e4e4e4] p-3 flex-shrink-0" x-cloak>
                {{-- Nome --}}
                <div x-show="passo === 'nome'" class="flex gap-2">
                    <input type="text" x-model="formData.cliente_nome" placeholder="Seu nome completo" @keydown.enter="adicionarMensagem()"
                        class="flex-1 rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                    <button @click="adicionarMensagem()" class="w-10 h-10 rounded-full bg-[#075e54] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
                {{-- Email --}}
                <div x-show="passo === 'email'" class="flex gap-2">
                    <input type="email" x-model="formData.cliente_email" placeholder="Seu e-mail" @keydown.enter="adicionarMensagem()"
                        class="flex-1 rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                    <button @click="adicionarMensagem()" class="w-10 h-10 rounded-full bg-[#075e54] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
                {{-- Telefone --}}
                <div x-show="passo === 'telefone'" class="flex gap-2">
                    <input type="text" x-model="formData.cliente_telefone" placeholder="(11) 99999-9999" @keydown.enter="adicionarMensagem()"
                        class="flex-1 rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                    <button @click="adicionarMensagem()" class="w-10 h-10 rounded-full bg-[#075e54] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
                {{-- Serviço --}}
                <div x-show="passo === 'servico'">
                    <select x-model="formData.servico" @change="adicionarMensagem()" class="w-full rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                        <option value="">Selecione um serviço...</option>
                        <template x-for="serv in servicos" :key="serv.id"><option :value="serv.nome" x-text="serv.nome + ' — ' + serv.preco_label"></option></template>
                    </select>
                </div>
                {{-- Barbeiro --}}
                <div x-show="passo === 'barbeiro'">
                    <select x-model="formData.barbeiro_id" @change="adicionarMensagem()" class="w-full rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                        <option value="">Selecione um barbeiro...</option>
                        <template x-for="b in barbeiros" :key="b.id"><option :value="b.id" x-text="b.nome"></option></template>
                    </select>
                </div>
                {{-- Data --}}
                <div x-show="passo === 'data'">
                    <input type="date" x-model="formData.data_agendamento" :min="amanha" @change="adicionarMensagem()" class="w-full rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                </div>
                {{-- Hora --}}
                <div x-show="passo === 'hora'">
                    <select x-model="formData.hora_agendamento" @change="adicionarMensagem()" class="w-full rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                        <option value="">Selecione um horário...</option>
                        <template x-for="h in horariosDisponiveis" :key="h"><option :value="h" x-text="h"></option></template>
                    </select>
                </div>
                {{-- Observações --}}
                <div x-show="passo === 'observacoes'" class="flex gap-2">
                    <input type="text" x-model="formData.observacoes" placeholder="Observação (opcional)" @keydown.enter="confirmarAgendamento()"
                        class="flex-1 rounded-[20px] bg-white px-4 py-2.5 text-sm outline-none">
                    <button @click="confirmarAgendamento()" :disabled="carregando" class="w-10 h-10 rounded-full bg-[#075e54] flex items-center justify-center flex-shrink-0 disabled:opacity-60">
                        <svg x-show="!carregando" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        <svg x-show="carregando" class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    </button>
                </div>
                {{-- Sucesso / reiniciar --}}
                <div x-show="passo === 'sucesso'" class="flex justify-center">
                    <button @click="reiniciar()" class="rounded-[20px] bg-[#075e54] text-white text-sm font-semibold px-6 py-2.5">Novo agendamento</button>
                </div>
            </div>
        </aside>
    </div>

    {{-- ===================== MONTADOR DE AGENDAMENTO ===================== --}}
    <div x-cloak x-show="montador.aberto" @keydown.escape.window="fecharMontador()"
         class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-0 sm:p-4" x-transition.opacity>
        <div class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col" @click.outside="fecharMontador()">
            {{-- Cabeçalho com etapas --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <div class="flex items-center gap-2">
                    <button x-show="montador.etapa !== 'servico'" @click="montadorVoltar()" class="text-zinc-400 hover:text-zinc-700 -ml-1"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></button>
                    <h3 class="text-base font-bold text-zinc-900" x-text="montadorTitulo()"></h3>
                </div>
                <button @click="fecharMontador()" class="text-zinc-400 hover:text-zinc-700"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            {{-- Trilha de progresso --}}
            <div class="flex gap-1.5 px-5 pt-3">
                <template x-for="(et, i) in ['servico','profissional','data','hora']" :key="et">
                    <div class="h-1.5 flex-1 rounded-full transition" :class="montadorEtapaIndex() >= i ? 'bg-[#155dfc]' : 'bg-zinc-200'"></div>
                </template>
            </div>

            <div class="builder-scroll px-5 py-4">
                {{-- resumo da seleção --}}
                <div class="flex flex-wrap gap-2 mb-4" x-show="montador.serviceNome">
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700" x-text="montador.serviceNome"></span>
                    <span x-show="montador.profissionalNome" class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700" x-text="montador.profissionalNome"></span>
                    <span x-show="montador.dataLabel" class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700" x-text="montador.dataLabel"></span>
                    <span x-show="montador.hora" class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700" x-text="montador.hora"></span>
                </div>

                {{-- loading --}}
                <div x-show="montador.loading" class="py-10 flex justify-center"><svg class="h-7 w-7 animate-spin text-[#155dfc]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg></div>

                {{-- ETAPA: serviço --}}
                <div x-show="!montador.loading && montador.etapa === 'servico'" class="space-y-2">
                    <template x-for="s in servicos" :key="'ms'+s.id">
                        <button @click="montadorSelecionarServico(s)" class="w-full flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-left hover:border-[#155dfc] hover:bg-[#155dfc]/5 transition">
                            <div><p class="text-sm font-semibold text-zinc-800" x-text="s.nome"></p><p class="text-xs text-zinc-500" x-text="s.duracao_label"></p></div>
                            <span class="text-sm font-extrabold text-[#1538fc]" x-text="s.preco_label"></span>
                        </button>
                    </template>
                </div>

                {{-- ETAPA: profissional --}}
                <div x-show="!montador.loading && montador.etapa === 'profissional'" class="space-y-2">
                    <button @click="montadorSelecionarProfissional(null, 'Qualquer profissional')" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-left text-sm font-semibold text-zinc-800 hover:border-[#155dfc] hover:bg-[#155dfc]/5 transition">⭐ Qualquer profissional disponível</button>
                    <template x-for="p in montador.profissionais" :key="'mp'+p.id">
                        <button @click="montadorSelecionarProfissional(p.id, p.nome)" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-left text-sm font-semibold text-zinc-800 hover:border-[#155dfc] hover:bg-[#155dfc]/5 transition" x-text="p.nome"></button>
                    </template>
                    <p x-show="!montador.profissionais.length" class="text-sm text-zinc-400 py-4 text-center">Nenhum profissional disponível para este serviço.</p>
                </div>

                {{-- ETAPA: data (mini calendário) --}}
                <div x-show="!montador.loading && montador.etapa === 'data'">
                    {{-- Cabeçalho do mês --}}
                    <div class="flex items-center justify-between mb-3">
                        <button @click="calMesAnterior()" :disabled="!calPodeVoltar()" class="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="text-sm font-bold text-zinc-800 capitalize" x-text="calLabelMes()"></span>
                        <button @click="calProxMes()" :disabled="!calPodeAvancar()" class="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    {{-- Dias da semana --}}
                    <div class="grid grid-cols-7 gap-1 mb-1">
                        <template x-for="(ws, wi) in ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb']" :key="'ws'+wi">
                            <span class="text-center text-[11px] font-semibold text-zinc-400 py-1" x-text="ws"></span>
                        </template>
                    </div>
                    {{-- Grade de dias --}}
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="(cel, ci) in calMatriz()" :key="'cd'+ci">
                            <div>
                                <template x-if="cel">
                                    <button @click="cel.disponivel && montadorSelecionarData(cel)" :disabled="!cel.disponivel"
                                        class="w-full aspect-square rounded-lg text-sm font-semibold flex items-center justify-center transition"
                                        :class="cel.disponivel
                                            ? 'text-zinc-800 hover:bg-[#155dfc] hover:text-white border border-zinc-200'
                                            : 'text-zinc-300 cursor-not-allowed line-through decoration-zinc-300'"
                                        x-text="cel.dia"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <p x-show="!montador.datas.length" class="text-sm text-zinc-400 py-4 text-center">Sem datas disponíveis no momento.</p>
                </div>

                {{-- ETAPA: hora --}}
                <div x-show="!montador.loading && montador.etapa === 'hora'" class="grid grid-cols-3 gap-2">
                    <template x-for="h in montador.horarios" :key="'mh'+h.time+(h.professional_id||'x')">
                        <button @click="h.disponivel && montadorSelecionarHora(h)" :disabled="!h.disponivel"
                            class="rounded-xl py-2.5 text-sm font-semibold transition"
                            :class="h.disponivel
                                ? 'border border-zinc-200 text-zinc-800 hover:border-[#155dfc] hover:bg-[#155dfc]/5'
                                : 'border border-zinc-100 bg-zinc-50 text-zinc-300 line-through cursor-not-allowed'"
                            x-text="h.time"></button>
                    </template>
                    <p x-show="!montador.horarios.length" class="col-span-3 text-sm text-zinc-400 py-4 text-center">Sem horários nesta data. Escolha outra.</p>
                </div>

                <p x-show="montador.erro" x-text="montador.erro" class="mt-3 text-sm text-red-600"></p>
            </div>
        </div>
    </div>


    <script>
    function barbeariaApp(opts) {
        return {
            ...opts,
            carregandoConfig: true,
            config: {},
            servicos: [],
            produtos: [],
            barbeiros: [],

            // Abas
            abas: [
                { id: 'barbeiros', label: 'Barbeiros' },
                { id: 'produtos', label: 'Produtos' },
                { id: 'servicos', label: 'Serviços' },
            ],
            abaAtiva: 'servicos',
            dotAtivo: 0,
            numDots: 1,
            barbeiroAtivo: 0,

            // Montador de agendamento (site -> chat)
            montador: { aberto: false, etapa: 'servico', serviceId: null, serviceNome: '', professionalId: null, profissionalNome: '', data: null, dataLabel: '', hora: null, profissionais: [], datas: [], horarios: [], loading: false, erro: '' },
            calMes: null, // mês exibido no mini calendário

            // ===== Chat =====
            mode: 'loading',        // loading | ai | tradicional
            messages: [],
            // IA
            sessionToken: null,
            aiInput: '',
            aiTyping: false,
            aiBusy: false,
            ui: {},
            proposal: null,
            customer: { nome: '', email: '', telefone: '', observacoes: '' },
            customerSaved: false,
            proposalBusy: false,
            proposalError: '',
            // Tradicional (fallback)
            passo: 'nome',
            carregando: false,
            horariosDisponiveis: [],
            amanha: new Date(Date.now() + 86400000).toISOString().split('T')[0],
            formData: {
                cliente_nome: '', cliente_email: '', cliente_telefone: '',
                barbeiro_id: '', servico: '', data_agendamento: '',
                hora_agendamento: '', observacoes: '',
            },

            init() {
                this.carregarDados();
                this.iniciarChat();
            },

            carregarDados() {
                fetch(this.configUrl).then(r => r.json()).then(d => {
                    this.config = d;
                    // ===== Apenas para apresentação: limita e distribui de forma
                    // determinística (pelo id), sem alterar o backend =====
                    const porId = (a, b) => (a.id || 0) - (b.id || 0);
                    const todosServicos = [...(d.servicos || [])].sort(porId);
                    this.servicos = todosServicos.slice(0, 6);
                    this.produtos = [...(d.produtos || [])].sort(porId).slice(0, 6);
                    const todosBarbeiros = [...(d.barbeiros || [])].sort(porId).slice(0, 6);
                    // Cada barbeiro recebe um conjunto fixo de até 6 serviços,
                    // derivado do próprio id (mesmo barbeiro = mesmos serviços).
                    this.barbeiros = todosBarbeiros.map(b => {
                        let servicos = [];
                        if (todosServicos.length) {
                            const inicio = (b.id || 0) % todosServicos.length;
                            servicos = Array.from({ length: Math.min(6, todosServicos.length) },
                                (_, i) => todosServicos[(inicio + i) % todosServicos.length]);
                        }
                        return { ...b, servicos };
                    });
                    if (this.barbeiros.length === 1) this.formData.barbeiro_id = this.barbeiros[0].id;
                    this.carregandoConfig = false;
                    this.$nextTick(() => this.recalcDots());
                }).catch(() => { this.carregandoConfig = false; });
            },

            // ===== Navegação de profissionais =====
            barbeiroPrev() { if (this.barbeiros.length) this.barbeiroAtivo = (this.barbeiroAtivo - 1 + this.barbeiros.length) % this.barbeiros.length; },
            barbeiroNext() { if (this.barbeiros.length) this.barbeiroAtivo = (this.barbeiroAtivo + 1) % this.barbeiros.length; },

            // ===== Montador de agendamento =====
            montadorTitulo() { return { servico: 'Escolha o serviço', profissional: 'Escolha o profissional', data: 'Escolha a data', hora: 'Escolha o horário' }[this.montador.etapa] || 'Agendar'; },
            montadorEtapaIndex() { return ['servico','profissional','data','hora'].indexOf(this.montador.etapa); },
            abrirMontador(serviceId, professionalId) {
                const s = this.servicos.find(x => x.id === serviceId);
                this.montador = { aberto: true, etapa: 'servico', serviceId: serviceId || null, serviceNome: s ? s.nome : '', professionalId: professionalId || null, profissionalNome: '', data: null, dataLabel: '', hora: null, profissionais: [], datas: [], horarios: [], loading: false, erro: '' };
                if (professionalId) {
                    const b = this.barbeiros.find(x => x.id === professionalId);
                    this.montador.profissionalNome = b ? b.nome : '';
                }
                if (serviceId && professionalId) { this.montador.etapa = 'data'; this.montadorCarregarDatas(); }
                else if (serviceId) { this.montador.etapa = 'profissional'; this.montadorCarregarProfissionais(); }
            },
            fecharMontador() { this.montador.aberto = false; },
            montadorVoltar() {
                const ordem = ['servico','profissional','data','hora'];
                const i = ordem.indexOf(this.montador.etapa);
                if (i > 0) this.montador.etapa = ordem[i - 1];
                this.montador.erro = '';
            },
            montadorSelecionarServico(s) { this.montador.serviceId = s.id; this.montador.serviceNome = s.nome; this.montador.etapa = 'profissional'; this.montadorCarregarProfissionais(); },
            montadorSelecionarProfissional(id, nome) { this.montador.professionalId = id; this.montador.profissionalNome = nome; this.montador.etapa = 'data'; this.montadorCarregarDatas(); },
            montadorSelecionarData(d) { this.montador.data = d.data; this.montador.dataLabel = d.label; this.montador.etapa = 'hora'; this.montadorCarregarHorarios(); },
            montadorSelecionarHora(h) { this.montador.hora = h.time; this.montador.professionalId = h.professional_id; this.montadorFinalizar(); },

            montadorFetch(url, params) {
                const q = new URLSearchParams(params).toString();
                this.montador.loading = true; this.montador.erro = '';
                return fetch(url + '?' + q).then(r => r.json()).finally(() => { this.montador.loading = false; });
            },
            montadorCarregarProfissionais() {
                this.montadorFetch(this.professionalsUrl, { service_id: this.montador.serviceId })
                    .then(d => { this.montador.profissionais = d.profissionais || []; })
                    .catch(() => { this.montador.erro = 'Erro ao carregar profissionais.'; });
            },
            montadorCarregarDatas() {
                this.montadorFetch(this.datesUrl, { service_id: this.montador.serviceId, professional_id: this.montador.professionalId || '' })
                    .then(d => {
                        this.montador.datas = d.datas || [];
                        // Posiciona o calendário no mês da primeira data disponível.
                        const primeira = this.montador.datas[0];
                        const base = primeira ? this.parseData(primeira.data) : new Date();
                        this.calMes = new Date(base.getFullYear(), base.getMonth(), 1);
                    })
                    .catch(() => { this.montador.erro = 'Erro ao carregar datas.'; });
            },
            // ===== Mini calendário (etapa data) =====
            parseData(s) { const [a, m, d] = (s || '').split('-').map(Number); return new Date(a, (m || 1) - 1, d || 1); },
            calLabelMes() {
                if (!this.calMes) return '';
                return this.calMes.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
            },
            calMatriz() {
                if (!this.calMes) return [];
                const ano = this.calMes.getFullYear(), mes = this.calMes.getMonth();
                const disp = {};
                (this.montador.datas || []).forEach(d => { disp[d.data] = d; });
                const primeiroDiaSemana = new Date(ano, mes, 1).getDay();
                const diasNoMes = new Date(ano, mes + 1, 0).getDate();
                const celulas = [];
                for (let i = 0; i < primeiroDiaSemana; i++) celulas.push(null);
                for (let dia = 1; dia <= diasNoMes; dia++) {
                    const ds = `${ano}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
                    const item = disp[ds];
                    celulas.push({ dia, data: ds, label: item ? item.label : '', disponivel: !!item });
                }
                return celulas;
            },
            calMesAnterior() { if (this.calMes && this.calPodeVoltar()) this.calMes = new Date(this.calMes.getFullYear(), this.calMes.getMonth() - 1, 1); },
            calProxMes() { if (this.calMes && this.calPodeAvancar()) this.calMes = new Date(this.calMes.getFullYear(), this.calMes.getMonth() + 1, 1); },
            calPodeVoltar() {
                if (!this.calMes) return false;
                const hoje = new Date();
                const mesAtual = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                return this.calMes > mesAtual;
            },
            calPodeAvancar() {
                if (!this.calMes || !(this.montador.datas || []).length) return false;
                const ultima = this.parseData(this.montador.datas[this.montador.datas.length - 1].data);
                const mesUltima = new Date(ultima.getFullYear(), ultima.getMonth(), 1);
                return this.calMes < mesUltima;
            },
            montadorCarregarHorarios() {
                this.montadorFetch(this.timesUrl, { service_id: this.montador.serviceId, data: this.montador.data, professional_id: this.montador.professionalId || '' })
                    .then(d => { this.montador.horarios = d.horarios || []; })
                    .catch(() => { this.montador.erro = 'Erro ao carregar horários.'; });
            },
            async montadorFinalizar() {
                if (!this.sessionToken) { this.montador.erro = 'Aguarde a conexão e tente de novo.'; return; }
                this.montador.loading = true; this.montador.erro = '';
                try {
                    const res = await fetch(this.prepareUrl, {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                        body: JSON.stringify({ session_token: this.sessionToken, service_id: this.montador.serviceId, professional_id: this.montador.professionalId, data: this.montador.data, hora: this.montador.hora }),
                    });
                    const body = await res.json().catch(() => ({}));
                    if (res.status === 409) { this.montador.erro = body.message || 'Horário recém-ocupado. Escolha outro.'; this.montadorCarregarHorarios(); return; }
                    if (!body.ok || !body.proposal) { this.montador.erro = body.message || 'Não foi possível preparar o agendamento.'; return; }
                    this.proposal = body.proposal; this.customerSaved = false; this.proposalError = '';
                    this.fecharMontador();
                    this.messages.push({ tipo: 'bot', texto: 'Tudo pronto! Revise o resumo abaixo e confirme seu agendamento. 👇', hora: this.agora() });
                    this.scrollChat();
                    this.$nextTick(() => { document.querySelector('aside')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
                } catch (e) { this.montador.erro = 'Erro de conexão.'; }
                finally { this.montador.loading = false; }
            },


            // ===== Chat com IA =====
            csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; },
            uuid() { return (crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const r = Math.random()*16|0; return (c==='x'?r:(r&0x3|0x8)).toString(16); })); },

            iniciarChat() {
                fetch(this.chatStartUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() } })
                    .then(r => r.json())
                    .then(d => {
                        // Guarda o token sempre: o montador do site usa proposta/confirmação.
                        if (d && d.session_token) this.sessionToken = d.session_token;
                        if (d && d.ai_enabled && d.session_token) {
                            this.mode = 'ai';
                            this.messages.push({ tipo: 'bot', texto: d.greeting, hora: this.agora() });
                        } else {
                            this.iniciarTradicional();
                        }
                        this.scrollChat();
                    })
                    .catch(() => this.iniciarTradicional());
            },

            enviarRapido(texto) {
                this.aiInput = texto;
                this.enviarMensagem();
            },

            enviarMensagem() {
                const texto = (this.aiInput || '').trim();
                if (!texto || this.aiBusy) return;
                this.aiInput = '';
                this.messages.push({ tipo: 'cliente', texto, hora: this.agora() });
                this.aiBusy = true;
                this.ui = {};
                this.scrollChat();

                // Simulação humana: depois da mensagem, fica alguns segundos "Online" em silêncio
                // e só então começa a "digitar", como uma pessoa de verdade.
                const opcoesEspera = [4000, 4000, 5000];
                const espera = opcoesEspera[Math.floor(Math.random() * opcoesEspera.length)];
                const tempoMinDigitando = 1200; // segura o "digitando..." por um instante após a resposta chegar

                // Dispara a requisição imediatamente (em paralelo com a espera).
                const requisicao = fetch(this.chatMessageUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ session_token: this.sessionToken, message: texto, website: '' }),
                }).then(async r => ({ status: r.status, body: await r.json().catch(() => ({})) }))
                  .catch(() => ({ status: 0, body: {} }));

                const esperaSilenciosa = new Promise(res => setTimeout(res, espera));

                // Passada a espera silenciosa, mostra o "digitando...".
                esperaSilenciosa.then(() => { this.aiTyping = true; this.scrollChat(); });

                // Só renderiza a resposta após a espera + a resposta ter chegado + um tempo mínimo digitando.
                Promise.all([requisicao, esperaSilenciosa])
                    .then(([res]) => new Promise(done => setTimeout(() => done(res), tempoMinDigitando)))
                    .then(({ status, body }) => {
                        this.aiTyping = false;
                        this.aiBusy = false;
                        if (status === 0) { this.messages.push({ tipo: 'bot', texto: 'Tive um problema de conexão. Tente novamente ou use o agendamento sem IA.', hora: this.agora() }); this.scrollChat(); return; }
                        if (status === 410) { this.messages.push({ tipo: 'bot', texto: 'Sua sessão expirou. Vou abrir o agendamento tradicional.', hora: this.agora() }); this.iniciarTradicional(); return; }
                        if (!body.ok && body.message) { this.messages.push({ tipo: 'bot', texto: body.message, hora: this.agora() }); this.scrollChat(); return; }
                        if (body.assistant) this.messages.push({ tipo: 'bot', texto: this.limparTexto(body.assistant), hora: this.agora() });
                        this.ui = body.ui || {};
                        if (this.ui.proposal) { this.proposal = this.ui.proposal; this.customerSaved = false; this.proposalError = ''; }
                        this.scrollChat();
                    });
            },

            salvarCliente() {
                this.proposalError = '';
                if (!this.customer.nome || !this.customer.email || !this.customer.telefone) { this.proposalError = 'Preencha nome, e-mail e telefone.'; return; }
                this.proposalBusy = true;
                fetch(this.chatCustomerUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ session_token: this.sessionToken, proposal_token: this.proposal.token, ...this.customer }),
                }).then(r => r.json()).then(d => {
                    this.proposalBusy = false;
                    if (d.ok) { this.customerSaved = true; } else { this.proposalError = d.message || 'Não foi possível salvar os dados.'; }
                }).catch(() => { this.proposalBusy = false; this.proposalError = 'Erro de conexão.'; });
            },

            confirmarBooking() {
                this.proposalError = '';
                this.proposalBusy = true;
                const idempotencyKey = this.uuid();
                fetch(this.chatConfirmUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ session_token: this.sessionToken, proposal_token: this.proposal.token, idempotency_key: idempotencyKey }),
                }).then(async r => ({ status: r.status, body: await r.json().catch(() => ({})) }))
                  .then(({ status, body }) => {
                    this.proposalBusy = false;
                    if (body.ok && body.agendamento) {
                        const a = body.agendamento;
                        this.proposal = null;
                        this.ui = {};
                        this.messages.push({ tipo: 'bot', texto: `✅ Agendamento confirmado!\n${a.servico} com ${a.profissional}\n${a.data} às ${a.inicio}${a.fim ? '–' + a.fim : ''}.\nTe esperamos! 💈`, hora: this.agora() });
                    } else if (status === 409) {
                        this.proposalError = body.message || 'Horário recém-ocupado.';
                        this.messages.push({ tipo: 'bot', texto: body.message || 'Esse horário acabou de ser ocupado. Vamos escolher outro?', hora: this.agora() });
                        this.proposal = null; this.customerSaved = false;
                    } else {
                        this.proposalError = body.message || 'Não foi possível confirmar.';
                    }
                    this.scrollChat();
                }).catch(() => { this.proposalBusy = false; this.proposalError = 'Erro de conexão.'; });
            },

            cancelarProposta() {
                this.proposal = null;
                this.customerSaved = false;
                this.proposalError = '';
                this.messages.push({ tipo: 'bot', texto: 'Sem problema! Me diga qual outro horário ou serviço você prefere.', hora: this.agora() });
                this.scrollChat();
            },

            // ===== Fallback tradicional =====
            iniciarTradicional() {
                this.mode = 'tradicional';
                this.passo = 'nome';
                if (!this.messages.length) {
                    this.messages.push({ tipo: 'bot', texto: 'Olá! Vamos agendar seu horário. Qual é o seu nome?', hora: this.agora() });
                } else {
                    this.messages.push({ tipo: 'bot', texto: 'Vamos agendar pelo formulário. Qual é o seu nome?', hora: this.agora() });
                }
                this.scrollChat();
            },

            // ===== Abas / carrossel =====
            get itensAba() {
                if (this.abaAtiva === 'servicos') return this.servicos;
                if (this.abaAtiva === 'produtos') return this.produtos;
                return this.barbeiros;
            },
            get tituloAba() {
                return { servicos: 'Serviços em destaque', produtos: 'Produtos disponíveis', barbeiros: 'Nossa equipe' }[this.abaAtiva];
            },
            get vazioLabel() {
                return { servicos: 'Nenhum serviço cadastrado.', produtos: 'Nenhum produto disponível.', barbeiros: 'Nenhum barbeiro cadastrado.' }[this.abaAtiva];
            },
            resetCarousel() {
                this.dotAtivo = 0;
                this.$nextTick(() => {
                    if (this.$refs.track) this.$refs.track.scrollLeft = 0;
                    this.recalcDots();
                });
            },
            cardStep() {
                const track = this.$refs.track;
                if (!track) return 261;
                const card = track.querySelector('.carousel-card');
                return card ? card.offsetWidth + 26 : 261;
            },
            // Quantas posições de scroll existem, considerando quantos cards
            // cabem na largura visível (1 bolinha por posição alcançável).
            recalcDots() {
                const track = this.$refs.track;
                const total = this.itensAba.length;
                if (!track || !total) { this.numDots = Math.max(1, total); return; }
                const visiveis = Math.max(1, Math.round(track.clientWidth / this.cardStep()));
                this.numDots = Math.max(1, total - visiveis + 1);
                if (this.dotAtivo > this.numDots - 1) this.dotAtivo = this.numDots - 1;
            },
            updateDot() {
                const track = this.$refs.track;
                if (!track) return;
                this.recalcDots();
                this.dotAtivo = Math.min(this.numDots - 1, Math.round(track.scrollLeft / this.cardStep()));
            },
            scrollTo(idx) {
                if (this.$refs.track) this.$refs.track.scrollLeft = idx * this.cardStep();
            },
            scrollNext() {
                const track = this.$refs.track;
                if (!track) return;
                const next = (this.dotAtivo + 1) % this.numDots;
                track.scrollLeft = next * this.cardStep();
            },
            scrollPrev() {
                const track = this.$refs.track;
                if (!track) return;
                const prev = (this.dotAtivo - 1 + this.numDots) % this.numDots;
                track.scrollLeft = prev * this.cardStep();
            },

            // ===== Helpers =====
            imagemCard(propria, idx) {
                if (propria) return propria;
                const imgs = this.config.imagens || [];
                if (!imgs.length) return null;
                return imgs[idx % imgs.length].url;
            },
            formatNumber(n) { return new Intl.NumberFormat('pt-BR').format(n || 0); },
            agora() { return new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }); },
            limparTexto(t) {
                if (!t) return '';
                return t
                    .replace(/\*\*(.+?)\*\*/g, '$1')      // **negrito** -> texto
                    .replace(/__(.+?)__/g, '$1')          // __negrito__ -> texto
                    .replace(/(^|\n)\s*[-*•]\s+/g, '$1')  // remove marcadores de lista
                    .replace(/(^|\n)#{1,6}\s+/g, '$1')    // remove títulos markdown
                    .replace(/`{1,3}/g, '')               // remove crases de código
                    .replace(/\n{3,}/g, '\n\n')           // colapsa linhas em branco
                    .trim();
            },
            escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[c]));
            },
            // Render seguro: escapa todo o conteúdo e, nas mensagens do bot, deixa
            // o número das listas ("1.", "2)") em negrito para o cliente escolher.
            formatarMensagem(texto, tipo) {
                const esc = this.escapeHtml(texto);
                if (tipo !== 'bot') return esc;
                return esc.replace(/(^|\n)(\s*)(\d{1,2})([.)])(\s)/g, '$1$2<strong>$3$4</strong>$5');
            },
            get horarioLabel() {
                if (this.config.horario_inicio && this.config.horario_fim) {
                    const hm = (s) => (s || '').slice(0, 5);
                    return `${hm(this.config.horario_inicio)} às ${hm(this.config.horario_fim)}`;
                }
                return 'Confira nossos horários';
            },
            scrollChat() {
                this.$nextTick(() => { if (this.$refs.chat) this.$refs.chat.scrollTop = this.$refs.chat.scrollHeight; });
            },
            formatarData(data) {
                if (!data) return '';
                const [y, m, d] = data.split('-');
                return `${d}/${m}/${y}`;
            },

            // ===== Fluxo do agendamento tradicional (fallback) =====
            adicionarMensagem() {
                const passos = ['nome', 'email', 'telefone', 'servico', 'barbeiro', 'data', 'hora', 'observacoes'];
                const valor = {
                    nome: this.formData.cliente_nome,
                    email: this.formData.cliente_email,
                    telefone: this.formData.cliente_telefone,
                    servico: this.formData.servico,
                    barbeiro: this.formData.barbeiro_id,
                    data: this.formData.data_agendamento,
                    hora: this.formData.hora_agendamento,
                }[this.passo];

                if (!valor) return;

                const eco = {
                    nome: this.formData.cliente_nome,
                    email: this.formData.cliente_email,
                    telefone: this.formData.cliente_telefone,
                    servico: this.formData.servico,
                    barbeiro: (this.barbeiros.find(b => b.id == this.formData.barbeiro_id) || {}).nome || '',
                    data: this.formatarData(this.formData.data_agendamento),
                    hora: this.formData.hora_agendamento,
                }[this.passo];

                this.messages.push({ tipo: 'cliente', texto: eco, hora: this.agora() });
                this.scrollChat();

                const i = passos.indexOf(this.passo);
                let prox = passos[i + 1];
                // Pula barbeiro se só houver um
                if (prox === 'barbeiro' && this.barbeiros.length <= 1) prox = passos[i + 2];

                setTimeout(() => {
                    this.passo = prox;
                    this.proximaPergunta(prox);
                }, 400);
            },
            proximaPergunta(passo) {
                const perguntas = {
                    email: 'Qual é o seu e-mail?',
                    telefone: 'Qual é o seu telefone?',
                    servico: 'Qual serviço você deseja?',
                    barbeiro: 'Com qual barbeiro?',
                    data: 'Para qual data?',
                    hora: 'Qual horário você prefere?',
                    observacoes: 'Alguma observação? (opcional, ou aperte enviar)',
                };
                if (perguntas[passo]) this.messages.push({ tipo: 'bot', texto: perguntas[passo], hora: this.agora() });
                if (passo === 'hora') this.gerarHorarios();
                this.scrollChat();
            },
            gerarHorarios() {
                this.horariosDisponiveis = [];
                const parse = (s, fb) => { const p = (s || fb).split(':'); return parseInt(p[0]) * 60 + parseInt(p[1] || 0); };
                let ini = parse(this.config.horario_inicio, '08:00');
                const fim = parse(this.config.horario_fim, '18:00');
                const step = this.config.intervalo_slots || 30;
                for (let m = ini; m < fim; m += step) {
                    this.horariosDisponiveis.push(`${String(Math.floor(m / 60)).padStart(2, '0')}:${String(m % 60).padStart(2, '0')}`);
                }
            },
            confirmarAgendamento() {
                if (!this.formData.barbeiro_id && this.barbeiros.length) this.formData.barbeiro_id = this.barbeiros[0].id;
                this.carregando = true;
                if (this.formData.observacoes) this.messages.push({ tipo: 'cliente', texto: this.formData.observacoes, hora: this.agora() });
                this.scrollChat();

                const servObj = this.servicos.find(s => s.nome === this.formData.servico);
                const payload = { ...this.formData, service_id: servObj ? servObj.id : null };

                fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: JSON.stringify(payload),
                }).then(async r => ({ status: r.status, body: await r.json().catch(() => ({})) }))
                  .then(({ status, body }) => {
                    this.carregando = false;
                    if (body.success) {
                        this.messages.push({ tipo: 'bot', texto: '✅ Agendamento confirmado! Te esperamos. 💈', hora: this.agora() });
                        this.passo = 'sucesso';
                    } else if (status === 409) {
                        this.messages.push({ tipo: 'bot', texto: '⚠️ ' + (body.message || 'Esse horário não está disponível.') + ' Escolha outro horário.', hora: this.agora() });
                        this.passo = 'hora';
                    } else {
                        this.messages.push({ tipo: 'bot', texto: '⚠️ ' + (body.message || 'Não consegui concluir. Tente novamente.'), hora: this.agora() });
                    }
                    this.scrollChat();
                }).catch(() => {
                    this.carregando = false;
                    this.messages.push({ tipo: 'bot', texto: '⚠️ Erro ao processar. Tente novamente.', hora: this.agora() });
                    this.scrollChat();
                });
            },
            reiniciar() {
                this.formData = { cliente_nome: '', cliente_email: '', cliente_telefone: '', barbeiro_id: this.barbeiros.length === 1 ? this.barbeiros[0].id : '', servico: '', data_agendamento: '', hora_agendamento: '', observacoes: '' };
                this.passo = 'nome';
                this.messages.push({ tipo: 'bot', texto: 'Vamos a um novo agendamento! Qual é o seu nome?', hora: this.agora() });
                this.scrollChat();
            },
        };
    }
    </script>
</body>
</html>
