<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalPage;
use App\Models\Project;
use App\Models\ProjectPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ProjectPageController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'pages' => ['nullable', 'array'],
            'pages.*' => ['integer', 'exists:global_pages,id'],
        ]);

        $selectedIds = collect($data['pages'] ?? [])
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return $this->respond($request, [
                'status' => 'noop',
                'message' => 'Nenhuma página selecionada.',
            ], back()->with('status', 'Nenhuma página selecionada.'));
        }

        $existingGlobalIds = $project->pages()
            ->whereNotNull('global_page_id')
            ->pluck('global_page_id')
            ->all();

        $globalPages = GlobalPage::query()
            ->whereIn('id', $selectedIds)
            ->get();

        $created = [];
        $maxOrder = $project->pages()->max('order') ?? 0;
        $slugSuffix = $project->pages()->count();

        foreach ($globalPages as $globalPage) {
            if (in_array($globalPage->id, $existingGlobalIds, true)) {
                continue;
            }

            $slugSuffix++;
            $page = $project->pages()->create([
                'global_page_id' => $globalPage->id,
                'name' => $globalPage->name,
                'slug' => Str::slug("{$project->slug}-{$globalPage->slug}-{$slugSuffix}"),
                'order' => ++$maxOrder ?: 1,
                'is_active' => true,
            ]);

            $created[] = $page->fresh(['components', 'globalPage']);
        }

        $createdCount = count($created);
        $responsePayload = [
            'status' => $createdCount ? 'ok' : 'noop',
            'created' => $created,
        ];

        if (Schema::hasColumn('projects', 'pages_seeded') && !$project->pages_seeded) {
            $project->updateQuietly(['pages_seeded' => true]);
        }

        $message = $createdCount
            ? 'Páginas adicionadas ao projeto.'
            : 'Nenhuma nova página foi adicionada.';

        return $this->respond($request, $responsePayload, back()->with('status', $message));
    }

    public function update(Request $request, ProjectPage $projectPage)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $projectPage->update([
            'name' => $data['name'],
            'order' => $data['order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->respond(
            $request,
            ['status' => 'ok', 'page' => $projectPage->fresh('components', 'globalPage')],
            back()->with('status', 'Página atualizada.')
        );
    }

    public function destroy(Request $request, ProjectPage $projectPage)
    {
        $id = $projectPage->id;
        $projectPage->delete();

        return $this->respond(
            $request,
            ['status' => 'ok', 'removed' => true, 'id' => $id],
            back()->with('status', 'Página removida do projeto.')
        );
    }

    protected function respond(Request $request, array $payload, $defaultResponse)
    {
        if ($request->ajax()) {
            return response()->json($payload);
        }

        return $defaultResponse;
    }

    public function updateAll(Request $request, Project $project)
    {
        $data = $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.name' => ['required', 'string', 'max:255'],
            'pages.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['pages'] as $id => $fields) {
            $page = $project->pages()->find($id);
            if ($page) {
                $page->update([
                    'name' => $fields['name'],
                    'order' => $fields['order'],
                    'is_active' => isset($fields['is_active']) && $fields['is_active'] ? true : false,
                ]);
            }
        }

        return back()->with('status', 'Páginas atualizadas com sucesso!');
    }
}
