<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectPage;
use App\Models\ProjectPageComponent;
use App\Models\StorytellingComponent;
use Illuminate\Http\Request;

class ProjectPageComponentController extends Controller
{
    public function store(Request $request, ProjectPage $projectPage)
    {
        $data = $request->validate([
            'components' => ['required', 'array'],
            'components.*' => ['integer', 'exists:storytelling_components,id'],
        ]);

        $selected = collect($data['components'])->unique()->values();
        if ($selected->isEmpty()) {
            return $this->respond($request, [
                'status' => 'noop',
                'message' => 'Nenhum componente selecionado.',
            ], back()->with('status', 'Nenhum componente selecionado.'));
        }

        $existing = $projectPage->components()
            ->whereIn('storytelling_components.id', $selected)
            ->pluck('storytelling_components.id')
            ->all();

        $maxOrder = ProjectPageComponent::query()
            ->where('project_page_id', $projectPage->id)
            ->max('order') ?? 0;

        $components = StorytellingComponent::query()
            ->whereIn('id', $selected)
            ->orderBy('layer')
            ->orderBy('name')
            ->get();

        $attached = [];

        foreach ($components as $component) {
            if (in_array($component->id, $existing, true)) {
                continue;
            }

            $projectPage->pageComponents()->create([
                'component_id' => $component->id,
                'global_component_id' => $component->id,
                'order' => ++$maxOrder,
                'settings' => null,
                'is_visible' => true,
            ]);

            $attached[] = $component;
        }

        $status = $attached ? 'ok' : 'noop';
        $message = $attached ? 'Componentes adicionados à página.' : 'Nenhum novo componente foi adicionado.';

        return $this->respond(
            $request,
            [
                'status' => $status,
                'attached' => $attached,
                'page' => $projectPage->fresh('components'),
            ],
            back()->with('status', $message)
        );
    }

    public function update(Request $request, ProjectPageComponent $projectPageComponent)
    {
        $data = $request->validate([
            'order' => ['required', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $projectPageComponent->update([
            'order' => $data['order'],
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        return $this->respond(
            $request,
            ['status' => 'ok', 'component' => $projectPageComponent->fresh('component')],
            back()->with('status', 'Componente atualizado.')
        );
    }

    public function destroy(Request $request, ProjectPageComponent $projectPageComponent)
    {
        $id = $projectPageComponent->id;
        $projectPageComponent->delete();

        return $this->respond(
            $request,
            ['status' => 'ok', 'removed' => true, 'id' => $id],
            back()->with('status', 'Componente removido da página.')
        );
    }

    protected function respond(Request $request, array $payload, $defaultResponse)
    {
        if ($request->ajax()) {
            return response()->json($payload);
        }

        return $defaultResponse;
    }

    public function updateAll(Request $request, ProjectPage $projectPage)
    {
        $data = $request->validate([
            'components' => ['required', 'array'],
            'components.*.order' => ['required', 'integer', 'min:0'],
            'components.*.is_visible' => ['nullable', 'boolean'],
        ]);

        foreach ($data['components'] as $pivotId => $fields) {
            $componentPivot = $projectPage->components()->wherePivot('id', $pivotId)->first();
            if ($componentPivot) {
                $projectPage->components()->updateExistingPivot($componentPivot->id, [
                    'order' => $fields['order'],
                    'is_visible' => !empty($fields['is_visible']),
                ]);
            }
        }

        return back()->with('status', 'Componentes atualizados com sucesso!');
    }
}
