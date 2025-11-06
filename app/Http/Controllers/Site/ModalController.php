<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ProjectProcess;
use App\Models\Project;
use App\Models\Skill;

class ModalController extends Controller
{
    public function content(string $type, string $slug)
    {
        if ($type === 'services') {
            $service = Service::query()
                ->where('slug', $slug)
                ->first();
            if (!$service) {
                return response('<p>Serviço não encontrado.</p>', 404);
            }
            // Carregar relações que podem enriquecer o modal, se existirem
            $service->load([
                'info',
                'benefits' => fn($q) => $q->orderBy('order'),
                'features' => fn($q) => $q->orderBy('order'),
                'ctas',
            ]);
            return response()->view('components.content.services.show', compact('service'));
        }

        if ($type === 'projects') {
            $project = Project::query()
                ->where('slug', $slug)
                ->first();
            if (!$project) {
                return response('<p>Projeto não encontrado.</p>', 404);
            }
            $project->load([
                'client',
                'service',
                'challenges',
                'solutions',
                'projectProcesses' => fn($q) => $q->orderBy('order'),
                'projectProcesses.images' => fn($q) => $q->orderBy('order'),
                'skills',
                'skillLinks' => fn($q) => $q->orderBy('order'),
                'skillLinks.skill',
                'skillLinks.competency',
                'tasks' => fn($q) => $q
                    ->with(['skill', 'competency'])
                    ->orderBy('skill_id')
                    ->orderBy('id'),
            ]);

            // Agrupar as competências por Skill
            $skillGroups = $project->skillLinks
                ->groupBy('skill_id')
                ->map(function ($items) {
                    return [
                        'skill' => optional($items->first()->skill)->name,
                        'competencies' => $items->map(fn($it) => optional($it->competency)->competency)->filter()->values(),
                    ];
                })->values();

            // Midias sociais do cliente (se houver pivot client_social_media com 'user')
            $clientSocials = collect();
            if ($project->client) {
                $project->client->loadMissing(['socialMedias']);
                $clientSocials = $project->client->socialMedias->map(function ($sm) {
                    return [
                        'name' => $sm->name ?? null,
                        'icon' => $sm->icon ?? null,
                        'url' => $sm->pivot?->user,
                    ];
                })->filter(fn($it) => !empty($it['url']))->values();
            }

            // Processos (se quiser exibir algo plano por enquanto)
            $processes = collect();

            // Alias $projeto para compatibilidade com o template atual
            $projeto = $project;

            return response()->view('components.content.projects.show', compact(
                'project',
                'projeto',
                'skillGroups',
                'clientSocials',
                'processes'
            ));
        }

        if ($type === 'skills') {
            $skill = Skill::query()->where('slug', $slug)->first();
            if (!$skill) {
                return response('<p>Skill não encontrada.</p>', 404);
            }
            $skill->load(['competencies' => fn($q) => $q->orderBy('competency')]);
            return response()->view('components.content.skills.show', compact('skill'));
        }

        // Fallback para conteúdos estáticos anteriores
        $view = "components.content.$type.$slug";
        if (view()->exists($view)) {
            return response()->view($view);
        }
        return response('<p>Conteúdo não encontrado.</p>', 404);
    }

    public function process(ProjectProcess $projectProcess)
    {
        $projectProcess->load([
            'project',
            'process',
            'images' => function ($q) {
                $q->orderBy('order');
            },
        ]);

        $images = $projectProcess->images->map(function ($img) {
            return [
                'id' => $img->id,
                'src' => asset($img->image),
                'title' => $img->title,
                'description' => $img->description,
                'solution' => $img->solution,
                'order' => $img->order,
            ];
        })->values();

        return response()->json([
            'project' => [
                'id' => $projectProcess->project?->id,
                'name' => $projectProcess->project?->name,
            ],
            'process' => [
                'id' => $projectProcess->process?->id,
                'name' => $projectProcess->process?->name,
                'slug' => $projectProcess->process?->slug,
                'icon' => $projectProcess->process?->icon,
            ],
            'description' => $projectProcess->description,
            'images' => $images,
        ]);
    }
}
