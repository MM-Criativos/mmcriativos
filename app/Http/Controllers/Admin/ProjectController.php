<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\Process;
use App\Models\GlobalPage;
use App\Models\StorytellingComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Services\Upload\ImageUploadService;
use App\Services\Upload\VideoUploadService;
use App\Support\StorageHelper;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('admin.projects.index');
    }

    public function progress()
    {
        $projects = Project::with('client')
            ->whereNull('finished_at')
            ->orderBy('name')
            ->get();

        return view('admin.projects.progress.index', compact('projects'));
    }

    public function completed()
    {
        $projects = Project::with('client')
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->orderBy('name')
            ->get();

        return view('admin.projects.completed.index', compact('projects'));
    }

    public function create()
    {
        $clients = Client::query()->orderBy('name')->get(['id', 'name']);
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        return view('admin.projects.create', compact('clients', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:projects,slug'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            // Cover pode ser imagem ou vídeo
            'thumb' => ['nullable', 'image'], // ✅ novo campo
            'skill_cover' => ['nullable', 'image'],
            'video' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 2;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // Campos de mídia serão definidos na etapa de apresentação.

        

        

        $project = Project::create($data);

        return redirect()
            ->route('admin.projects.steps.show', $project)
            ->with('status', 'Projeto criado com sucesso.');
    }

    public function edit(Project $project)
    {
        $project->load([
            'client',
            'service',
            'challenges',
            'solutions',
            'projectProcesses' => fn($q) => $q->orderBy('order'),
            'projectProcesses.process',
            'projectProcesses.images' => fn($q) => $q->orderBy('order'),
            'skills',
            'skillLinks' => fn($q) => $q->orderBy('order'),
            'skillLinks.skill',
            'skillLinks.competency',
        ]);

        $clients = Client::query()->orderBy('name')->get(['id', 'name']);
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $processes = \App\Models\Process::query()->orderBy('order')->orderBy('name')->get(['id', 'name']);
        $skills = \App\Models\Skill::with(['competencies' => fn($q) => $q->orderBy('competency')])
            ->orderBy('name')
            ->get();

        return view('admin.projects.edit', compact('project', 'clients', 'services', 'processes', 'skills'));
    }

    public function update(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:projects,slug,' . $project->id],
            'client_id' => ['nullable', 'exists:clients,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'summary' => ['nullable', 'string'],
            // Cover pode ser imagem ou vídeo
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg,mov'],
            'thumb' => ['nullable', 'image'], // ✅ novo campo
            'skill_cover' => ['nullable', 'image'],
            'video' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $project->slug;
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $mime = (string) $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                /** @var ImageUploadService $uploader */
                $uploader = app(ImageUploadService::class);
                $basename = "project-{$slug}-cover";
                $path = $uploader->store($file, 'projects', ['basename' => $basename]);
                $data['cover'] = 'storage/' . $path;
            } else if (str_starts_with($mime, 'video/')) {
                /** @var VideoUploadService $vid */
                $vid = app(VideoUploadService::class);
                $basename = "project-{$slug}-cover";
                $out = $vid->transcode($file, 'projects', ['basename' => $basename]);
                $data['cover'] = 'storage/' . $out['video'];
                if (empty($data['thumb']) && !empty($out['poster'])) {
                    $data['thumb'] = 'storage/' . $out['poster'];
                }
            } else {
                $path = $file->store('projects', 'public');
                $data['cover'] = 'storage/' . $path;
            }
        }

        if ($request->hasFile('thumb')) {
            StorageHelper::deletePublic($project->thumb);
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = "project-{$slug}-thumb";
            $path = $uploader->store($request->file('thumb'), 'projects/thumbs', ['basename' => $basename]);
            $data['thumb'] = 'storage/' . $path;
        }

        if ($request->hasFile('skill_cover')) {
            StorageHelper::deletePublic($project->skill_cover);
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = "project-{$slug}-skill-cover";
            $path = $uploader->store($request->file('skill_cover'), 'projects/skills', ['basename' => $basename]);
            $data['skill_cover'] = 'storage/' . $path;
        }

        $project->update($data);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'client_id' => $project->client_id,
                    'service_id' => $project->service_id,
                    'summary' => $project->summary,
                'video' => $project->video,
                'cover' => $project->cover ? asset($project->cover) : null,
                'thumb' => $project->thumb ? asset($project->thumb) : null, // ✅
                'skill_cover' => $project->skill_cover ? asset($project->skill_cover) : null,
            ],
        ]);
        }

        return back()->with('status', 'Projeto atualizado.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('status', 'Projeto removido.');
    }

    public function updateSummary(Request $request, Project $project)
    {
        $data = $request->validate([
            'summary' => ['nullable', 'string'],
        ]);

        $project->update([
            'summary' => $data['summary'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'project' => $project->only(['id', 'summary']),
            ]);
        }

        return back()->with('status', 'Resumo do projeto atualizado.');
    }

    public function steps(Project $project)
    {
        $project->load([
            'client',
            'service',
            'planning.briefingResponses',
            'planning.interpretacao',
            'planning.kickoff',
            'challenges',
            'solutions',
            'projectProcesses' => fn($q) => $q->orderBy('order'),
            'projectProcesses.process',
            'projectProcesses.images' => fn($q) => $q->orderBy('order'),
            'pages' => fn($query) => $query->orderBy('order')->with([
                'components' => fn($componentQuery) => $componentQuery->orderBy('project_page_component.order'),
                'globalPage',
            ]),
        ]);

        // Removido: não preenche mais respostas automaticamente.
        // A tabela planning_briefing_responses deve ser preenchida apenas quando o cliente
        // responder o formulário público enviado por e-mail.
        $tab = request()->query('tab', 'planning');

        $processes = Process::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $availablePages = GlobalPage::query()
            ->orderBy('name')
            ->get();

        $availableComponents = StorytellingComponent::query()
            ->orderBy('layer')
            ->orderBy('name')
            ->get();

        return view('admin.projects.steps.show', compact(
            'project',
            'tab',
            'processes',
            'availablePages',
            'availableComponents'
        ));
    }

}
