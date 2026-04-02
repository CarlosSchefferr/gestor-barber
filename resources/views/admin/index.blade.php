@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';

    $opcoesServicos = ['' => 'Escolha na lista...'];
    if(isset($services)) {
        foreach($services as $service) {
            $price = is_array($service) ? $service['price'] : $service->price;
            $duration = is_array($service) ? $service['duration'] : $service->duration;
            $name = is_array($service) ? $service['name'] : $service->name;
            $id = is_array($service) ? $service['id'] : $service->id;
            $opcoesServicos[$id] = "{$name} ({$duration}min - R$ " . number_format((float)$price, 2, ',', '.') . ")";
        }
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Administração</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Usuários</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="abrirModalNovoUsuario()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo usuário
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

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total de usuários</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $estatisticas['total_usuarios'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Usuários</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Barbeiros ativos</p>
            <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $estatisticas['total_barbeiros'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Profissionais</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total agendamentos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $estatisticas['total_agendamentos'] }}</p>
            <p class="mt-1 text-sm text-zinc-500">Agendamentos</p>
        </div>
    </div>

    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Busca avancada</span>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-semibold text-zinc-700">Buscar por nome</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Digite o nome do usuário..."
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

    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de usuários</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os usuários cadastrados no sistema</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Nível</th>
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
                                        Proprietário
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
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum usuário encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo usuário.</p>
                                <button type="button" onclick="abrirModalNovoUsuario()" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo usuário
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

<div id="modalNovoUsuario" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full bg-zinc-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" x-data="{ abaAtiva: 'dados' }">
    <div class="relative w-full rounded-3xl border border-zinc-200 bg-white shadow-xl flex flex-col max-h-[90vh] transition-all duration-300 ease-in-out" :class="abaAtiva === 'servicos' ? 'max-w-6xl' : 'max-w-5xl'">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo usuário</h3>
            </div>
        </div>

        <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Dados do usuário
            </button>
            <button @click="abaAtiva = 'servicos'" :class="abaAtiva === 'servicos' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Serviços
            </button>
            <button @click="abaAtiva = 'horarios'" :class="abaAtiva === 'horarios' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Horários
            </button>
        </div>

        <form id="formNovoUsuario" action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 flex-1 overflow-y-auto border-t border-zinc-200">
            @csrf
            <input type="hidden" name="_admin_form" value="create">

            <div x-show="abaAtiva === 'dados'" class="space-y-6">
                @if($errors->any() && old('_admin_form', 'create') === 'create')
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4">
                        <h4 class="font-semibold text-red-800 text-sm mb-2">Não foi possível salvar o usuário. Revise os campos abaixo:</h4>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-xs text-red-700">• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Seção: Dados do Usuário -->
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
                        <svg class="w-5 h-5 text-barber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h4 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">Dados do Usuário</h4>
                    </div>

                    <!-- Preview da Foto de Perfil (Centralizado) -->
                    <div class="flex flex-col items-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                        <div id="avatarPreviewNovo" class="w-28 h-28 rounded-full bg-zinc-200 flex items-center justify-center overflow-hidden mb-4 border-4 border-white shadow-lg">
                            <svg class="w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <label class="text-sm font-semibold text-zinc-700 mb-2">Foto de perfil</label>
                        <input type="file" name="avatar" id="avatarInputNovo" accept="image/*" class="hidden" onchange="previewAvatar(this, 'avatarPreviewNovo')">
                        <button type="button" onclick="document.getElementById('avatarInputNovo').click()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Escolher foto
                        </button>
                        <p class="text-xs text-zinc-400 mt-2">Opcional • JPG, PNG até 2MB</p>
                    </div>

                    <!-- Nome completo / Nome profissional -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Nome completo">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nome profissional (Site)</label>
                            <input type="text" name="professional_name" value="{{ old('professional_name') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Nome para o site">
                        </div>
                    </div>

                    <!-- CPF / Data de nascimento -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">CPF <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf" required value="{{ old('cpf') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="000.000.000-00">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Data de nascimento <span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_birth" required value="{{ old('date_of_birth') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Sexo / Telefone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Sexo <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="gender"
                                :options="['' => 'Selecione', 'M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro']"
                                :value="old('gender', '')"
                                placeholder="Selecione o gênero"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required value="{{ old('phone') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="(11) 99999-9999">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Seção: Dados Administrativos -->
                <div class="space-y-5 pt-2">
                    <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
                        <svg class="w-5 h-5 text-barber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h4 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">Dados Administrativos</h4>
                    </div>

                    <!-- Nível de acesso / Cargo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nível de acesso <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="role"
                                :options="['' => 'Selecione', 'barber' => 'Barbeiro', 'owner' => 'Proprietário']"
                                :value="old('role', '')"
                                placeholder="Selecione o nível"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Cargo <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="cargo"
                                :options="['' => 'Selecione', 'Barbeiro' => 'Barbeiro', 'Gerente' => 'Gerente', 'Recepcionista' => 'Recepcionista', 'Auxiliar' => 'Auxiliar', 'Admin' => 'Admin']"
                                :value="old('cargo', '')"
                                placeholder="Selecione o cargo"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Salário / E-mail -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Salário</label>
                            <div class="mt-2">
                                <x-currency-input name="salary" :value="old('salary')" />
                            </div>
                            <p class="text-xs text-zinc-400 mt-1">Opcional</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="usuario@email.com">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        <div class="w-full sm:w-1/2">
                            <label class="text-sm font-semibold text-zinc-700">Senha</label>
                            <div class="relative mt-2">
                                <input type="text" name="password" id="senhaInputNovo" value="{{ old('password', Str::random(12)) }}" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-24 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 font-mono" placeholder="Senha provisória">
                                <button type="button" onclick="gerarSenhaProvisoria('senhaInputNovo')" class="absolute inset-y-0 right-2 flex items-center px-3 text-xs font-semibold text-barber-600 hover:text-barber-700">
                                    Gerar nova
                                </button>
                            </div>
                        </div>
                        <div class="w-full sm:w-1/2 sm:mt-7">
                            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                Esta é uma senha provisória para o primeiro acesso. O usuário deve alterá-la após o login.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="abaAtiva === 'servicos'" class="space-y-4 min-h-[400px] pb-48">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-3 items-end justify-center w-full mb-6 mx-auto">
                        <div class="w-full max-w-md">
                            <label class="block text-sm font-semibold text-zinc-700 mb-2">Selecione um serviço</label>
                            <x-custom-select
                                name="select_servico_temp_novo"
                                :options="$opcoesServicos"
                            />
                        </div>
                        <button type="button" onclick="adicionarServico('novo', getSelectedServiceId('novo'))" class="inline-flex h-[46px] items-center justify-center rounded-2xl bg-zinc-900 px-6 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-zinc-800 shrink-0">
                            Adicionar serviço
                        </button>
                    </div>
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

            <div x-show="abaAtiva === 'horarios'" class="space-y-4">
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
                        <label class="text-sm font-semibold text-zinc-700">Início do intervalo (00:00)</label>
                        <input type="time" name="schedule[break_start]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Fim do intervalo (00:00)</label>
                        <input type="time" name="schedule[break_end]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                </div>
                <p class="text-xs text-zinc-500 text-center py-3">Todos os campos são opcionais. Deixe em branco caso não utilize horário configurado.</p>
            </div>
        </form>

        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8 shrink-0">
            <button type="button" onclick="fecharModalNovoUsuario()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="validarFormulario('novo')" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar usuário
            </button>
        </div>
    </div>
</div>

<div id="modalEditarUsuario" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full bg-zinc-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" x-data="{ abaAtiva: 'dados' }">
    <div class="relative w-full rounded-3xl border border-zinc-200 bg-white shadow-xl flex flex-col max-h-[90vh] transition-all duration-300 ease-in-out" :class="abaAtiva === 'servicos' ? 'max-w-6xl' : 'max-w-5xl'">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar usuário</h3>
            </div>
        </div>

        <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Dados do usuário
            </button>
            <button @click="abaAtiva = 'servicos'" :class="abaAtiva === 'servicos' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Serviços
            </button>
            <button @click="abaAtiva = 'horarios'" :class="abaAtiva === 'horarios' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Horários
            </button>
        </div>

        <form id="formEditarUsuario" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 flex-1 overflow-y-auto border-t border-zinc-200">
            @csrf
            @method('PUT')
            <input type="hidden" name="_admin_form" value="edit">
            <input type="hidden" name="_editing_user_id" id="editingUserId" value="">

            <div x-show="abaAtiva === 'dados'" class="space-y-6">
                @if($errors->any() && old('_admin_form') === 'edit')
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4">
                        <h4 class="font-semibold text-red-800 text-sm mb-2">Não foi possível atualizar o usuário. Revise os campos abaixo:</h4>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-xs text-red-700">• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Seção: Dados do Usuário -->
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
                        <svg class="w-5 h-5 text-barber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h4 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">Dados do Usuário</h4>
                    </div>

                    <!-- Preview da Foto de Perfil (Centralizado) -->
                    <div class="flex flex-col items-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                        <div id="avatarPreviewEdit" class="w-28 h-28 rounded-full bg-zinc-200 flex items-center justify-center overflow-hidden mb-4 border-4 border-white shadow-lg">
                            <svg class="w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <label class="text-sm font-semibold text-zinc-700 mb-2">Foto de perfil</label>
                        <input type="file" name="avatar" id="avatarInputEdit" accept="image/*" class="hidden" onchange="previewAvatar(this, 'avatarPreviewEdit')">
                        <button type="button" onclick="document.getElementById('avatarInputEdit').click()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Alterar foto
                        </button>
                        <p class="text-xs text-zinc-400 mt-2">Opcional • JPG, PNG até 2MB</p>
                    </div>

                    <!-- Nome completo / Nome profissional -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Nome completo">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nome profissional (Site)</label>
                            <input type="text" name="professional_name" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Nome para o site">
                        </div>
                    </div>

                    <!-- CPF / Data de nascimento -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">CPF <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="000.000.000-00">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Data de nascimento <span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_birth" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Sexo / Telefone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Sexo <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="gender"
                                :options="['' => 'Selecione', 'M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro']"
                                placeholder="Selecione o gênero"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="(11) 99999-9999">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Seção: Dados Administrativos -->
                <div class="space-y-5 pt-2">
                    <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
                        <svg class="w-5 h-5 text-barber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h4 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">Dados Administrativos</h4>
                    </div>

                    <!-- Nível de acesso / Cargo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Nível de acesso <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="role"
                                :options="['' => 'Selecione', 'barber' => 'Barbeiro', 'owner' => 'Proprietário']"
                                placeholder="Selecione o nível"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Cargo <span class="text-red-500">*</span></label>
                            <x-custom-select
                                name="cargo"
                                :options="['' => 'Selecione', 'Barbeiro' => 'Barbeiro', 'Gerente' => 'Gerente', 'Recepcionista' => 'Recepcionista', 'Auxiliar' => 'Auxiliar', 'Admin' => 'Admin']"
                                placeholder="Selecione o cargo"
                                required
                            />
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Salário / E-mail -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Salário</label>
                            <div class="mt-2">
                                <x-currency-input name="salary" />
                            </div>
                            <p class="text-xs text-zinc-400 mt-1">Opcional</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="usuario@email.com">
                            <span class="error-message text-xs text-red-600 mt-1 hidden"></span>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nova senha</label>
                        <div class="relative mt-2">
                            <input type="text" name="password" id="senhaInputEdit" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-24 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20 font-mono" placeholder="Deixe em branco para manter">
                            <button type="button" onclick="gerarSenhaProvisoria('senhaInputEdit')" class="absolute inset-y-0 right-2 flex items-center px-3 text-xs font-semibold text-barber-600 hover:text-barber-700">
                                Gerar nova
                            </button>
                        </div>
                        <p class="text-xs text-zinc-500 mt-1">Deixe a senha em branco para manter a senha atual.</p>
                    </div>
                </div>
            </div>

            <div x-show="abaAtiva === 'servicos'" class="space-y-4 min-h-[400px] pb-48">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-3 items-end justify-center w-full mb-6 mx-auto">
                        <div class="w-full max-w-md">
                            <label class="block text-sm font-semibold text-zinc-700 mb-2">Selecione um serviço</label>
                            <x-custom-select
                                name="select_servico_temp_edit"
                                :options="$opcoesServicos"
                            />
                        </div>
                        <button type="button" onclick="adicionarServico('edit', getSelectedServiceId('edit'))" class="inline-flex h-[46px] items-center justify-center rounded-2xl bg-zinc-900 px-6 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-zinc-800 shrink-0">
                            Adicionar serviço
                        </button>
                    </div>
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

            <div x-show="abaAtiva === 'horarios'" class="space-y-4">
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
                        <label class="text-sm font-semibold text-zinc-700">Início do intervalo (00:00)</label>
                        <input type="time" name="schedule[break_start]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Fim do intervalo (00:00)</label>
                        <input type="time" name="schedule[break_end]" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                    </div>
                </div>
                <p class="text-xs text-zinc-500 text-center py-3">Todos os campos são opcionais. Deixe em branco caso não utilize horário configurado.</p>
            </div>
        </form>

        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8 shrink-0">
            <button type="button" onclick="fecharModalEditarUsuario()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="validarFormulario('edit')" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar alteracoes
            </button>
        </div>
    </div>
</div>

<div id="confirmModal" class="fixed inset-0 z-50 hidden h-full w-full bg-zinc-900/60 backdrop-blur-[2px] flex items-center justify-center p-4">
    <div class="relative w-full max-w-md rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="mt-5 text-xl font-bold text-zinc-900">Confirmar exclusao</h3>
            <p class="mt-2 text-sm text-zinc-500">
                Tem certeza que deseja excluir este usuário? Esta acao nao pode ser desfeita.
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

document.addEventListener('DOMContentLoaded', function() {
    const hasErrors = @json($errors->any());
    const oldInput = @json(session()->getOldInput());

    if (!hasErrors) return;

    if (oldInput?._admin_form === 'edit' && oldInput?._editing_user_id) {
        abrirModalEditarUsuario(oldInput._editing_user_id);
        preencherEdicaoComOldInput(oldInput);
        const erroBoxEdit = document.querySelector('#modalEditarUsuario .bg-red-50');
        if (erroBoxEdit) {
            setTimeout(() => erroBoxEdit.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
        return;
    }

    const erroBox = document.querySelector('#modalNovoUsuario .bg-red-50');
    if (erroBox) {
        abrirModalNovoUsuario();
        setTimeout(() => {
            erroBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
});

function getSelectedServiceId(modalType) {
    const modalId = modalType === 'novo' ? 'modalNovoUsuario' : 'modalEditarUsuario';
    const nameAttr = modalType === 'novo' ? 'select_servico_temp_novo' : 'select_servico_temp_edit';
    const el = document.querySelector(`#${modalId} [name="${nameAttr}"]`);
    return el ? el.value : null;
}

function getServicoById(id) {
    return servicosDisponiveis.find(s => s.id == id);
}

function adicionarServico(modalType, serviceId = null) {
    const selectWrapper = document.querySelector(`input[name="select_servico_temp_${modalType === 'novo' ? 'novo' : 'edit'}"]`).closest('.cs-wrapper');
    const trigger = selectWrapper?.querySelector('.cs-trigger');

    if (!serviceId) {
        if (trigger) {
            trigger.classList.add('border-red-500', 'ring-2', 'ring-red-500/20');
            trigger.classList.remove('border-zinc-200');
        }
        return;
    }

    // Remove estado de erro se houver
    if (trigger) {
        trigger.classList.remove('border-red-500', 'ring-2', 'ring-red-500/20');
        trigger.classList.add('border-zinc-200');
    }

    const service = getServicoById(serviceId);
    if (!service) return;

    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
    if (servicosAtivos[serviceId]) {
        // Serviço já adicionado - destacar visualmente
        if (trigger) {
            trigger.classList.add('border-amber-500', 'ring-2', 'ring-amber-500/20');
            trigger.classList.remove('border-zinc-200');
            setTimeout(() => {
                trigger.classList.remove('border-amber-500', 'ring-2', 'ring-amber-500/20');
                trigger.classList.add('border-zinc-200');
            }, 2000);
        }
        return;
    }
    servicosAtivos[serviceId] = {
        id: service.id,
        name: service.name,
        duration: service.duration,
        price: parseFloat(service.price),
        commission_percentage: 30
    };
    renderServicosCriacao(modalType);
}

function renderServicosCriacao(modalType) {
    const tbodyId = modalType === 'novo' ? 'servicos-tbody' : 'servicos-tbody-edit';
    const tbody = document.getElementById(tbodyId);
    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;

    tbody.innerHTML = '';

    if (Object.keys(servicosAtivos).length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-500 bg-zinc-50/50">
                    Quando nenhum serviço estiver cadastrado, será utilizado o tempo, valor e comissão do cadastro do serviço.
                </td>
            </tr>
        `;
        return;
    }

    Object.entries(servicosAtivos).forEach(([serviceId, service]) => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-zinc-50';
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-medium text-zinc-900">${service.name}</td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <div class="w-full min-w-[80px] max-w-[120px] rounded-2xl border border-zinc-200 bg-zinc-50 shadow-sm transition focus-within:border-barber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-barber-500/20 flex items-center overflow-hidden">
                    <input type="number" class="duration-input w-full bg-transparent pl-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none border-none focus:ring-0" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.duration}" min="1">
                    <span class="pr-3 pl-1 text-xs select-none shrink-0" style="color: #18181b;">min</span>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <div class="w-full min-w-[100px] max-w-[140px] rounded-2xl border border-zinc-200 bg-zinc-50 shadow-sm transition focus-within:border-barber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-barber-500/20 flex items-center overflow-hidden">
                    <span class="pl-3 pr-1 text-xs select-none shrink-0" style="color: #18181b;">R$</span>
                    <input type="number" class="price-input w-full bg-transparent pr-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none border-none focus:ring-0" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.price.toFixed(2)}" min="0" step="0.01">
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-zinc-900">
                <div class="w-full min-w-[80px] max-w-[120px] rounded-2xl border border-zinc-200 bg-zinc-50 shadow-sm transition focus-within:border-barber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-barber-500/20 flex items-center overflow-hidden">
                    <input type="number" class="commission-input w-full bg-transparent pl-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none border-none focus:ring-0" data-service-id="${serviceId}" data-modal-type="${modalType}" value="${service.commission_percentage}" min="0" max="100" step="0.01">
                    <span class="pr-3 pl-1 text-xs select-none shrink-0" style="color: #18181b;">%</span>
                </div>
            </td>
            <td class="px-6 py-4 text-center">
                <button type="button" class="btn-remover-servico inline-flex items-center justify-center rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100" data-service-id="${serviceId}" data-modal-type="${modalType}">
                    Remover
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

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
    form.querySelectorAll('.error-message').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    form.querySelectorAll('input, select, button.cs-trigger').forEach(el => {
        el.classList.remove('border-red-500', 'bg-red-50');
    });

    // Validar campos de input e select com required
    const campos = form.querySelectorAll('input[required]:not([type="hidden"]), select[required]');
    // Também validar inputs hidden com required (custom-select)
    const camposHidden = form.querySelectorAll('input[type="hidden"][required]');

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
                errorSpan.textContent = getMensagemErro(campo);
            }
        }
    });

    // Validar custom-selects (inputs hidden)
    camposHidden.forEach(campo => {
        let valor = campo.value.trim();
        if (!valor || valor === '') {
            temErro = true;

            // Encontrar o wrapper do custom-select
            const wrapper = campo.closest('.cs-wrapper');
            if (wrapper) {
                const trigger = wrapper.querySelector('.cs-trigger');
                if (trigger) {
                    trigger.classList.add('border-red-500', 'bg-red-50');
                    if (!primeiroErro) primeiroErro = trigger;
                }
            }

            const errorSpan = campo.closest('div')?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.classList.remove('hidden');
                errorSpan.textContent = getMensagemErro(campo);
            }
        }
    });

    if (temErro && primeiroErro) {
        primeiroErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    form.querySelectorAll('input[data-services-generated="1"]').forEach(el => el.remove());

    const servicesSubmitted = document.createElement('input');
    servicesSubmitted.type = 'hidden';
    servicesSubmitted.name = 'services_submitted';
    servicesSubmitted.value = '1';
    servicesSubmitted.dataset.servicesGenerated = '1';
    form.appendChild(servicesSubmitted);

    const servicos = serializarServicos(modalType);
    servicos.forEach((servico, index) => {
        Object.entries(servico).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `services[${index}][${key}]`;
            input.value = value;
            input.dataset.servicesGenerated = '1';
            form.appendChild(input);
        });
    });
    form.submit();
    return false;
}

function getMensagemErro(campo) {
    const mensagens = {
        'email': 'E-mail inválido',
        'name': 'Nome é obrigatório',
        'cpf': 'CPF é obrigatório',
        'date_of_birth': 'Data de nascimento é obrigatória',
        'gender': 'Selecione o sexo',
        'role': 'Selecione o nível de acesso',
        'cargo': 'Selecione o cargo',
        'phone': 'Telefone é obrigatório'
    };

    if (campo.type === 'email') return 'E-mail inválido';
    if (campo.type === 'tel') return 'Telefone inválido';
    return mensagens[campo.name] || 'Campo obrigatório';
}

function serializarServicos(modalType) {
    const servicosAtivos = modalType === 'novo' ? servicosAdicionados : servicosAdicionadosEdit;
    return Object.values(servicosAtivos).map(s => ({
        service_id: s.id,
        time_minutes: Number.isFinite(Number(s.duration)) ? Number(s.duration) : 0,
        price: Number.isFinite(Number(s.price)) ? Number(s.price) : 0,
        commission_percentage: Number.isFinite(Number(s.commission_percentage)) ? Number(s.commission_percentage) : 0
    }));
}

function gerarSenhaProvisoria(inputId) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let senha = '';
    for (let i = 0; i < 12; i++) {
        senha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById(inputId).value = senha;
}

function formatarMoeda(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toFixed(2);
    input.value = value.replace('.', ',');
}

function previewAvatar(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function resetAvatarPreview(previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = `
        <svg class="w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
    `;
}

function abrirModalNovoUsuario() {
    servicosAdicionados = {};
    document.getElementById('formNovoUsuario').reset();
    resetAvatarPreview('avatarPreviewNovo');
    gerarSenhaProvisoria('senhaInputNovo');
    renderServicosCriacao('novo');
    document.getElementById('modalNovoUsuario').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalNovoUsuario() {
    document.getElementById('modalNovoUsuario').classList.add('hidden', 'pointer-events-none');
    servicosAdicionados = {};
    resetAvatarPreview('avatarPreviewNovo');
}

function abrirModalEditarUsuario(id) {
    const usuario = usuarios[id];
    if (!usuario) return;

    servicosAdicionadosEdit = {};
    const form = document.getElementById('formEditarUsuario');
    form.action = `/admin/users/${id}`;
    const editingUserId = document.getElementById('editingUserId');
    if (editingUserId) editingUserId.value = id;

    const setValue = (name, value) => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const setCustomSelectValue = (name, value) => {
        const hiddenInput = form.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!hiddenInput) return;

        hiddenInput.value = value ?? '';
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    };

    setValue('name', usuario.name ?? '');
    setValue('professional_name', usuario.professional_name ?? '');
    setValue('cpf', usuario.cpf ?? '');
    setValue('date_of_birth', usuario.date_of_birth ?? '');
    setCustomSelectValue('gender', usuario.gender ?? '');
    setCustomSelectValue('role', usuario.role ?? '');
    setCustomSelectValue('cargo', usuario.cargo ?? '');
    setValue('salary', usuario.salary ?? '');
    setValue('phone', usuario.phone ?? '');
    setValue('email', usuario.email ?? '');
    setValue('password', '');
    setValue('schedule[entry_time]', usuario.schedule?.entry_time ?? '');
    setValue('schedule[exit_time]', usuario.schedule?.exit_time ?? '');
    setValue('schedule[break_start]', usuario.schedule?.break_start ?? '');
    setValue('schedule[break_end]', usuario.schedule?.break_end ?? '');

    if (Array.isArray(usuario.services)) {
        usuario.services.forEach((service) => {
            if (!service?.service_id) return;
            const catalogService = getServicoById(service.service_id);
            servicosAdicionadosEdit[service.service_id] = {
                id: service.service_id,
                name: service.name ?? catalogService?.name ?? `Serviço #${service.service_id}`,
                duration: Number(service.time_minutes ?? catalogService?.duration ?? 30),
                price: Number(service.price ?? catalogService?.price ?? 0),
                commission_percentage: Number(service.commission_percentage ?? 0),
            };
        });
    }

    // Atualizar preview do avatar
    const avatarPreview = document.getElementById('avatarPreviewEdit');
    if (usuario.avatar) {
        avatarPreview.innerHTML = `<img src="/storage/${usuario.avatar}" class="w-full h-full object-cover" alt="Avatar">`;
    } else {
        resetAvatarPreview('avatarPreviewEdit');
    }

    renderServicosCriacao('edit');
    document.getElementById('modalEditarUsuario').classList.remove('hidden', 'pointer-events-none');
}

function preencherEdicaoComOldInput(oldInput) {
    const form = document.getElementById('formEditarUsuario');
    if (!form || !oldInput) return;

    const setValue = (name, value) => {
        if (value === undefined || value === null) return;
        const el = form.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const setCustomSelectValue = (name, value) => {
        if (value === undefined || value === null) return;
        const hiddenInput = form.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!hiddenInput) return;

        hiddenInput.value = value;
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    };

    setValue('name', oldInput.name);
    setValue('professional_name', oldInput.professional_name);
    setValue('cpf', oldInput.cpf);
    setValue('date_of_birth', oldInput.date_of_birth);
    setCustomSelectValue('gender', oldInput.gender);
    setCustomSelectValue('role', oldInput.role);
    setCustomSelectValue('cargo', oldInput.cargo);
    setValue('salary', oldInput.salary);
    setValue('phone', oldInput.phone);
    setValue('email', oldInput.email);
    setValue('password', oldInput.password);
    setValue('schedule[entry_time]', oldInput?.schedule?.entry_time);
    setValue('schedule[exit_time]', oldInput?.schedule?.exit_time);
    setValue('schedule[break_start]', oldInput?.schedule?.break_start);
    setValue('schedule[break_end]', oldInput?.schedule?.break_end);

    servicosAdicionadosEdit = {};
    if (Array.isArray(oldInput.services)) {
        oldInput.services.forEach((service) => {
            const serviceId = Number(service?.service_id);
            if (!serviceId) return;
            const catalogService = getServicoById(serviceId);
            servicosAdicionadosEdit[serviceId] = {
                id: serviceId,
                name: catalogService?.name ?? `Serviço #${serviceId}`,
                duration: Number(service?.time_minutes ?? catalogService?.duration ?? 30),
                price: Number(service?.price ?? catalogService?.price ?? 0),
                commission_percentage: Number(service?.commission_percentage ?? 0),
            };
        });
    }

    renderServicosCriacao('edit');
}

function fecharModalEditarUsuario() {
    document.getElementById('modalEditarUsuario').classList.add('hidden', 'pointer-events-none');
    servicosAdicionadosEdit = {};
    resetAvatarPreview('avatarPreviewEdit');
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
