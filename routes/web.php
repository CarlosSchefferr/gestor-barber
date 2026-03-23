<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Clientes index acessível para usuários autenticados (inclui barbers)
    Route::get('clientes', [App\Http\Controllers\ClienteController::class, 'index'])->name('clientes.index');
    // Toggle ativo/inativo para clientes (permitido por usuários autenticados; controller checa permissões)
    Route::patch('clientes/{cliente}/toggle-status', [App\Http\Controllers\ClienteController::class, 'toggleStatus'])->name('clientes.toggleStatus');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    Route::resource('agendamentos', App\Http\Controllers\AgendamentoController::class);

    // Inline endpoint to allow authenticated users to create clientes from other screens
    Route::post('clientes/inline', [App\Http\Controllers\ClienteController::class, 'storeInline'])->name('clientes.inline.store');

    // Rotas restritas apenas para proprietários
    Route::middleware('owner')->group(function () {
        // Clientes CRUD for owners (index is exposed separately for barbers/auth users)
        Route::resource('clientes', App\Http\Controllers\ClienteController::class)->except(['index']);
        Route::get('financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro.index');

        // Agenda Configurações
        Route::get('agenda/configuracoes', [App\Http\Controllers\AgendaConfigController::class, 'index'])->name('agenda.config.index');
        Route::put('agenda/configuracoes', [App\Http\Controllers\AgendaConfigController::class, 'update'])->name('agenda.config.update');
        Route::post('agenda/imagens', [App\Http\Controllers\AgendaConfigController::class, 'uploadImages'])->name('agenda.imagens.upload');
        Route::delete('agenda/imagens/{imagem}', [App\Http\Controllers\AgendaConfigController::class, 'deleteImage'])->name('agenda.imagens.delete');
        Route::patch('agenda/imagens/reorder', [App\Http\Controllers\AgendaConfigController::class, 'reorderImages'])->name('agenda.imagens.reorder');

        // Rotas de Admin (apenas para proprietários)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
            Route::get('/users/create', [App\Http\Controllers\AdminController::class, 'create'])->name('create');
            Route::post('/users', [App\Http\Controllers\AdminController::class, 'store'])->name('store');
            Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'show'])->name('show');
            Route::get('/users/{user}/edit', [App\Http\Controllers\AdminController::class, 'edit'])->name('edit');
            Route::put('/users/{user}', [App\Http\Controllers\AdminController::class, 'update'])->name('update');
            Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'destroy'])->name('destroy');
        });

        // Serviços e Produtos — CRUD para proprietários (dentro do prefix admin)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('services', App\Http\Controllers\ServiceController::class);
            Route::resource('products', App\Http\Controllers\ProductController::class);
            // Inline service creation for owners via AJAX
            Route::post('services/inline', [App\Http\Controllers\ServiceController::class, 'storeInline'])->name('services.inline.store');
        });

        // Transações financeiras (criar)
        Route::post('transacoes', [App\Http\Controllers\TransacaoController::class, 'store'])->name('transacoes.store');
        // Metas
        Route::post('metas', [App\Http\Controllers\MetaController::class, 'store'])->name('metas.store');

        // Apresentação mensal financeira
        Route::get('financeiro/apresentacao/mensal', [App\Http\Controllers\FinanceiroPresentationController::class, 'preview'])
            ->name('financeiro.presentation.monthly.preview');
        Route::get('financeiro/apresentacao/mensal/pdf', [App\Http\Controllers\FinanceiroPresentationController::class, 'downloadPDF'])
            ->name('financeiro.presentation.monthly.pdf');
    });
});

// Rotas públicas de agendamento (sem autenticação)
Route::get('t/{public_token}', [App\Http\Controllers\PublicAgendamentoController::class, 'show'])->name('public.agendamento.show');
Route::get('t/{public_token}/api/config', [App\Http\Controllers\PublicAgendamentoController::class, 'getAgendaConfig'])->name('public.agendamento.config');
Route::post('t/{public_token}/api/submit', [App\Http\Controllers\PublicAgendamentoController::class, 'submitAgendamento'])->name('public.agendamento.submit');

require __DIR__.'/auth.php';
