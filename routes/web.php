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
use App\Http\Controllers\Admin\ClientInfoController as AdminClientInfoController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ProjectChallengeController as AdminProjectChallengeController;
use App\Http\Controllers\Admin\ProjectSolutionController as AdminProjectSolutionController;
use App\Http\Controllers\Admin\ProjectProcessController as AdminProjectProcessController;
use App\Http\Controllers\Admin\ProjectImageController as AdminProjectImageController;
use App\Http\Controllers\Admin\ProjectSkillCompetencyController as AdminProjectSkillCompetencyController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
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
})->middleware(['auth', 'verified', 'approved'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin auth removido — fluxo unificado no Breeze

// Rotas do Breeze (auth)
require __DIR__ . '/auth.php';

// Página de pendência de aprovação
Route::get('/pending-approval', function () {
    return view('auth.pending');
})->name('pending.approval');

// Admin painel (usa auth do Breeze)
Route::middleware(['auth','approved'])->prefix('admin')->name('admin.')->group(function () {
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
    Route::put('clients/{client}/info', [AdminClientInfoController::class, 'update'])->name('clients.info.update');
    Route::get('/cep/{cep}', [AdminClientInfoController::class, 'getAddressByCep'])->name('cep.lookup');
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

    // Projects
    Route::resource('projects', AdminProjectController::class)->only(['index','create','store','edit','update','destroy']);

    // Projects: challenges & solutions
    Route::resource('projects.challenges', AdminProjectChallengeController::class)
        ->only(['store','update','destroy'])
        ->shallow();
    Route::resource('projects.solutions', AdminProjectSolutionController::class)
        ->only(['store','update','destroy'])
        ->shallow();

    // Projects: processes (pivot) and images
    Route::post('projects/{project}/processes', [AdminProjectProcessController::class, 'store'])->name('projects.processes.store');
    Route::put('project-processes/{projectProcess}', [AdminProjectProcessController::class, 'update'])->name('project-processes.update');
    Route::delete('project-processes/{projectProcess}', [AdminProjectProcessController::class, 'destroy'])->name('project-processes.destroy');

    Route::post('project-processes/{projectProcess}/images', [AdminProjectImageController::class, 'store'])->name('project-processes.images.store');
    Route::put('project-images/{projectImage}', [AdminProjectImageController::class, 'update'])->name('project-images.update');
    Route::delete('project-images/{projectImage}', [AdminProjectImageController::class, 'destroy'])->name('project-images.destroy');

    // Projects: skills + competencies
    Route::post('projects/{project}/skills/attach', [AdminProjectSkillCompetencyController::class, 'attach'])->name('projects.skills.attach');
    Route::delete('project-skill-competency/{projectSkillCompetency}', [AdminProjectSkillCompetencyController::class, 'destroy'])->name('project-skill-competency.destroy');

    // Settings
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'store'])->name('settings.store');

    // Team (users)
    Route::get('team', [AdminTeamController::class, 'index'])->name('team.index');
    Route::put('team/{user}/role', [AdminTeamController::class, 'updateRole'])->name('team.role');
    Route::put('team/{user}/approve', [AdminTeamController::class, 'approve'])->name('team.approve');
    Route::delete('team/{user}', [AdminTeamController::class, 'destroy'])->name('team.destroy');
});
