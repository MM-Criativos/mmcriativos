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
     * Salva as questões selecionadas para o projeto
     */
    public function save(Request $request, Project $project)
    {
        $request->validate([
            'template_ids' => ['required', 'array'],
            'template_ids.*' => ['required', 'exists:qualitative_templates,id']
        ]);

        // Remove questões existentes
        PlanningBriefingQualitative::where('project_id', $project->id)->delete();

        // Insere novas questões
        $qualitatives = collect($request->template_ids)->map(function ($templateId) use ($project) {
            return [
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'template_id' => $templateId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        PlanningBriefingQualitative::insert($qualitatives->toArray());

        return response()->json([
            'message' => 'Questionário salvo com sucesso'
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

        $qualitatives = PlanningBriefingQualitative::with('template')
            ->where('project_id', $project->id)
            ->get();

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
