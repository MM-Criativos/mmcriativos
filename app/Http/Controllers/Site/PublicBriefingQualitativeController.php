<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlanningBriefingQualitative;
use App\Models\PlanningBriefingQualitativeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicBriefingQualitativeController extends Controller
{
    /**
     * Mostra o formulário qualitativo para o cliente
     */
    public function show(Project $project)
    {
        $qualitatives = PlanningBriefingQualitative::normalizeForProject($project);

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
        $qualitatives = PlanningBriefingQualitative::normalizeForProject($project)->load('template');

        $rules = [
            'responses' => ['nullable', 'array'],
        ];

        foreach ($qualitatives as $qualitative) {
            $template = $qualitative->template;

            if (!$template) {
                continue;
            }

            $key = "responses.{$qualitative->id}";
            $isRequired = (bool) ($template->is_required ?? false);

            switch ($template->type) {
                case 'multi_choice':
                    $rules[$key] = array_merge($isRequired ? ['required'] : ['nullable'], ['array']);
                    $rules["{$key}.*"] = ['nullable', 'string'];
                    break;
                case 'choice':
                case 'textarea':
                case 'text':
                    $rules[$key] = array_merge($isRequired ? ['required'] : ['nullable'], ['string']);
                    break;
                case 'file':
                    $rules[$key] = array_merge($isRequired ? ['required'] : ['nullable'], ['file', 'max:10240']);
                    break;
                default:
                    $rules[$key] = array_merge($isRequired ? ['required'] : ['nullable'], ['string']);
                    break;
            }
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($project, $qualitatives, $validated, $request) {
            PlanningBriefingQualitativeResponse::where('project_id', $project->id)->delete();

            foreach ($qualitatives as $qualitative) {
                $template = $qualitative->template;

                if (!$template) {
                    continue;
                }

                $key = "responses.{$qualitative->id}";
                $answer = null;
                $filePath = null;

                switch ($template->type) {
                    case 'file':
                        if ($request->hasFile($key)) {
                            $uploaded = $request->file($key);
                            $storedPath = $uploaded->store("briefings/qualitative/{$project->id}", 'public');
                            $filePath = Storage::disk('public')->url($storedPath);
                            $answer = $uploaded->getClientOriginalName();
                        }
                        break;

                    case 'multi_choice':
                        $values = $request->input($key, []);
                        if (is_array($values)) {
                            $filtered = array_values(array_filter($values, fn($value) => $value !== null && $value !== ''));
                            if (!empty($filtered)) {
                                $answer = json_encode($filtered, JSON_UNESCAPED_UNICODE);
                            }
                        }
                        break;

                    default:
                        $value = $request->input($key);
                        if (is_string($value)) {
                            $trimmed = trim($value);
                            $answer = $trimmed !== '' ? $trimmed : null;
                        }
                        break;
                }

                if ($answer === null && $filePath === null) {
                    continue;
                }

                PlanningBriefingQualitativeResponse::create([
                    'briefing_id' => $qualitative->id,
                    'template_id' => $qualitative->template_id,
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                    'answer' => $answer,
                    'file_path' => $filePath,
                    'type' => $template->type ?? 'text',
                ]);
            }

            // Atualizar status do planejamento para in_progress
            $project->planning->update([
                'status' => 'in_progress'
            ]);
        });

        return redirect(config('app.url'))
            ->with('status', 'Obrigado! Suas respostas foram salvas com sucesso.');
    }
}
