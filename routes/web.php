<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Admin\SkillCompetencyController as AdminSkillCompetencyController;
use App\Http\Controllers\Admin\ServiceInfoController as AdminServiceInfoController;
use App\Http\Controllers\Admin\ProjectPlanningQualitativeController;
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
use App\Http\Controllers\Admin\ProjectTaskController as AdminProjectTaskController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\ProjectPlanningController as AdminProjectPlanningController;
use App\Http\Controllers\Admin\ProjectPageController as AdminProjectPageController;
use App\Http\Controllers\Admin\ProjectPageComponentController as AdminProjectPageComponentController;
use App\Http\Controllers\Admin\ProcessController as AdminProcessController;
use App\Http\Controllers\Site\PublicBriefingController;
use App\Http\Controllers\Site\PublicBriefingQualitativeController;
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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Site\ModalController;
use App\Http\Controllers\Site\PublicBudgetController;
use App\Http\Controllers\Vee\VeeTestRunnerDispatchController;
use App\Http\Controllers\Admin\Commercial\KpiController as CommercialKpiController;
use App\Http\Controllers\SuperAdminTenantController;
use App\Http\Controllers\Admin\Commercial\DashboardController as CommercialDashboardController;
use App\Http\Controllers\Admin\Commercial\PlanController as CommercialPlanController;
use App\Http\Controllers\Admin\Commercial\BudgetController as CommercialBudgetController;
use App\Http\Controllers\Admin\Commercial\ExtraController as CommercialExtraController;
use App\Http\Controllers\Admin\Commercial\EmailTemplateController as CommercialEmailTemplateController;
use App\Http\Controllers\Admin\Commercial\ProspeccaoController as CommercialProspeccaoController;
use App\Http\Controllers\Admin\Commercial\ScriptVendasController as CommercialScriptVendasController;
use App\Http\Controllers\Admin\Content\DashboardController as ContentDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

/*
|------------------------------------------------------------------
| Diagnostic / Cookie debug
|------------------------------------------------------------------
*/
Route::get('/cookie-debug', function (Request $request) {
    session()->put('debug_cookie', now()->toDateTimeString());

    return response()->json([
        'session_id' => session()->getId(),
        'cookies' => $request->cookies->all(),
        'session_cookie_name' => config('session.cookie'),
    ]);
});

