@php($groups = $project->skillLinks->groupBy('skill_id'))
@if ($project->skillLinks->isEmpty())
    <p class="text-sm text-gray-600">Nenhuma competência vinculada.</p>
@else
    @foreach ($groups as $skillId => $items)
        <div class="mb-3">
            <div class="text-sm font-semibold text-gray-800 mb-1">{{ optional($items->first()->skill)->name }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($items as $link)
                    <form method="POST" action="{{ route('admin.project-skill-competency.destroy', $link) }}" class="inline js-psc-delete" data-psc-id="{{ $link->id }}">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1 rounded-full bg-gray-100 hover:bg-red-600 hover:text-white text-xs border">
                            {{ optional($link->competency)->competency }} ✕
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endforeach
@endif

