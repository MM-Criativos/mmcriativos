<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;

// Site público
Route::get('/', function () {
    return view('pages.index');
})->name('home');

// Conteúdos dinâmicos para o holo-modal
Route::get('/modal-content/{type}/{slug}', function ($type, $slug) {
    $view = "components.content.$type.$slug";

    if (view()->exists($view)) {
        return response()->view($view);
    }

    return response('<p>Conteúdo não encontrado.</p>', 404);
})->name('modal.content');

// Rotas padrão do Breeze (dashboard e profile protegidos)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Área administrativa (autenticação própria)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::view('/', 'admin.home')->name('dashboard');
    });
});

// Rotas do Breeze (auth)
require __DIR__ . '/auth.php';
