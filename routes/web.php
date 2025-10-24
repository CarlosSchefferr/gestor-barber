<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    Route::resource('agendamentos', App\Http\Controllers\AgendamentoController::class);
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
    Route::get('financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro.index');
    
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
});

require __DIR__.'/auth.php';
