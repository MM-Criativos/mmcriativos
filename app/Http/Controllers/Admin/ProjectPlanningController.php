<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingRegua;
use App\Models\PlanningBriefingResponse;
use App\Models\PlanningInterpretacao;
use App\Models\PlanningKickoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectPerceptionBriefingMail;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ProjectPlanningController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function saveScale(Request $request, Project $project): RedirectResponse
    {
        if (!$project->client_id) {
            return back()->with('status', 'Defina o cliente do projeto antes de salvar a escala.');
        }

        $data = $request->validate([
            'responses' => ['required', 'array'],
            'responses.*.value' => ['nullable', 'integer'],
            'responses.*.comment' => ['nullable', 'string'],
        ]);

        $clientId = $project->client_id;
        $payload = $data['responses'] ?? [];

        DB::transaction(function () use ($payload, $project, $clientId) {
            $reguaIds = array_map('intval', array_keys($payload));
            $reguas = PlanningBriefingRegua::whereIn('id', $reguaIds)->get()->keyBy('id');

            foreach ($payload as $reguaId => $row) {
                $reguaId = (int) $reguaId;
                if (!$reguas->has($reguaId)) {
                    continue;
                }
                $value = isset($row['value']) ? (int) $row['value'] : null;
                $comment = $row['comment'] ?? null;

                $resp = PlanningBriefingResponse::firstOrNew([
                    'project_id' => $project->id,
                    'client_id' => $clientId,
                    'briefing_regua_id' => $reguaId,
                ]);
                $resp->value = $value;
                $resp->comment = $comment;
                $resp->save();
            }
        });

        return back()->with('status', 'Escalas salvas com sucesso.');
    }

    public function sendScaleEmail(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        $clientName = optional($project->client)->name ?? 'cliente';

        // Gere o link usando o host atual do painel para evitar localhost/assinaturas inválidas
        $currentRoot = $request->getSchemeAndHttpHost();
        $revertRoot = config('app.url');
        try {
            URL::forceRootUrl($currentRoot);
            $link = URL::temporarySignedRoute(
                'public.briefing.perception',
                now()->addDays(14),
                ['project' => $project->id]
            );
        } finally {
            if (!empty($revertRoot)) {
                URL::forceRootUrl($revertRoot);
            }
        }

        Mail::to($data['email'])->send(new ProjectPerceptionBriefingMail($clientName, $link));
        return back()->with('status', 'Briefing enviado por e-mail.');
    }

    public function saveInterpretation(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'analise_publico' => ['nullable', 'string'],
            'analise_concorrencia' => ['nullable', 'string'],
            'diretrizes_visuais' => ['nullable', 'string'],
            'definicao_escopo' => ['nullable', 'string'],
            'observacoes_tecnicas' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'review', 'approved'])],
            'approved_at' => ['nullable', 'date'],
        ]);

        $timezone = config('app.timezone') ?: date_default_timezone_get();

        $interpretacao = PlanningInterpretacao::firstOrNew([
            'project_id' => $project->id,
        ]);
        $interpretacao->client_id = $project->client_id;

        $interpretacao->analise_publico = filled($data['analise_publico'] ?? null) ? trim($data['analise_publico']) : null;
        $interpretacao->analise_concorrencia = filled($data['analise_concorrencia'] ?? null) ? trim($data['analise_concorrencia']) : null;
        $interpretacao->definicao_escopo = filled($data['definicao_escopo'] ?? null) ? trim($data['definicao_escopo']) : null;
        $interpretacao->observacoes_tecnicas = filled($data['observacoes_tecnicas'] ?? null) ? trim($data['observacoes_tecnicas']) : null;
        $interpretacao->diretrizes_visuais = filled($data['diretrizes_visuais'] ?? null) ? trim($data['diretrizes_visuais']) : null;

        $interpretacao->status = $data['status'];

        if ($interpretacao->status === 'approved') {
            if (!empty($data['approved_at'])) {
                $interpretacao->approved_at = Carbon::createFromFormat('Y-m-d\TH:i', $data['approved_at'], $timezone);
            } elseif (!$interpretacao->approved_at) {
                $interpretacao->approved_at = now($timezone);
            }
        } else {
            $interpretacao->approved_at = null;
        }

        $interpretacao->save();

        return redirect()
            ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning'])
            ->with('status', 'Interpretação atualizada com sucesso.');
    }

    public function saveKickoff(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'objetivo' => ['nullable', 'string'],
            'resumo_alinhamento' => ['nullable', 'string'],
            'tarefas_iniciais' => ['nullable', 'string'],
            'responsaveis' => ['nullable', 'string'],
            'materiais_apresentados' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['agendado', 'realizado', 'aprovado'])],
            'data_reuniao' => ['nullable', 'date'],
            'approved_at' => ['nullable', 'date'],
        ]);

        $timezone = config('app.timezone') ?: date_default_timezone_get();

        $kickoff = PlanningKickoff::firstOrNew([
            'project_id' => $project->id,
        ]);
        $kickoff->client_id = $project->client_id;

        $kickoff->titulo = filled($data['titulo'] ?? null) ? trim($data['titulo']) : null;
        $kickoff->objetivo = filled($data['objetivo'] ?? null) ? trim($data['objetivo']) : null;
        $kickoff->resumo_alinhamento = filled($data['resumo_alinhamento'] ?? null) ? trim($data['resumo_alinhamento']) : null;
        $kickoff->tarefas_iniciais = filled($data['tarefas_iniciais'] ?? null) ? trim($data['tarefas_iniciais']) : null;
        $kickoff->responsaveis = filled($data['responsaveis'] ?? null) ? trim($data['responsaveis']) : null;
        $kickoff->materiais_apresentados = filled($data['materiais_apresentados'] ?? null) ? trim($data['materiais_apresentados']) : null;

        if (!empty($data['data_reuniao'])) {
            $kickoff->data_reuniao = Carbon::createFromFormat('Y-m-d', $data['data_reuniao'], $timezone)->startOfDay();
        } else {
            $kickoff->data_reuniao = null;
        }

        $kickoff->status = $data['status'];

        if ($kickoff->status === 'aprovado') {
            if (!empty($data['approved_at'])) {
                $kickoff->approved_at = Carbon::createFromFormat('Y-m-d\TH:i', $data['approved_at'], $timezone);
            } elseif (!$kickoff->approved_at) {
                $kickoff->approved_at = now($timezone);
            }
        } else {
            $kickoff->approved_at = null;
        }

        $kickoff->save();

        return redirect()
            ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning'])
            ->with('status', 'Kickoff atualizado com sucesso.');
    }
}
