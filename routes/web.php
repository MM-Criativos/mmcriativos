<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui definimos as rotas principais do site MM Criativos.
| A home é a página inicial, e a rota /modal-content carrega
| conteúdos dinâmicos (services, skills, projects, etc.) para o modal holográfico.
|
*/

// 🌐 Página inicial
Route::get('/', function () {
    return view('pages.index');
})->name('home');


// ⚡ Rota dinâmica para carregar conteúdos no modal
Route::get('/modal-content/{type}/{slug}', function ($type, $slug) {
    $view = "components.content.$type.$slug";

    if (view()->exists($view)) {
        return response()->view($view);
    }

    return response('<p>Conteúdo não encontrado.</p>', 404);
})->name('modal.content');
