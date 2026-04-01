@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Administração</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Usuarios</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="abrirModalNovoUsuario()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo usuario
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            @if(session('provisional_password'))
                <p class="mt-2 text-sm text-emerald-600">Senha provisória: <span class="font-mono font-semibold">{{ session('provisional_password') }}</span></p>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Cards de Estatisticas -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total de usuarios</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $estatisticas['total_usuarios'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Base cadastrada</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Barbeiros ativos</p>
            <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $estatisticas['total_barbeiros'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Profissionais</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total agendamentos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $estatisticas['total_agendamentos'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Base de agendamentos</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Busca avancada</span>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-semibold text-zinc-700">Buscar por nome</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Digite o nome do usuario..."
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Cargo</label>
                <x-custom-select
                    name="cargo"
                    :options="[
                        '' => 'Todos',
                        'Barbeiro' => 'Barbeiro',
                        'Gerente' => 'Gerente',
                        'Recepcionista' => 'Recepcionista',
                        'Admin' => 'Admin',
                    ]"
                    :value="request('cargo', '')"
                    placeholder="Selecione o cargo"
                />
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Status</label>
                <x-custom-select
                    name="status"
                    :options="[
                        '' => 'Todos',
                        'active' => 'Ativos',
                        'inactive' => 'Inativos',
                    ]"
                    :value="request('status', '')"
                    placeholder="Selecione o status"
                />
            </div>

            <div class="md:col-span-3 flex flex-wrap items-center justify-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Aplicar filtros
                </button>
                <a href="{{ route('admin.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Usuarios -->
    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de usuarios</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os usuarios cadastrados no sistema</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Nivel</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cadastro</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($usuarios as $usuario)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-barber-100">
                                        <span class="text-sm font-bold text-barber-700">
                                            {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-zinc-900">{{ $usuario->name }}</div>
                                        <div class="text-xs text-zinc-500">{{ $usuario->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-zinc-900">{{ $usuario->cargo ?? '-' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($usuario->role === 'owner')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Proprietario
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Barbeiro
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-zinc-900">{{ $usuario->created_at?->format('d/m/Y') ?? '-' }}</div>
                                @if($usuario->created_at)
                                    <div class="text-xs text-zinc-500">{{ $usuario->created_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($usuario->agendamentos_count > 0 || \Carbon\Carbon::parse($usuario->updated_at)->diffInDays(now()) <= 30)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                        Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="abrirModalEditarUsuario({{ $usuario->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @if($usuario->id !== auth()->id())
                                        <button type="button" onclick="confirmDelete({{ $usuario->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-red-50 hover:text-red-600" title="Remover">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                    <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum usuario encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo usuario.</p>
                                <button type="button" onclick="abrirModalNovoUsuario()" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo usuario
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
            <div class="border-t border-zinc-200 bg-white px-6 py-4">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL NOVO USUARIO COM ABAS -->
<div id="modalNovoUsuario" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]" x-data="{ abaAtiva: 'dados' }">
    <div class="relative top-10 mx-auto mb-10 w-11/12 rounded-3xl border border-zinc-200 bg-white shadow-xl" :class="abaAtiva === 'servicos' ? 'max-w-4xl' : 'max-w-2xl'">
        <!-- Header -->
        <div class="border-b border-zinc-200 p-6 sm:p-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo usuario</h3>
            </div>
        </div>

        <!-- ABAS -->
        <div class="border-b border-zinc-200 flex gap-0">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Dados do Usuario
            </button>
            <button @click="abaAtiva = 'servicos'" :class="abaAtiva === 'servicos' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Servicos
            </button>
            <button @click="abaAtiva = 'horarios'" :class="abaAtiva === 'horarios' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Horarios
            </button>
        </div>

        <!-- FORMULARIO COM ABAS -->
        <form id="formNovoUsuario" action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf

            <!-- Aba Dados -->
            <div x-show="abaAtiva === 'dados'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4">
                        <h4 class="font-semibold text-red-800 text-sm mb-2">Erros encontrados:</h4>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-xs text-red-700">• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="Nome completo">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome Profissional (Site)</label>
                        <input type="text" name="professional_name" value="{{ old('professional_name') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Nome para o site">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">CPF <span class="text-red-500">*</span></label>
                        <input type="text" name="cpf" required value="{{ old('cpf') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="000.000.000-00">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Data de Nascimento <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_birth" required value="{{ old('date_of_birth') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Sexo <span class="text-red-500">*</span></label>
                        <x-custom-select
                            name="gender"
                            :options="['' => 'Selecione', 'M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro']"
                            :value="old('gender', '')"
                            placeholder="Selecione o gênero"
                        />
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nivel de Acesso <span class="text-red-500">*</span></label>
                        <x-custom-select
                            name="role"
                            :options="['' => 'Selecione', 'barber' => 'Barbeiro', 'owner' => 'Proprietario']"
                            :value="old('role', '')"
                            placeholder="Selecione o nível"
                        />
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Cargo <span class="text-red-500">*</span></label>
                        <input type="text" name="cargo" required value="{{ old('cargo') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="Ex: Barbeiro, Gerente">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Salario</label>
                        <input type="number" name="salary" step="0.01" min="0" value="{{ old('salary') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="0.00">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" required value="{{ old('phone') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="(11) 99999-9999">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="usuario@email.com">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Senha <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="senhaInput" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50" placeholder="Deixe em branco para gerar automaticamente">
                        <p class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            Esta é uma senha provisória para o primeiro acesso. O usuario deve alterá-la após o login.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Aba Servicos -->
            <div x-show="abaAtiva === 'servicos'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                <p class="text-sm text-zinc-600">Quando nenhum serviço estiver cadastrado, será utilizado o tempo, valor e comissão do cadastro do serviço.</p>
                <div class="mb-4 flex gap-2">
                    <select id="servico-select" class="flex-1 mt-2 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                        <option value="">Selecione um serviço</option>
                    </select>
                    <button type="button" onclick="adicionarServico('novo')" class="mt-2 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                        Adicionar
                    </button>
                </div>
                <div class="rounded-lg border border-zinc-200 overflow-x-auto">
                    <table class="w-full min-w-max">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Serviço</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Tempo (min)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Valor (R$)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Comissão (%)</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-zinc-700 whitespace-nowrap">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="servicos-tbody" class="divide-y divide-zinc-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba Horarios -->
            <div x-show="abaAtiva === 'horarios'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Entrada (00:00)</label>
                        <input type="time" name="schedule[entry_time]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Saída (00:00)</label>
                        <input type="time" name="schedule[exit_time]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Início do Intervalo (00:00)</label>
                        <input type="time" name="schedule[break_start]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Fim do Intervalo (00:00)</label>
                        <input type="time" name="schedule[break_end]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                </div>
                <p class="text-xs text-zinc-500 text-center py-3">Todos os campos são opcionais. Deixe em branco caso não utilize horário configurado.</p>
            </div>
        </form>

        <!-- BOTOES -->
        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8">
            <button type="button" onclick="fecharModalNovoUsuario()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="validarFormulario('novo')" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar usuario
            </button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR USUARIO COM ABAS -->
<div id="modalEditarUsuario" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]" x-data="{ abaAtiva: 'dados' }">
    <div class="relative top-10 mx-auto mb-10 w-11/12 rounded-3xl border border-zinc-200 bg-white shadow-xl" :class="abaAtiva === 'servicos' ? 'max-w-4xl' : 'max-w-2xl'">
        <!-- Header -->
        <div class="border-b border-zinc-200 p-6 sm:p-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar usuario</h3>
            </div>
        </div>

        <!-- ABAS -->
        <div class="border-b border-zinc-200 flex gap-0">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Dados do Usuario
            </button>
            <button @click="abaAtiva = 'servicos'" :class="abaAtiva === 'servicos' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Servicos
            </button>
            <button @click="abaAtiva = 'horarios'" :class="abaAtiva === 'horarios' ? 'border-b-2 border-barber-500 text-barber-600 font-semibold' : 'border-b-2 border-transparent text-zinc-600 hover:text-zinc-900'" class="flex-1 px-4 py-4 text-sm font-medium transition">
                Horarios
            </button>
        </div>

        <!-- FORMULARIO COM ABAS -->
        <form id="formEditarUsuario" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            @method('PUT')

            <!-- Aba Dados -->
            <div x-show="abaAtiva === 'dados'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Nome Completo <span class="text-red-500">*</span></label>
                        <input id="editar-name" type="text" name="name" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome Profissional (Site)</label>
                        <input id="editar-professional_name" type="text" name="professional_name" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">CPF <span class="text-red-500">*</span></label>
                        <input id="editar-cpf" type="text" name="cpf" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Data de Nascimento <span class="text-red-500">*</span></label>
                        <input id="editar-date_of_birth" type="date" name="date_of_birth" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Sexo <span class="text-red-500">*</span></label>
                        <select id="editar-gender" name="gender" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                            <option value="">Selecione</option>
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                            <option value="O">Outro</option>
                        </select>
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nivel de Acesso <span class="text-red-500">*</span></label>
                        <select id="editar-role" name="role" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                            <option value="barber">Barbeiro</option>
                            <option value="owner">Proprietario</option>
                        </select>
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Cargo <span class="text-red-500">*</span></label>
                        <input id="editar-cargo" type="text" name="cargo" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Salario</label>
                        <input id="editar-salary" type="number" name="salary" step="0.01" min="0" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                        <input id="editar-phone" type="tel" name="phone" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">E-mail <span class="text-red-500">*</span></label>
                        <input id="editar-email" type="email" name="email" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 error:border-red-500 error:bg-red-50">
                        <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Senha</label>
                        <input id="editar-password" type="password" name="password" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Deixe em branco para manter a senha atual">
                        <p class="mt-2 text-xs text-zinc-500">Deixe em branco para manter a senha atual.</p>
                    </div>
                </div>
            </div>

            <!-- Aba Servicos -->
            <div x-show="abaAtiva === 'servicos'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                <p class="text-sm text-zinc-600">Configuração de serviços específicos para este profissional.</p>
                <div class="mb-4 flex gap-2">
                    <select id="servico-select-edit" class="flex-1 mt-2 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                        <option value="">Selecione um serviço</option>
                    </select>
                    <button type="button" onclick="adicionarServico('edit')" class="mt-2 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                        Adicionar
                    </button>
                </div>
                <div class="rounded-lg border border-zinc-200 overflow-x-auto">
                    <table class="w-full min-w-max">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Serviço</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Tempo (min)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Valor (R$)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 whitespace-nowrap">Comissão (%)</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-zinc-700 whitespace-nowrap">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="servicos-tbody-edit" class="divide-y divide-zinc-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba Horarios -->
            <div x-show="abaAtiva === 'horarios'" class="space-y-4 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Entrada (00:00)</label>
                        <input id="editar-entry_time" type="time" name="schedule[entry_time]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Saída (00:00)</label>
                        <input id="editar-exit_time" type="time" name="schedule[exit_time]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Início do Intervalo (00:00)</label>
                        <input id="editar-break_start" type="time" name="schedule[break_start]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Fim do Intervalo (00:00)</label>
                        <input id="editar-break_end" type="time" name="schedule[break_end]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                </div>
                <p class="text-xs text-zinc-500 text-center py-3">Todos os campos são opcionais. Deixe em branco caso não utilize horário configurado.</p>
            </div>
        </form>

        <!-- BOTOES -->
        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8">
            <button type="button" onclick="fecharModalEditarUsuario()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="validarFormulario('edit')" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar alteracoes
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirmacao de Exclusao -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="mt-5 text-xl font-bold text-zinc-900">Confirmar exclusao</h3>
            <p class="mt-2 text-sm text-zinc-500">
                Tem certeza que deseja excluir este usuario? Esta acao nao pode ser desfeita.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Cancelar
                </button>
                <button id="confirmDeleteBtn" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700">
                    Sim, excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let userIdToDelete = null;
const usuarios = @json($usuariosJs);
const servicosDisponiveis = @json($services);
let servicosAdicionados = {};
let servicosAdicionadosEdit = {};

// Abrir modal automaticamente se houver erros
document.addEventListener('DOMContentLoaded', function() {
    const erroBox = document.querySelector('#modalNovoUsuario .bg-red-50');
    if (erroBox) {
        document.getElementById('modalNovoUsuario').classList.remove('hidden', 'pointer-events-none');
        // Scroll para o erro
        setTimeout(() => {
            erroBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
});

// Initialize services dropdown
function initServicoSelect(selectId) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Selecione um serviço</option>';
    servicosDisponiveis.forEach(service => {
        const option = document.createElement('option');
        option.value = service.id;
        option.textContent = `${service.name} (${service.duration}min - R$ ${parseFloat(service.price).toFixed(2)})`;
        select.appendChild(option);
    });
}

function getServicoById(id) {
    return servicosDisponiveis.find(s => s.id == id);
}

function adicionarServico(modalType) {
    const selectId = modalType === 'novo' ? 'servico-select' : 'servico-select-edit';
    const select = document.getElementById(selectId);
    const serviceId = select.value;

    if (!serviceId) return alert('Selecione um serviço');

    const service = getServicoById(serviceId);
    if (!service) return;

    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;

    // Don't add duplicate services
    if (servicosAtivos[serviceId]) return alert('Este serviço já foi adicionado');

    servicosAtivos[serviceId] = {
        id: service.id,
        name: service.name,
        duration: service.duration,
        price: parseFloat(service.price),
        commission_percentage: 30 // default 30%
    };

    renderServicosCriacao(modalType);
    select.value = '';
}

function renderServicosCriacao(modalType) {
    const tbodyId = modalType === 'novo' ? 'servicos-tbody' : 'servicos-tbody-edit';
    const tbody = document.getElementById(tbodyId);
    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;

    tbody.innerHTML = '';

    Object.entries(servicosAtivos).forEach(([serviceId, service]) => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-zinc-50';
        row.innerHTML = `
            <td class="px-6 py-4 text-sm text-zinc-900">${service.name}</td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <input type="number" class="duration-input w-20 rounded-2xl border border-zinc-200 px-3 py-2 text-sm bg-zinc-50 focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 transition" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.duration}" min="1">
                <span class="text-xs text-zinc-500 ml-1">min</span>
            </td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <input type="number" class="price-input w-24 rounded-2xl border border-zinc-200 px-3 py-2 text-sm bg-zinc-50 focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 transition" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.price.toFixed(2)}" min="0" step="0.01">
            </td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <input type="number" class="commission-input w-20 rounded-2xl border border-zinc-200 px-3 py-2 text-sm bg-zinc-50 focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 transition" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.commission_percentage}" min="0" max="100" step="0.01">
                <span class="text-xs text-zinc-500 ml-1">%</span>
            </td>
            <td class="px-6 py-4 text-center">
                <button type="button" class="btn-remover-servico text-red-600 hover:text-red-700 text-xs font-semibold" data-service-id="${serviceId}" data-modal-type="${modalType}">
                    Remover
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Attach event listeners
    tbody.querySelectorAll('.duration-input').forEach(input => {
        input.addEventListener('change', function() {
            const serviceId = this.dataset.serviceId;
            const modalType = this.dataset.modalType;
            const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
            servicosAtivos[serviceId].duration = parseInt(this.value);
        });
    });

    tbody.querySelectorAll('.price-input').forEach(input => {
        input.addEventListener('change', function() {
            const serviceId = this.dataset.serviceId;
            const modalType = this.dataset.modalType;
            const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
            servicosAtivos[serviceId].price = parseFloat(this.value);
        });
    });

    tbody.querySelectorAll('.commission-input').forEach(input => {
        input.addEventListener('change', function() {
            const serviceId = this.dataset.serviceId;
            const modalType = this.dataset.modalType;
            const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
            servicosAtivos[serviceId].commission_percentage = parseFloat(this.value);
        });
    });

    tbody.querySelectorAll('.btn-remover-servico').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const serviceId = this.dataset.serviceId;
            const modalType = this.dataset.modalType;
            removerServico(serviceId, modalType);
        });
    });
}

function removerServico(serviceId, modalType) {
    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
    delete servicosAtivos[serviceId];
    renderServicosCriacao(modalType);
}

function validarFormulario(modalType) {
    const formId = modalType === 'novo' ? 'formNovoUsuario' : 'formEditarUsuario';
    const form = document.getElementById(formId);

    // Limpar erros anteriores
    form.querySelectorAll('.error-message').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    form.querySelectorAll('input, select').forEach(el => {
        el.classList.remove('border-red-500', 'bg-red-50');
    });

    // Validar campos obrigatórios
    const campos = form.querySelectorAll('[required]');
    let temErro = false;
    let primeiroErro = null;

    campos.forEach(campo => {
        let valor = campo.value.trim();
        let temValor = false;

        if (campo.type === 'email') {
            temValor = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
        } else if (campo.type === 'tel') {
            temValor = valor.length >= 10;
        } else {
            temValor = valor.length > 0;
        }

        if (!temValor) {
            temErro = true;
            if (!primeiroErro) primeiroErro = campo;

            campo.classList.add('border-red-500', 'bg-red-50');
            const errorSpan = campo.closest('div').querySelector('.error-message');
            if (errorSpan) {
                errorSpan.classList.remove('hidden');
                if (campo.type === 'email') {
                    errorSpan.textContent = 'E-mail inválido';
                } else if (campo.type === 'tel') {
                    errorSpan.textContent = 'Telefone inválido';
                } else if (campo.name === 'name') {
                    errorSpan.textContent = 'Nome é obrigatório';
                } else if (campo.name === 'cpf') {
                    errorSpan.textContent = 'CPF é obrigatório';
                } else if (campo.name === 'date_of_birth') {
                    errorSpan.textContent = 'Data de nascimento é obrigatória';
                } else if (campo.name === 'gender') {
                    errorSpan.textContent = 'Selecione o gênero';
                } else if (campo.name === 'role') {
                    errorSpan.textContent = 'Selecione o nível de acesso';
                } else if (campo.name === 'cargo') {
                    errorSpan.textContent = 'Cargo é obrigatório';
                } else if (campo.name === 'phone') {
                    errorSpan.textContent = 'Telefone é obrigatório';
                } else {
                    errorSpan.textContent = 'Campo obrigatório';
                }
            }
        }
    });

    if (temErro && primeiroErro) {
        // Scroll até o primeiro campo com erro
        const modalId = modalType === 'novo' ? 'modalNovoUsuario' : 'modalEditarUsuario';
        const modal = document.getElementById(modalId);
        const contentDiv = modal.querySelector('.space-y-4');
        primeiroErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    // Se passou na validação, submeter o formulário
    const servicos = serializarServicos(modalType);
    servicos.forEach((servico, index) => {
        Object.entries(servico).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `services[${index}][${key}]`;
            input.value = value;
            form.appendChild(input);
        });
    });

    form.submit();
    return false;
}

function serializarServicos(modalType) {
    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
    return Object.values(servicosAtivos).map(s => ({
        service_id: s.id,
        time_minutes: s.duration,
        price: s.price,
        commission_percentage: s.commission_percentage
    }));
}

function abrirModalNovoUsuario() {
    servicosAdicionados = {};
    document.getElementById('formNovoUsuario').reset();
    renderServicosCriacao('novo');
    initServicoSelect('servico-select');
    document.getElementById('modalNovoUsuario').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalNovoUsuario() {
    document.getElementById('modalNovoUsuario').classList.add('hidden', 'pointer-events-none');
    servicosAdicionados = {};
}

function abrirModalEditarUsuario(id) {
    const usuario = usuarios[id];
    if (!usuario) return;

    servicosAdicionadosEdit = {};
    const form = document.getElementById('formEditarUsuario');
    form.action = `/admin/users/${id}`;

    document.getElementById('editar-name').value = usuario.name ?? '';
    document.getElementById('editar-professional_name').value = usuario.professional_name ?? '';
    document.getElementById('editar-cpf').value = usuario.cpf ?? '';
    document.getElementById('editar-date_of_birth').value = usuario.date_of_birth ?? '';
    document.getElementById('editar-gender').value = usuario.gender ?? '';
    document.getElementById('editar-role').value = usuario.role ?? '';
    document.getElementById('editar-cargo').value = usuario.cargo ?? '';
    document.getElementById('editar-salary').value = usuario.salary ?? '';
    document.getElementById('editar-phone').value = usuario.phone ?? '';
    document.getElementById('editar-email').value = usuario.email ?? '';
    document.getElementById('editar-password').value = '';

    // Pre-populate existing services (would require another API call or data attribute)
    // For now, start with empty services
    renderServicosCriacao('edit');
    initServicoSelect('servico-select-edit');

    document.getElementById('modalEditarUsuario').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalEditarUsuario() {
    document.getElementById('modalEditarUsuario').classList.add('hidden', 'pointer-events-none');
    servicosAdicionadosEdit = {};
}

function confirmDelete(id) {
    userIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    userIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (userIdToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userIdToDelete}`;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = '{{ csrf_token() }}';

        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('modalNovoUsuario').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoUsuario();
});

document.getElementById('modalEditarUsuario').addEventListener('click', function(e) {
    if (e.target === this) fecharModalEditarUsuario();
});
</script>
@endsection
