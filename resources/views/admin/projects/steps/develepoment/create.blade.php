@php
    $statusOptions = \App\Models\ProjectTask::STATUSES;
    $skillOptions = $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);
@endphp

<div x-cloak x-show="createTaskModal" x-transition.opacity
    class="fixed inset-0 z-40 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black bg-opacity-40" @click="createTaskModal = false"></div>

    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
        @keydown.escape.window="createTaskModal = false">
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Nova tarefa de desenvolvimento</h3>
                <p class="text-sm text-gray-500">Cadastre tarefas com skill, compet&ecirc;ncia e respons&aacute;vel definido.</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" @click="createTaskModal = false">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            @if ($skillOptions->isEmpty())
                <div class="border border-dashed border-orange-300 rounded-lg bg-orange-50 text-orange-700 text-sm p-4">
                    Cadastre skills e compet&ecirc;ncias no m&oacute;dulo de Skills para liberar o cadastro de tarefas.
                </div>
            @else
                <form method="POST" action="{{ route('admin.projects.tasks.store', $project) }}"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    x-data="{
                        options: @js($skillOptions),
                        skill: @js(old('skill_id')),
                        competency: @js(old('skill_competency_id')),
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                        <select name="skill_id" x-model="skill" @change="ensureCompetency()" required
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="">Selecione...</option>
                            <template x-for="option in options" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                        @error('skill_id', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compet&ecirc;ncia</label>
                        <select name="skill_competency_id" x-model="competency" required
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="">Selecione...</option>
                            <template x-for="competencyOption in competencies" :key="competencyOption.id">
                                <option :value="competencyOption.id" x-text="competencyOption.name"></option>
                            </template>
                        </select>
                        @error('skill_competency_id', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">T&iacute;tulo</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        @error('title', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', \App\Models\ProjectTask::STATUS_PENDING) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Respons&aacute;vel</label>
                        <select name="assigned_to"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="">Definir depois</option>
                            @foreach ($teamMembers as $member)
                                <option value="{{ $member->id }}" @selected(old('assigned_to') == $member->id)>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descri&ccedil;&atilde;o</label>
                        <textarea name="description" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ old('description') }}</textarea>
                        @error('description', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas de progresso</label>
                        <textarea name="progress_notes" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ old('progress_notes') }}</textarea>
                        @error('progress_notes', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-3">
                        <button type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md border text-sm text-gray-700 hover:bg-gray-50"
                            @click="createTaskModal = false">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 text-white rounded-md text-sm font-medium hover:bg-orange-700">
                            <i class="fa-solid fa-plus"></i>
                            Adicionar tarefa
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
