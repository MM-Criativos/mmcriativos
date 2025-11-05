<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingRegua;
use App\Models\PlanningBriefingResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectPerceptionBriefingMail;

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
}
