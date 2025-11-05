<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingQualitative;
use App\Models\PlanningBriefingQualitativeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicBriefingQualitativeController extends Controller
{
    /**
     * Mostra o formulário qualitativo para o cliente
     */
    public function show(Project $project)
    {
        $qualitatives = PlanningBriefingQualitative::with('template')
            ->where('project_id', $project->id)
            ->get();

        if ($qualitatives->isEmpty()) {
            abort(404, 'Questionário não encontrado.');
        }

        return view('public.briefing.qualitative', compact('project', 'qualitatives'));
    }

    /**
     * Salva as respostas do cliente
     */
    public function save(Request $request, Project $project)
    {
        $request->validate([
            'responses' => ['required', 'array'],
            'responses.*' => ['required']
        ]);

        $qualitatives = PlanningBriefingQualitative::where('project_id', $project->id)->get();
        $responses = $request->input('responses', []);

        DB::transaction(function () use ($project, $qualitatives, $responses) {
            foreach ($responses as $qualitativeId => $response) {
                $qualitative = $qualitatives->firstWhere('id', $qualitativeId);

                if (!$qualitative) continue;

                PlanningBriefingQualitativeResponse::create([
                    'qualitative_id' => $qualitative->id,
                    'template_id' => $qualitative->template_id,
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                    'response' => is_array($response) ? $response : [$response],
                ]);
            }

            // Atualizar status do planejamento para in_progress
            $project->planning->update([
                'status' => 'in_progress'
            ]);
        });

        return redirect('https://mmcriativos.test')
            ->with('status', 'Obrigado! Suas respostas foram salvas com sucesso.');
    }
}
