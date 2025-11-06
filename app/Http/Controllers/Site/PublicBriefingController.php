<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingRegua;
use App\Models\PlanningBriefingResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicBriefingController extends Controller
{
    public function perception(Project $project)
    {
        $project->load('client');
        $action = route('public.briefing.perception.save', $project);
        return view('public.briefing.perception', compact('project', 'action'));
    }

    public function savePerception(Request $request, Project $project)
    {
        if (!$project->client_id) {
            return back()->with('status', 'Projeto sem cliente associado.');
        }

        $data = $request->validate([
            'responses' => ['required', 'array'],
            'responses.*.value' => ['nullable', 'integer'],
            'responses.*.comment' => ['nullable', 'string'],
        ]);

        $payload = $data['responses'] ?? [];
        $clientId = $project->client_id;

        DB::transaction(function () use ($payload, $project, $clientId) {
            $reguaIds = array_map('intval', array_keys($payload));
            $reguas = PlanningBriefingRegua::whereIn('id', $reguaIds)->get()->keyBy('id');
            foreach ($payload as $reguaId => $row) {
                $reguaId = (int) $reguaId;
                if (!$reguas->has($reguaId)) continue;
                $value = isset($row['value']) ? (int)$row['value'] : null;
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

        $redirectUrl = config('app.url');
        $redirectDelay = 5000;
        $message = 'Seu formulário foi preenchido com sucesso! Muito obrigado por preencher, em breve entraremos em contato com você!';

        return view('public.briefing.perception-success', [
            'redirectUrl' => $redirectUrl,
            'redirectDelay' => $redirectDelay,
            'message' => $message,
            'project' => $project,
        ]);
    }
}
