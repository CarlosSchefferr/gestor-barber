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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #c96f1f 0%, #d4822a 100%);
            min-height: 100vh;
        }

        .carousel-fade {
            animation: fadeInOut 0.7s ease-in-out;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        .chat-message {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-container {
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .chat-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #c96f1f 0%, #d4822a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(201, 111, 31, 0.3);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 16px;
            background: rgba(0, 0, 0, 0.3);
        }

        .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            background: white;
            width: 24px;
            border-radius: 4px;
        }

        .bar-info {
            position: relative;
            z-index: 10;
        }

        .bar-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            padding: 12px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .settings-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .settings-btn:hover {
            transform: rotate(90deg) scale(1.1);
            background: white;
        }

        .ribbon {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #c96f1f 0%, #d4822a 100%);
            color: white;
            padding: 8px 20px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .carousel-dots {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Settings Button (only if user is logged in) -->
    @auth
        <div class="settings-btn" title="Ir para configurações" onclick="window.location.href='{{ route('agenda.config.index') }}'">
            <svg class="w-6 h-6 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
    @endauth

    <div class="min-h-screen w-full flex items-center justify-center px-4 py-8">
        <div x-data="chatAgendamento({{ json_encode(route('public.agendamento.config', $agendaConfig->public_token)) }}, {{ json_encode(route('public.agendamento.submit', $agendaConfig->public_token)) }})" class="w-full max-w-full lg:max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 h-auto lg:h-[700px]">
                <!-- LEFT SIDE: Gallery & Info -->
                <div class="rounded-3xl overflow-hidden shadow-2xl border-4 border-white bg-white order-2 lg:order-1">
                    <div x-data="{ currentImage: 0, images: [], imageCount: 0 }" x-init="fetch($root.dataset.configUrl).then(r => r.json()).then(d => { images = d.imagens; imageCount = images.length; currentImage = 0; if (imageCount > 0) setInterval(() => { currentImage = (currentImage + 1) % imageCount }, 5000); })" :data-config-url="configUrl" class="relative bg-gradient-to-b from-barber-500 to-barber-600 h-96 lg:h-full flex flex-col justify-between">

                        <!-- Ribbon/Badge -->
                        <div class="ribbon">
                            Clique nas configurações para personalizar
                        </div>

                        <!-- Carousel Images -->
                        <div class="relative flex-1 overflow-hidden bg-zinc-900">
                            <template x-for="(image, index) in images" :key="index">
                                <img :src="image.url" :alt="'Imagem ' + (index + 1)" class="carousel-fade absolute inset-0 w-full h-full object-cover" :class="currentImage === index ? 'opacity-100' : 'opacity-0'" style="transition: opacity 0.7s ease-in-out;">
                            </template>

                            <!-- Fallback -->
                            <template x-if="!images.length">
                                <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-gradient-to-br from-barber-500 to-barber-700">
                                    <div class="text-center">
                                        <svg class="h-20 w-20 text-white/40 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-white/60 text-sm">Nenhuma imagem adicionada ainda</p>
                                        <p class="text-white/40 text-xs mt-2">Adicione fotos nas configurações</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Carousel Dots -->
                        <template x-if="images.length > 1">
                            <div class="carousel-dots">
                                <template x-for="(image, index) in images" :key="index">
                                    <div class="carousel-dot" :class="currentImage === index ? 'active' : ''" @click="currentImage = index"></div>
                                </template>
                            </div>
                        </template>

                        <!-- Barbershop Info -->
                        <div class="bar-info backdrop-blur-sm bg-black/20 p-6 text-white">
                            <h2 x-data="{ nome: '' }" x-init="fetch($root.dataset.configUrl).then(r => r.json()).then(d => { nome = d.nome_barbearia })" :data-config-url="configUrl" class="text-3xl font-black" x-text="nome">{{ $agendaConfig->nome_barbearia }}</h2>

                            <template x-if="images.length > 1">
                                <div class="flex items-center gap-2 text-xs text-white/80 mt-2">
                                    <span x-text="'Galeria: ' + (currentImage + 1) + ' / ' + images.length"></span>
                                </div>
                            </template>

                            <!-- Contact Info Badges -->
                            <div class="flex flex-col gap-2 mt-4">
                                @if($agendaConfig->telefone)
                                    <a href="tel:{{ $agendaConfig->telefone }}" class="bar-info-badge hover:shadow-lg hover:scale-105 transition">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>{{ $agendaConfig->telefone }}</span>
                                    </a>
                                @endif
                                @if($agendaConfig->endereco)
                                    <div class="bar-info-badge">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"></path>
                                        </svg>
                                        <span>{{ $agendaConfig->endereco }}</span>
                                    </div>
                                @endif
                                @if($agendaConfig->descricao)
                                    <div class="bar-info-badge">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 2a8 8 0 100 16 8 8 0 000-16zm0 3a1 1 0 110 2 1 1 0 010-2zm0 4a1 1 0 100 2v3a1 1 0 102 0v-4a1 1 0 00-1-1z"></path>
                                        </svg>
                                        <span>{{ Str::limit($agendaConfig->descricao, 40) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE: Chat -->
                <div class="rounded-3xl border-4 border-white bg-white shadow-2xl overflow-hidden flex flex-col order-1 lg:order-2" style="height: 100%;">

                    <!-- Chat Header with Gradient -->
                    <div class="bg-gradient-to-r from-barber-600 via-barber-500 to-barber-700 text-white px-6 py-5">
                        <h3 class="text-2xl font-black leading-tight">Agende seu Horário</h3>
                        <p class="text-xs text-white/75 mt-2 font-semibold tracking-wide">Responda as perguntas e finalize seu agendamento</p>
                    </div>

                    <!-- Chat Messages Container -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 chat-container" style="max-height: calc(100% - 200px);">
                        <template x-for="(msg, idx) in messages" :key="idx">
                            <div class="chat-message" :class="msg.tipo === 'bot' ? '' : 'flex justify-end'">
                                <div :class="msg.tipo === 'bot' ? 'bg-zinc-100 text-zinc-900 rounded-3xl rounded-tl-none' : 'bg-gradient-to-r from-barber-500 to-barber-600 text-white rounded-3xl rounded-tr-none'" class="px-5 py-3 max-w-xs text-sm font-medium">
                                    <p x-text="msg.texto"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input Area -->
                    <div class="border-t-2 border-zinc-100 bg-zinc-50 p-5">
                        <!-- Nome -->
                        <div x-show="passo === 'nome'" class="space-y-3 animate-in fade-in">
                            <input type="text" x-model="formData.cliente_nome" placeholder="Digite seu nome completo" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-primary text-white px-4 py-3 text-sm font-bold rounded-lg transition">Continuar →</button>
                        </div>

                        <!-- Email -->
                        <div x-show="passo === 'email'" class="space-y-3 animate-in fade-in">
                            <input type="email" x-model="formData.cliente_email" placeholder="Seu melhor email" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-primary text-white px-4 py-3 text-sm font-bold rounded-lg transition">Continuar →</button>
                        </div>

                        <!-- Telefone -->
                        <div x-show="passo === 'telefone'" class="space-y-3 animate-in fade-in">
                            <input type="text" x-model="formData.cliente_telefone" placeholder="(11) 99999-9999" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-primary text-white px-4 py-3 text-sm font-bold rounded-lg transition">Continuar →</button>
                        </div>

                        <!-- Serviço -->
                        <div x-show="passo === 'servico'" class="space-y-3 animate-in fade-in">
                            <select x-model="formData.servico" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                                <option value="">Selecione um serviço...</option>
                                <template x-for="serv in servicos" :key="serv">
                                    <option :value="serv" x-text="serv"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Data -->
                        <div x-show="passo === 'data'" class="space-y-3 animate-in fade-in">
                            <input type="date" x-model="formData.data_agendamento" :min="today" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                        </div>

                        <!-- Hora -->
                        <div x-show="passo === 'hora'" class="space-y-3 animate-in fade-in">
                            <select x-model="formData.hora_agendamento" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                                <option value="">Selecione uma hora...</option>
                                <template x-for="h in horariosDisponiveis" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Observações -->
                        <div x-show="passo === 'observacoes'" class="space-y-3 animate-in fade-in">
                            <textarea x-model="formData.observacoes" placeholder="(Opcional) Alguma observação?" rows="2" class="w-full rounded-lg border-2 border-zinc-200 bg-white px-4 py-3 text-sm font-medium focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition resize-none" @keydown.ctrl.enter="confirmarAgendamento()"></textarea>
                            <button @click="confirmarAgendamento()" :disabled="carregando" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-4 py-3 text-sm font-bold rounded-lg transition hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!carregando">Confirmar Agendamento ✓</span>
                                <span x-show="carregando" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Processando...
                                </span>
                            </button>
                        </div>

                        <!-- Sucesso -->
                        <div x-show="passo === 'sucesso'" class="space-y-4 text-center animate-in fade-in">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-emerald-100 to-emerald-50">
                                <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-black text-lg text-zinc-900">Agendamento realizado!</p>
                                <p class="text-sm text-zinc-600 mt-2">Verifique seu email para a confirmação</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function chatAgendamento(configUrl, submitUrl) {
        return {
            configUrl: configUrl,
            submitUrl: submitUrl,
            passo: 'nome',
            messages: [
                { tipo: 'bot', texto: 'Olá! Bem-vindo à nossa barbearia. Para agendar, preciso de alguns dados. Qual é o seu nome?' }
            ],
            formData: {
                cliente_nome: '',
                cliente_email: '',
                cliente_telefone: '',
                barbeiro_id: '',
                servico: '',
                data_agendamento: '',
                hora_agendamento: '',
                observacoes: ''
            },
            servicos: [],
            barbeiros: [],
            horariosDisponiveis: [],
            carregando: false,
            today: new Date().toISOString().split('T')[0],

            init() {
                this.carregarDados();
                this.$watch('passo', () => this.scrollChatParaBaixo());
            },

            carregarDados() {
                fetch(this.configUrl)
                    .then(r => r.json())
                    .then(d => {
                        this.servicos = d.servicos || [];
                        this.barbeiros = d.barbeiros || [];
                        if (this.barbeiros.length > 0) {
                            this.formData.barbeiro_id = this.barbeiros[0].id;
                        }
                    });
            },

            scrollChatParaBaixo() {
                this.$nextTick(() => {
                    const chat = document.querySelector('.chat-container');
                    if (chat) chat.scrollTop = chat.scrollHeight;
                });
            },

            adicionarMensagem() {
                const valores = {
                    nome: this.formData.cliente_nome,
                    email: this.formData.cliente_email,
                    telefone: this.formData.cliente_telefone,
                    servico: this.formData.servico,
                    data: this.formData.data_agendamento,
                    hora: this.formData.hora_agendamento
                };

                const passos = ['nome', 'email', 'telefone', 'servico', 'data', 'hora', 'observacoes'];
                const respostas = {
                    nome: `${this.formData.cliente_nome}`,
                    email: `${this.formData.cliente_email}`,
                    telefone: `${this.formData.cliente_telefone}`,
                    servico: `${this.formData.servico}`,
                    data: `${this.formatarData(this.formData.data_agendamento)}`,
                    hora: `${this.formData.hora_agendamento}`
                };

                if (valores[this.passo]) {
                    this.messages.push({ tipo: 'cliente', texto: respostas[this.passo] });
                    this.scrollChatParaBaixo();

                    const indiceAtual = passos.indexOf(this.passo);
                    if (indiceAtual < passos.length - 1) {
                        setTimeout(() => {
                            const proximoPasso = passos[indiceAtual + 1];
                            this.passo = proximoPasso;
                            this.mostrarProximaMensagem(proximoPasso);
                        }, 500);
                    }
                }
            },

            mostrarProximaMensagem(passo) {
                const mensagens = {
                    email: 'Qual é o seu email?',
                    telefone: 'Qual é o seu telefone?',
                    servico: 'Qual serviço você deseja?',
                    data: 'Qual data você prefere?',
                    hora: 'Qual horário?',
                    observacoes: 'Tem alguma observação? (Deixe em branco se não)'
                };

                if (mensagens[passo]) {
                    this.messages.push({ tipo: 'bot', texto: mensagens[passo] });
                }

                if (passo === 'data') {
                    this.gerarHorarios();
                }

                this.scrollChatParaBaixo();
            },

            gerarHorarios() {
                this.horariosDisponiveis = [];
                const inicio = 8;
                const fim = 18;
                for (let h = inicio; h < fim; h++) {
                    this.horariosDisponiveis.push(`${String(h).padStart(2, '0')}:00`);
                    this.horariosDisponiveis.push(`${String(h).padStart(2, '0')}:30`);
                }
            },

            formatarData(data) {
                if (!data) return '';
                const d = new Date(data);
                return d.toLocaleDateString('pt-BR');
            },

            confirmarAgendamento() {
                this.carregando = true;
                fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        this.messages.push({ tipo: 'cliente', texto: this.formData.observacoes || 'Sem observações' });
                        this.messages.push({ tipo: 'bot', texto: 'Perfeito! Seu agendamento foi confirmado. Obrigado! 🎉' });
                        this.passo = 'sucesso';
                        this.scrollChatParaBaixo();
                    } else {
                        this.messages.push({ tipo: 'bot', texto: 'Desculpe, ocorreu um erro. Tente novamente.' });
                        this.carregando = false;
                    }
                })
                .catch(e => {
                    console.error(e);
                    this.messages.push({ tipo: 'bot', texto: 'Erro ao processar seu agendamento.' });
                    this.carregando = false;
                });
            }
        }
    }
    </script>
</body>
</html>