/*
|------------------------------------------------------------------
| Guest
|------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|------------------------------------------------------------------
| Authenticated
|------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post(
        'email/verification-notification',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

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
Route::get('/dashboard', DashboardController::class)
    ->middleware(['verified', 'approved', 'auth'])
    ->name('dashboard');

Route::get('/dashboard/day-tasks', [DashboardController::class, 'dayTasks'])
    ->middleware(['verified', 'approved', 'auth'])
    ->name('dashboard.day-tasks');

Route::post('/vee/orch/test-runner/dispatch', VeeTestRunnerDispatchController::class)
    ->middleware(['verified', 'approved', 'auth'])
    ->name('vee.orch.test-runner.dispatch');

Route::middleware(['verified', 'approved', 'auth'])->prefix('mmcloud/tenants')->name('mmcloud.tenants.')->group(function () {
    Route::get('/', [SuperAdminTenantController::class, 'index'])->name('index');
    Route::get('create', [SuperAdminTenantController::class, 'create'])->name('create');
    Route::post('/', [SuperAdminTenantController::class, 'store'])->name('store');
    Route::get('{tenant}/edit', [SuperAdminTenantController::class, 'edit'])->name('edit');
    Route::patch('{tenant}', [SuperAdminTenantController::class, 'update'])->name('update');
    Route::get('{tenant}/instances/create', [SuperAdminTenantController::class, 'createInstance'])
        ->name('instances.create');
    Route::post('{tenant}/instances', [SuperAdminTenantController::class, 'storeInstance'])
        ->name('instances.store');
    Route::post('{tenant}/regenerate-api-token', [SuperAdminTenantController::class, 'regenerateApiToken'])
        ->name('regenerate-api-token');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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

// Rotas para o questionário qualitativo
Route::get('projects/{project}/planning/qualitative/create', [ProjectPlanningQualitativeController::class, 'create'])
    ->name('admin.projects.planning.qualitative.create');

// Public signed route for client briefing view (no auth).
// The GET view requires a signed URL, but the POST that saves the form
// should not require the signature — it only needs CSRF protection.
Route::get('briefing/{project}/perception', [PublicBriefingController::class, 'perception'])
    ->middleware('signed')
    ->name('public.briefing.perception'); // Allow form submission without URL signature (CSRF handled in the form)
Route::post('briefing/{project}/perception', [PublicBriefingController::class, 'savePerception'])
    ->name('public.briefing.perception.save');

// Admin painel (usa auth do Breeze)
Route::middleware(['auth', 'approved'])->prefix('admin')->name('admin.')->group(function () {
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

    // Processes library
    Route::resource('processes', AdminProcessController::class);

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
    // Sub-sections: progress (em andamento) and completed (concluídos)
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('progress', [AdminProjectController::class, 'progress'])->name('progress.index');
        Route::get('completed', [AdminProjectController::class, 'completed'])->name('completed.index');
        Route::get('{project}/steps', [AdminProjectController::class, 'steps'])->name('steps.show');
        // Planning: scale responses
        Route::post('{project}/planning/scale', [AdminProjectPlanningController::class, 'saveScale'])
            ->name('planning.scale.save');
        Route::post('{project}/planning/scale/email', [AdminProjectPlanningController::class, 'sendScaleEmail'])
            ->name('planning.scale.email');
        Route::post('{project}/planning/interpretation', [AdminProjectPlanningController::class, 'saveInterpretation'])
            ->name('planning.interpretation.save');
        Route::post('{project}/planning/kickoff', [AdminProjectPlanningController::class, 'saveKickoff'])
            ->name('planning.kickoff.save');

        // Planning: qualitative questionnaire
        Route::get('{project}/planning/qualitative/edit', [ProjectPlanningQualitativeController::class, 'edit'])
            ->name('planning.qualitative.edit');
        Route::get('{project}/planning/qualitative/templates', [ProjectPlanningQualitativeController::class, 'templates'])
            ->name('planning.qualitative.templates');
        Route::get('{project}/planning/qualitative/preview', [ProjectPlanningQualitativeController::class, 'preview'])
            ->name('planning.qualitative.preview');
        Route::post('{project}/planning/qualitative/save', [ProjectPlanningQualitativeController::class, 'save'])
            ->name('planning.qualitative.save');
        Route::post('{project}/planning/qualitative/email', [ProjectPlanningQualitativeController::class, 'sendEmail'])
            ->name('planning.qualitative.email');

        Route::post('{project}/tasks', [AdminProjectTaskController::class, 'store'])->name('tasks.store');
    });
    Route::resource('projects', AdminProjectController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Projects: challenges & solutions
    Route::resource('projects.challenges', AdminProjectChallengeController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
    Route::resource('projects.solutions', AdminProjectSolutionController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
    Route::put('/projects/{project}/challenges/update-all', [AdminProjectChallengeController::class, 'updateAll'])
        ->name('projects.challenges.updateAll');
    Route::put('/projects/{project}/solutions/update-all', [AdminProjectSolutionController::class, 'updateAll'])
        ->name('projects.solutions.updateAll');


    // Projects: processes (pivot) and images
    Route::post('projects/{project}/processes', [AdminProjectProcessController::class, 'store'])->name('projects.processes.store');
    Route::put('project-processes/{projectProcess}', [AdminProjectProcessController::class, 'update'])->name('project-processes.update');
    Route::delete('project-processes/{projectProcess}', [AdminProjectProcessController::class, 'destroy'])->name('project-processes.destroy');

    Route::post('projects/{project}/summary', [AdminProjectController::class, 'updateSummary'])->name('projects.summary.update');
    Route::post('projects/{project}/finish', [AdminProjectController::class, 'finish'])->name('projects.finish');
    Route::post('projects/{project}/resume', [AdminProjectController::class, 'resume'])->name('projects.resume');

    Route::post('project-processes/{projectProcess}/images', [AdminProjectImageController::class, 'store'])->name('project-processes.images.store');
    Route::put('project-images/{projectImage}', [AdminProjectImageController::class, 'update'])->name('project-images.update');
    Route::delete('project-images/{projectImage}', [AdminProjectImageController::class, 'destroy'])->name('project-images.destroy');

    // Projects: pages and storytelling components
    Route::post('projects/{project}/pages', [AdminProjectPageController::class, 'store'])->name('projects.pages.store');
    Route::put('project-pages/{projectPage}', [AdminProjectPageController::class, 'update'])->name('project-pages.update');
    Route::delete('project-pages/{projectPage}', [AdminProjectPageController::class, 'destroy'])->name('project-pages.destroy');

    Route::post('project-pages/{projectPage}/components', [AdminProjectPageComponentController::class, 'store'])->name('project-pages.components.store');
    Route::put('project-page-components/{projectPageComponent}', [AdminProjectPageComponentController::class, 'update'])->name('project-page-components.update');
    Route::delete('project-page-components/{projectPageComponent}', [AdminProjectPageComponentController::class, 'destroy'])->name('project-page-components.destroy');
    Route::put('projects/{project}/pages/update-all', [AdminProjectPageController::class, 'updateAll'])
        ->name('projects.pages.updateAll');
    Route::put('project-pages/{projectPage}/components/update-all', [AdminProjectPageComponentController::class, 'updateAll'])
        ->name('project-page-components.updateAll');

    // Projects: skills + competencies
    Route::post('projects/{project}/skills/attach', [AdminProjectSkillCompetencyController::class, 'attach'])->name('projects.skills.attach');
    Route::delete('project-skill-competency/{projectSkillCompetency}', [AdminProjectSkillCompetencyController::class, 'destroy'])->name('project-skill-competency.destroy');

    // Projects: tasks
    Route::get('projects/{project}/development/tasks/list', [AdminProjectTaskController::class, 'list'])->name('projects.steps.development.tasks.list');
    Route::get('project-tasks/completed', [AdminProjectTaskController::class, 'completed'])->name('project-tasks.completed');
    Route::post('project-tasks/{projectTask}/complete', [AdminProjectTaskController::class, 'complete'])->name('project-tasks.complete');
    Route::put('project-tasks/{projectTask}', [AdminProjectTaskController::class, 'update'])->name('project-tasks.update');
    Route::delete('project-tasks/{projectTask}', [AdminProjectTaskController::class, 'destroy'])->name('project-tasks.destroy');
    Route::post('project-task-items/{projectTaskItem}/toggle', [AdminProjectTaskController::class, 'toggleItem'])
        ->name('project-task-items.toggle');

    // Tasks hub
    Route::get('tasks', [AdminTaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/calendar', [AdminTaskController::class, 'calendar'])->name('tasks.calendar');
    Route::post('tasks/calendar', [AdminTaskController::class, 'storeCalendar'])->name('tasks.calendar.store');
    Route::put('tasks/calendar/{projectTask}', [AdminTaskController::class, 'updateCalendar'])->name('tasks.calendar.update');
    Route::delete('tasks/calendar/{projectTask}', [AdminTaskController::class, 'destroyCalendar'])->name('tasks.calendar.destroy');
    Route::get('tasks/kanban', [AdminTaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('tasks/kanban/{projectTask}/status', [AdminTaskController::class, 'updateStatus'])->name('tasks.kanban.status');
    Route::get('tasks/completed', [AdminTaskController::class, 'completed'])->name('tasks.completed');

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

    // Prospecao MM Cloud
    Route::prefix('mmcloud/prospeccao')->name('commercial.prospeccao.')->middleware('can.commercial')->group(function () {
        Route::get('/',                   [CommercialProspeccaoController::class, 'index'])->name('index');
        Route::post('/',                  [CommercialProspeccaoController::class, 'store'])->name('store');
        Route::patch('{cloudLead}',       [CommercialProspeccaoController::class, 'update'])->name('update');
        Route::delete('{cloudLead}',      [CommercialProspeccaoController::class, 'destroy'])->name('destroy');
        Route::patch('{cloudLead}/field', [CommercialProspeccaoController::class, 'updateField'])->name('updateField');
    });

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

        // Script de Vendas
        Route::get('script-vendas', [CommercialScriptVendasController::class, 'index'])->name('script-vendas.index');
    });
});

// Rotas publicas para briefings assinados
Route::name('public.')->group(function () {
    Route::get('briefing/qualitative/{project}', [PublicBriefingQualitativeController::class, 'show'])
        ->name('briefing.qualitative')
        ->middleware('signed');

    Route::post('briefing/qualitative/{project}/save', [PublicBriefingQualitativeController::class, 'save'])
        ->name('briefing.qualitative.save');
});
