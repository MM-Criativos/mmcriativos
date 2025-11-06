@php
    $errorBag = 'projectTasksUpdate_' . $task->id;
    $statusOptions = \App\Models\ProjectTask::STATUSES;
    $skillOptions = $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);
@endphp

<div x-cloak x-show="modalOpen" class="fixed inset-0 z-30 flex items-center justify-center px-4 py-8 bg-black bg-opacity-50">
    <div @click.away="modalOpen = false" @keydown.escape.window="modalOpen = false"
        class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h4 class="text-lg font-semibold text-gray-900">Editar tarefa</h4>
                <p class="text-sm text-gray-500">{{ $task->title }}</p>
            </div>
            <button type="button" @click="modalOpen = false" class="text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <form id="task-update-{{ $task->id }}" method="POST" action="{{ route('admin.project-tasks.update', $task) }}"
                class="grid grid-cols-1 md:grid-cols-2 gap-4"
                x-data="{
                    options: @js($skillOptions),
                    skill: @js($task->skill_id),
                    competency: @js($task->skill_competency_id),
                    get competencies() {
                        const selected = this.options.find(option => String(option.id) === String(this.skill));
                        return selected ? selected.competencies : [];
                    },
                    ensureCompetency() {
                        if (!this.competencies.some(option => String(option.id) === String(this.competency))) {
                            this.competency = '';
                        }
                    }
                }"
                x-init="ensureCompetency()">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                    <select name="skill_id" x-model="skill" @change="ensureCompetency()" required
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Selecione...</option>
                        <template x-for="option in options" :key="option.id">
                            <option :value="option.id" x-text="option.name"></option>
                        </template>
                    </select>
                    @error('skill_id', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                    <select name="skill_competency_id" x-model="competency" required
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Selecione...</option>
                        <template x-for="competencyOption in competencies" :key="competencyOption.id">
                            <option :value="competencyOption.id" x-text="competencyOption.name"></option>
                        </template>
                    </select>
                    @error('skill_competency_id', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="title" value="{{ $task->title }}" required
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                    @error('title', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status"
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select name="assigned_to"
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Definir depois</option>
                        @foreach ($teamMembers as $member)
                            <option value="{{ $member->id }}" @selected($task->assigned_to === $member->id)>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" rows="3"
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ $task->description }}</textarea>
                    @error('description', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas de progresso</label>
                    <textarea name="progress_notes" rows="3"
                        class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ $task->progress_notes }}</textarea>
                    @error('progress_notes', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </form>

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between border-t border-gray-100 pt-4">
                <button type="button" @click="modalOpen = false"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                    Cancelar
                </button>

                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.project-tasks.destroy', $task) }}"
                        onsubmit="return confirm('Deseja remover esta tarefa?');">
                        @csrf
                        @method('DELETE')
                        <button
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm bg-red-600 text-white hover:bg-red-700">
                            <i class="fa-solid fa-trash"></i>
                            Excluir
                        </button>
                    </form>

                    <button type="submit" form="task-update-{{ $task->id }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm bg-orange-600 text-white hover:bg-orange-700">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Salvar alterações
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

