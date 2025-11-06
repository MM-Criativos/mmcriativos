<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingQualitative;
use App\Models\QualitativeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\ProjectQualitativeBriefingMail;

class ProjectPlanningQualitativeController extends Controller
{
    /**
     * Mostra o formulário para criar um novo questionário
     */
    public function create(Project $project)
    {
        return view('admin.projects.steps.planning.create', [
            'project' => $project
        ]);
    }

    /**
     * Retorna os templates disponíveis para o questionário
     */
    public function templates(Project $project)
    {
        $templates = QualitativeTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'templates' => $templates
        ]);
    }

    /**
     * Exibe o formulário de edição para selecionar as perguntas do questionário.
     */
    public function edit(Project $project)
    {
        $qualitatives = PlanningBriefingQualitative::normalizeForProject($project);

        $templates = QualitativeTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $selectedTemplateIds = $qualitatives->pluck('template_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('admin.projects.steps.planning.edit', [
            'project' => $project,
            'templates' => $templates,
            'selectedTemplateIds' => $selectedTemplateIds,
        ]);
    }

    /**
     * Salva as questões selecionadas para o projeto
     */
    public function save(Request $request, Project $project)
    {
        $request->validate([
            'template_ids' => ['nullable', 'array'],
            'template_ids.*' => ['integer', 'exists:qualitative_templates,id']
        ]);

        $templateIds = collect($request->input('template_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        PlanningBriefingQualitative::syncForProject($project, $templateIds);

        return redirect()
            ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning'])
            ->with('status', $templateIds->isEmpty()
                ? 'Questionário qualitativo removido.'
                : 'Questionário atualizado com sucesso!');
    }

    /**
     * Exibe uma pré-visualização do questionário qualitativo selecionado para o projeto.
     */
    public function preview(Project $project)
    {
        $qualitatives = PlanningBriefingQualitative::normalizeForProject($project);

        if ($qualitatives->isEmpty()) {
            return redirect()
                ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning'])
                ->with('error', 'Nenhum questionário qualitativo encontrado para visualizar.');
        }

        $questions = $qualitatives->map(function ($qualitative) {
            $template = $qualitative->template;

            if (!$template) {
                return null;
            }

            return [
                'category' => $template->category ?? 'Outras perguntas',
                'question' => $template->question,
                'type' => $template->type,
                'options' => $template->options ?? [],
                'placeholder' => $template->placeholder,
                'is_required' => (bool) ($template->is_required ?? false),
            ];
        })->filter()->values();

        if ($questions->isEmpty()) {
            return redirect()
                ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning'])
                ->with('error', 'As perguntas do questionário não foram encontradas para visualização.');
        }

        return view('admin.projects.steps.planning.preview', [
            'project' => $project,
            'questions' => $questions,
        ]);
    }

    /**
     * Envia o questionário por email para o cliente
     */
    public function sendEmail(Request $request, Project $project)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $qualitatives = PlanningBriefingQualitative::normalizeForProject($project);

        if ($qualitatives->isEmpty()) {
            return back()->with('error', 'Nenhuma questão selecionada para envio.');
        }

        // Gerar URL assinada para o formulário
        $url = URL::signedRoute('public.briefing.qualitative', $project);

        // Enviar email
        Mail::to($request->email)
            ->send(new ProjectQualitativeBriefingMail($project, $url));

        return back()->with('status', 'E-mail enviado com sucesso!');
    }
}
