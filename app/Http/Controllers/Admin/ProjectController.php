<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = Project::with('client')
            ->orderByDesc('finished_at')
            ->orderBy('name')
            ->get();

        return view('admin.projects.index', compact('projects'));
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
            'summary' => ['nullable', 'string'],
            // Cover pode ser imagem ou vídeo
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg,mov'],
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

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('projects', 'public');
            $data['cover'] = 'storage/' . $path;
        }

        if ($request->hasFile('thumb')) {
            $path = $request->file('thumb')->store('projects/thumbs', 'public');
            $data['thumb'] = 'storage/' . $path;
        }

        if ($request->hasFile('skill_cover')) {
            $path = $request->file('skill_cover')->store('projects/skills', 'public');
            $data['skill_cover'] = 'storage/' . $path;
        }

        $project = Project::create($data);

        return redirect()
            ->route('admin.projects.edit', $project)
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

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('projects', 'public');
            $data['cover'] = 'storage/' . $path;
        }

        if ($request->hasFile('thumb')) {
            $path = $request->file('thumb')->store('projects/thumbs', 'public');
            $data['thumb'] = 'storage/' . $path;
        }

        if ($request->hasFile('skill_cover')) {
            $path = $request->file('skill_cover')->store('projects/skills', 'public');
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
}
