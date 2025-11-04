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
use App\Http\Controllers\Admin\ClasseController as AdminClasseController;
use App\Http\Controllers\Admin\SkillInfoController as AdminSkillInfoController;
use App\Http\Controllers\Admin\LayoutController as AdminLayoutController;
use App\Http\Controllers\Admin\SliderController as AdminSliderController;
use App\Http\Controllers\Admin\LineController as AdminLineController;
use App\Http\Controllers\Admin\AboutUsController as AdminAboutUsController;
use App\Http\Controllers\Admin\PriceController as AdminPriceController;
use App\Http\Controllers\Site\ContactFormController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Site\ModalController;
use App\Http\Controllers\Site\PublicBudgetController;
use App\Http\Controllers\Admin\Commercial\KpiController as CommercialKpiController;
use App\Http\Controllers\Admin\Commercial\DashboardController as CommercialDashboardController;
use App\Http\Controllers\Admin\Commercial\PlanController as CommercialPlanController;
use App\Http\Controllers\Admin\Commercial\BudgetController as CommercialBudgetController;
use App\Http\Controllers\Admin\Commercial\ExtraController as CommercialExtraController;
use App\Http\Controllers\Admin\Commercial\EmailTemplateController as CommercialEmailTemplateController;
use App\Http\Controllers\Admin\Content\DashboardController as ContentDashboardController;

// Site pÃºblico
Route::get('/', function () {
    return view('pages.index');
})->name('home');

// Páginas estáticas
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Contato (site)
Route::post('/contact', [ContactFormController::class, 'send'])->name('contact.send');



// ConteÃºdos dinÃ¢micos para o holo-modal
// Conteúdos dinâmicos para o holo-modal
Route::get('/modal-content/{type}/{slug}', [ModalController::class, 'content'])->name('modal.content');
Route::get('/modal-process/{projectProcess}', [ModalController::class, 'process'])->name('modal.process');


// Rotas padrÃ£o do Breeze (dashboard e profile protegidos)
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return view('dashboard');
})->middleware(['verified', 'approved', 'auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin auth removido â€” fluxo unificado no Breeze

// Rotas do Breeze (auth)
require __DIR__ . '/auth.php';

// PÃ¡gina de pendÃªncia de aprovaÃ§Ã£o
Route::get('/pending-approval', function () {
    return view('auth.pending');
})->name('pending.approval');

// Public budget routes (view, accept, decline)
Route::prefix('budget')->name('budget.')->group(function () {
    Route::get('/{token}', [PublicBudgetController::class, 'show'])->name('public');
    Route::get('/{token}/accept', [PublicBudgetController::class, 'accept'])->name('accept');
    Route::get('/{token}/decline', [PublicBudgetController::class, 'decline'])->name('decline');
});

// Admin painel (usa auth do Breeze)
Route::middleware(['auth','approved'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard pode continuar usando /dashboard padrÃ£o; aqui focamos nos CRUDs

    // ServiÃ§os
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
    Route::post('services/{service}/processes/bulk', [AdminServiceProcessController::class, 'bulk'])
        ->name('services.processes.bulk');
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
    Route::put('skills/{skill}/info', [AdminSkillInfoController::class, 'update'])->name('skills.info.update');
    Route::resource('skills.competencies', AdminSkillCompetencyController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();

    // Layout (UI)
    Route::get('layout', [AdminLayoutController::class, 'index'])->name('layout.index');
    Route::get('layout/slider', [AdminSliderController::class, 'edit'])->name('layout.slider.edit');
    Route::put('layout/slider', [AdminSliderController::class, 'update'])->name('layout.slider.update');
    Route::get('layout/lines', [AdminLineController::class, 'edit'])->name('layout.lines.edit');
    Route::put('layout/lines', [AdminLineController::class, 'update'])->name('layout.lines.update');
    Route::get('layout/aboutus', [AdminAboutUsController::class, 'edit'])->name('layout.aboutus.edit');
    Route::put('layout/aboutus', [AdminAboutUsController::class, 'update'])->name('layout.aboutus.update');
    Route::get('layout/price', [AdminPriceController::class, 'edit'])->name('layout.price.edit');
    Route::put('layout/price', [AdminPriceController::class, 'update'])->name('layout.price.update');

    // Content module (Serviços e Habilidades)
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [ContentDashboardController::class, 'index'])->name('dashboard');
    });

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
    Route::get('team/{user}/edit', [AdminTeamController::class, 'edit'])->name('team.edit');
    Route::put('team/{user}', [AdminTeamController::class, 'update'])->name('team.update');
    Route::put('team/{user}/role', [AdminTeamController::class, 'updateRole'])->name('team.role');
    Route::put('team/{user}/approve', [AdminTeamController::class, 'approve'])->name('team.approve');
    Route::delete('team/{user}', [AdminTeamController::class, 'destroy'])->name('team.destroy');

    // Classes (admin)
    Route::get('classes', [AdminClasseController::class, 'index'])->name('classes.index');
    Route::get('classes/{classe}/edit', [AdminClasseController::class, 'edit'])->name('classes.edit');
    Route::put('classes/{classe}', [AdminClasseController::class, 'update'])->name('classes.update');

    // Commercial module
    Route::prefix('commercial')->name('commercial.')->group(function () {
        // Dashboard
        Route::get('/', [CommercialDashboardController::class, 'index'])->name('dashboard');

        // Plans
        Route::resource('plans', CommercialPlanController::class)->except(['show']);

        // Budgets
        Route::resource('budgets', CommercialBudgetController::class)->except(['show']);
        Route::post('budgets/{budget}/send-email', [CommercialBudgetController::class, 'sendEmail'])->name('budgets.send-email');
        Route::get('budgets/{budget}/preview', [CommercialBudgetController::class, 'preview'])->name('budgets.preview');
        Route::post('budgets/{budget}/items/extra', [CommercialBudgetController::class, 'addExtra'])->name('budgets.items.extra');
        Route::put('budget-items/{budgetItem}', [CommercialBudgetController::class, 'updateItem'])->name('budget-items.update');
        Route::delete('budget-items/{budgetItem}', [CommercialBudgetController::class, 'destroyItem'])->name('budget-items.destroy');

        // Extras
        Route::resource('extras', CommercialExtraController::class)->except(['show']);
        Route::get('extras/by-service', [CommercialExtraController::class, 'byService'])->name('extras.by-service');

        // Email templates
        Route::resource('email-templates', CommercialEmailTemplateController::class)->except(['show']);
        Route::get('email-templates/{emailTemplate}/preview', [CommercialEmailTemplateController::class, 'preview'])->name('email-templates.preview');

        // KPI
        Route::get('kpi', [CommercialKpiController::class, 'index'])->name('kpi.index');
    });
});

