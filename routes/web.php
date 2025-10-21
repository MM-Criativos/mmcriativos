<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Admin\SkillCompetencyController as AdminSkillCompetencyController;
use App\Http\Controllers\Admin\ServiceInfoController as AdminServiceInfoController;
use App\Http\Controllers\Admin\ServiceBenefitController as AdminServiceBenefitController;
use App\Http\Controllers\Admin\ServiceFeatureController as AdminServiceFeatureController;
use App\Http\Controllers\Admin\ServiceProcessController as AdminServiceProcessController;
use App\Http\Controllers\Admin\ServiceCtaController as AdminServiceCtaController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ClientSocialMediaController as AdminClientSocialMediaController;
use App\Http\Controllers\Admin\ClientTestimonialController as AdminClientTestimonialController;
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

// Admin auth removido — fluxo unificado no Breeze

// Rotas do Breeze (auth)
require __DIR__ . '/auth.php';

// Admin painel (usa auth do Breeze)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard pode continuar usando /dashboard padrão; aqui focamos nos CRUDs

    // Serviços
    Route::resource('services', AdminServiceController::class);
    Route::put('services/{service}/info', [AdminServiceInfoController::class, 'update'])->name('services.info.update');
    Route::resource('services.benefits', AdminServiceBenefitController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
    Route::resource('services.features', AdminServiceFeatureController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
    Route::resource('services.processes', AdminServiceProcessController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
    Route::resource('services.ctas', AdminServiceCtaController::class)->only(['store', 'update', 'destroy'])->shallow();

    // Clients
    Route::resource('clients', AdminClientController::class);
    Route::get('clients/{client}/contacts', [AdminContactController::class, 'index'])->name('clients.contacts.index');
    Route::get('clients/{client}/contacts/create', [AdminContactController::class, 'create'])->name('clients.contacts.create');
    Route::post('clients/{client}/contacts', [AdminContactController::class, 'store'])->name('clients.contacts.store');
    Route::get('contacts/{contact}/edit', [AdminContactController::class, 'edit'])->name('contacts.edit');
    Route::put('contacts/{contact}', [AdminContactController::class, 'update'])->name('contacts.update');
    Route::delete('contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');
    Route::get('clients/{client}/contacts/select', [AdminContactController::class, 'select'])->name('clients.contacts.select');

    // Socials upsert
    Route::put('clients/{client}/socials/{socialMedia}', [AdminClientSocialMediaController::class, 'upsert'])->name('clients.socials.upsert');
    Route::delete('clients/{client}/socials/{socialMedia}', [AdminClientSocialMediaController::class, 'destroy'])->name('clients.socials.destroy');

    // Testimonials
    Route::resource('testimonials', AdminClientTestimonialController::class);

    // Skills
    Route::resource('skills', AdminSkillController::class);
    Route::resource('skills.competencies', AdminSkillCompetencyController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
});
