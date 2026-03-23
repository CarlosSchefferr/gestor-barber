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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        body {
            background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
        }

        .carousel-container {
            overflow: hidden;
            border-radius: 24px;
        }

        .carousel-image {
            transition: opacity 0.6s ease-in-out;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 16px;
            background: rgba(0, 0, 0, 0.15);
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
            width: 28px;
            border-radius: 4px;
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

        .message-bot {
            animation: slideInLeft 0.3s ease-out;
        }

        .message-user {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #1f2937;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, rgb(201, 111, 31) 0%, rgb(212, 130, 42) 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 111, 31, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 40;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .settings-btn:hover {
            transform: scale(1.05) rotate(90deg);
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #ecfdf5;
            margin: 0 auto 16px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Settings Button (only if authenticated) -->
    @auth
        <div class="settings-btn" title="Configurações" onclick="window.location.href='{{ route('agenda.config.index') }}'">
            <svg class="w-6 h-6 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
    @endauth

    <div class="min-h-screen w-full flex items-center justify-center px-4 py-8">
        <div x-data="chatAgendamento({{ json_encode(route('public.agendamento.config', $agendaConfig->public_token)) }}, {{ json_encode(route('public.agendamento.submit', $agendaConfig->public_token)) }})" class="w-full max-w-6xl">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-auto lg:h-[650px]">
                <!-- LEFT: Gallery -->
                <div class="rounded-3xl overflow-hidden shadow-lg border border-zinc-200 bg-white order-2 lg:order-1">
                    <div x-data="{ currentImage: 0, images: [], imageCount: 0 }" x-init="fetch($root.dataset.configUrl).then(r => r.json()).then(d => { images = d.imagens; imageCount = images.length; currentImage = 0; if (imageCount > 0) setInterval(() => { currentImage = (currentImage + 1) % imageCount }, 5000); })" :data-config-url="configUrl" class="relative bg-zinc-900 h-96 lg:h-full flex flex-col justify-between">

                        <!-- Carousel -->
                        <div class="relative flex-1 overflow-hidden bg-zinc-950">
                            <template x-for="(image, index) in images" :key="index">
                                <img :src="image.url" :alt="'Imagem ' + (index + 1)" class="carousel-image absolute inset-0 w-full h-full object-cover" :class="currentImage === index ? 'opacity-100' : 'opacity-0'">
                            </template>

                            <!-- Fallback -->
                            <template x-if="!images.length">
                                <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-zinc-900">
                                    <div class="text-center">
                                        <svg class="h-16 w-16 text-zinc-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-zinc-500 text-sm font-medium">Nenhuma imagem</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Dots →
                        <template x-if="images.length > 1">
                            <div class="carousel-dots">
                                <template x-for="(image, index) in images" :key="index">
                                    <div class="carousel-dot" :class="currentImage === index ? 'active' : ''" @click="currentImage = index"></div>
                                </template>
                            </div>
                        </template>

                        <!-- Info -->
                        <div class="bg-white border-t border-zinc-200 p-6">
                            <h2 x-data="{ nome: '' }" x-init="fetch($root.dataset.configUrl).then(r => r.json()).then(d => { nome = d.nome_barbearia })" :data-config-url="configUrl" class="text-2xl font-bold text-zinc-900" x-text="nome">{{ $agendaConfig->nome_barbearia }}</h2>

                            <div class="mt-4 space-y-2">
                                @if($agendaConfig->telefone)
                                    <a href="tel:{{ $agendaConfig->telefone }}" class="info-badge hover:shadow-md transition">
                                        <svg class="h-4 w-4 text-barber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="text-zinc-700 font-medium">{{ $agendaConfig->telefone }}</span>
                                    </a>
                                @endif

                                @if($agendaConfig->endereco)
                                    <div class="info-badge">
                                        <svg class="h-4 w-4 text-barber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path>
                                        </svg>
                                        <span class="text-zinc-700 font-medium">{{ Str::limit($agendaConfig->endereco, 35) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Chat -->
                <div class="rounded-3xl border border-zinc-200 bg-white shadow-lg overflow-hidden flex flex-col order-1 lg:order-2" style="height: 100%;">

                    <!-- Header -->
                    <div class="bg-white border-b border-zinc-200 px-6 py-5">
                        <h3 class="text-xl font-bold text-zinc-900">Agende seu horário</h3>
                        <p class="text-xs text-zinc-500 mt-1 font-medium">Preencha os dados e confirme seu agendamento</p>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 chat-container" style="max-height: calc(100% - 240px);">
                        <template x-for="(msg, idx) in messages" :key="idx">
                            <div class="flex" :class="msg.tipo === 'cliente' ? 'justify-end' : 'justify-start'">
                                <div :class="msg.tipo === 'cliente' ? 'bg-barber-500 text-white' : 'bg-zinc-100 text-zinc-900'" class="message-bot rounded-2xl px-4 py-3 max-w-xs text-sm">
                                    <p x-text="msg.texto" class="leading-relaxed"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input -->
                    <div class="border-t border-zinc-200 bg-zinc-50 p-5 space-y-3" style="max-height: 200px;">
                        <!-- Nome -->
                        <div x-show="passo === 'nome'" class="space-y-3">
                            <input type="text" x-model="formData.cliente_nome" placeholder="Seu nome completo" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-submit">Continuar</button>
                        </div>

                        <!-- Email -->
                        <div x-show="passo === 'email'" class="space-y-3">
                            <input type="email" x-model="formData.cliente_email" placeholder="Seu email" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-submit">Continuar</button>
                        </div>

                        <!-- Telefone -->
                        <div x-show="passo === 'telefone'" class="space-y-3">
                            <input type="text" x-model="formData.cliente_telefone" placeholder="(11) 99999-9999" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @keydown.enter="adicionarMensagem()">
                            <button @click="adicionarMensagem()" class="w-full btn-submit">Continuar</button>
                        </div>

                        <!-- Serviço -->
                        <div x-show="passo === 'servico'" class="space-y-3">
                            <select x-model="formData.servico" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                                <option value="">Selecione um serviço...</option>
                                <template x-for="serv in servicos" :key="serv">
                                    <option :value="serv" x-text="serv"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Data -->
                        <div x-show="passo === 'data'" class="space-y-3">
                            <input type="date" x-model="formData.data_agendamento" :min="today" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                        </div>

                        <!-- Hora -->
                        <div x-show="passo === 'hora'" class="space-y-3">
                            <select x-model="formData.hora_agendamento" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition" @change="adicionarMensagem()">
                                <option value="">Selecione uma hora...</option>
                                <template x-for="h in horariosDisponiveis" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Observações -->
                        <div x-show="passo === 'observacoes'" class="space-y-3">
                            <textarea x-model="formData.observacoes" placeholder="(Opcional) Alguma observação?" rows="2" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-barber-500 focus:ring-2 focus:ring-barber-500/20 outline-none transition resize-none" @keydown.ctrl.enter="confirmarAgendamento()"></textarea>
                            <button @click="confirmarAgendamento()" :disabled="carregando" class="w-full btn-submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <span x-show="!carregando">Confirmar Agendamento</span>
                                <span x-show="carregando">Processando...</span>
                            </button>
                        </div>

                        <!-- Sucesso -->
                        <div x-show="passo === 'sucesso'" class="text-center space-y-3">
                            <div class="success-icon">
                                <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="font-bold text-zinc-900">Agendamento realizado!</p>
                            <p class="text-xs text-zinc-600">Verifique seu email para confirmação</p>
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
                { tipo: 'bot', texto: 'Olá! Bem-vindo. Para agendar, preciso de alguns dados. Qual é o seu nome?' }
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
                        this.messages.push({ tipo: 'bot', texto: 'Perfeito! Seu agendamento foi confirmado!' });
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
