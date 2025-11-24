<style>
    /* Modo claro */
    .icon-project.fa-duotone::before,
    .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Camada primária */
    }

    .icon-project.fa-duotone::after,
    .icon-project.fad::after {
        color: rgb(0 0 0) !important;
        /* Camada secundária */
        opacity: 1 !important;
    }

    /* Modo escuro */
    .dark .icon-project.fa-duotone::before,
    .dark .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Mantém o laranja */
    }

    .dark .icon-project.fa-duotone::after,
    .dark .icon-project.fad::after {
        color: rgb(255 255 255) !important;
        /* Cinza escuro no dark mode */
        opacity: 1 !important;
    }

    /* NORMAL */
    .btn-mmcriativos {
        background-color: transparent !important;
        border: 2px solid #ff8800 !important;
        color: #000 !important;
        transition: all 0.25s ease-in-out !important;
    }

    /* HOVER com DEGRADÊ */
    .btn-mmcriativos:hover {
        background-image: linear-gradient(to right, #feb365, #ff8800) !important;
        border-color: 2px solid transparent !important;
        color: #000 !important;
    }

    /* DARK MODE — NORMAL */
    .dark .btn-mmcriativos {
        background-color: transparent !important;
        border: 2px solid #ff8800 !important;
        color: #fff !important;
    }

    /* DARK MODE — HOVER com DEGRADÊ */
    .dark .btn-mmcriativos:hover {
        background-image: linear-gradient(to right, #feb365, #ff8800) !important;
        border-color: 2px solid transparent !important;
        color: #fff !important;
    }
</style>

@php
    $projects = $projects instanceof \Illuminate\Support\Collection ? $projects : collect($projects ?? []);
    $skills = $skills instanceof \Illuminate\Support\Collection ? $skills : collect($skills ?? []);
    $teamMembers = $teamMembers instanceof \Illuminate\Support\Collection ? $teamMembers : collect($teamMembers ?? []);
    $statusOptions = is_iterable($statusOptions) ? $statusOptions : [];

    $skillOptions = $skills
        ->map(function ($skill) {
            return [
                'id' => (string) $skill->id,
                'name' => $skill->name,
                'competencies' => $skill->competencies
                    ->map(function ($competency) {
                        return [
                            'id' => (string) $competency->id,
                            'name' => $competency->competency,
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })
        ->values()
        ->all();

    $oldItems = collect(old('items', []));
    $initialItemsSource = $oldItems->isNotEmpty()
        ? $oldItems->toArray()
        : $task->items
            ->map(
                fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'skill_competency_id' => $item->skill_competency_id,
                    'assigned_to' => $item->assigned_to,
                ],
            )
            ->toArray();
    $initialTaskItems = collect($initialItemsSource)
        ->map(function ($item, $index) {
            return [
                'uid' => $item['uid'] ?? ($item['id'] ?? 'taskitem_' . $index . '_' . uniqid()),
                'id' => $item['id'] ?? null,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'skill_competency_id' => isset($item['skill_competency_id'])
                    ? (string) $item['skill_competency_id']
                    : '',
                'assigned_to' => isset($item['assigned_to']) ? (string) $item['assigned_to'] : '',
            ];
        })
        ->values()
        ->toArray();

    $plannedAtValue = old('planned_at', optional($task->planned_at)->format('d/m/Y H:i'));
    $initialProject = old('project_id', $task->project_id);
    $initialSkill = old('skill_id', $task->skill_id);
    $initialCompetency = old('skill_competency_id', $task->skill_competency_id);
    $initialAssigned = old('assigned_to', $task->assigned_to);
    $initialStatus = old('status', $task->status);
    $initialTitle = old('title', $task->title);
    $initialDescription = old('description', $task->description);
    $taskItemsErrors = collect($errors->getMessages() ?? [])
        ->filter(fn($messages, $key) => str_starts_with($key, 'items.'))
        ->flatten();
@endphp

<div id="edit-task-modal-{{ $task->id }}" tabindex="-1"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-full backdrop-blur-sm">
    <div class="relative w-full max-w-4xl">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-neutral-900 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Editar tarefa</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ $task->title }}</p>
                </div>
                <button type="button" data-modal-hide="edit-task-modal-{{ $task->id }}"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <form method="POST" action="{{ route('admin.tasks.calendar.update', $task) }}" class="space-y-6"
                    x-data="{
                        options: @js($skillOptions),
                        skill: @js((string) $initialSkill),
                        competency: @js((string) $initialCompetency),
                        project: @js((string) $initialProject),
                        items: @js($initialTaskItems),
                        nextUid: Date.now(),
                        get competencies() {
                            const selected = this.options.find(option => String(option.id) === String(this.skill));
                            return selected ? selected.competencies : [];
                        },
                        ensureCompetency() {
                            if (!this.competencies.some(option => String(option.id) === String(this.competency))) {
                                this.competency = '';
                            }
                            this.ensureItemsCompetencies();
                        },
                        ensureItemsCompetencies() {
                            const competencyIds = this.competencies.map(option => String(option.id));
                            this.items = this.items.map(item => {
                                if (!competencyIds.includes(String(item.skill_competency_id))) {
                                    item.skill_competency_id = '';
                                }
                                return item;
                            });
                        },
                        addItem() {
                            if (!this.skill) {
                                alert('Selecione a skill da tarefa antes de adicionar itens.');
                                return;
                            }
                            this.items.push({
                                uid: `edit_${this.nextUid++}`,
                                id: null,
                                title: '',
                                description: '',
                                skill_competency_id: this.competency || '',
                                assigned_to: '',
                            });
                        },
                        removeItem(index) {
                            this.items.splice(index, 1);
                        },
                        primaryCompetencyName() {
                            const option = this.competencies.find(option => String(option.id) === String(this.competency));
                            return option ? option.name : '';
                        },
                        primaryCompetencyOptionLabel() {
                            if (!this.skill) {
                                return 'Selecione uma skill para liberar as competências';
                            }
                            const name = this.primaryCompetencyName();
                            return name ? `Seguir competência principal (${name})` : 'Seguir competência principal da tarefa';
                        }
                    }" x-init="ensureCompetency();">
                    @csrf
                    @method('PUT')

                    @if ($taskItemsErrors->isNotEmpty())
                        <div class="bg-red-50 border border-red-100 text-xs text-red-600 rounded-md p-3 space-y-1">
                            @foreach ($taskItemsErrors as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Projeto</label>
                            <select name="project_id" x-model="project" required
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Selecione...</option>
                                @foreach ($projects as $projectOption)
                                    <option value="{{ $projectOption->id }}" @selected((string) $initialProject === (string) $projectOption->id)>
                                        {{ $projectOption->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Skill</label>
                            <select name="skill_id" x-model="skill" @change="ensureCompetency()" required
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Selecione...</option>
                                <template x-for="option in options" :key="option.id">
                                    <option :value="String(option.id)" :selected="String(option.id) === String(skill)"
                                        x-text="option.name"></option>
                                </template>
                            </select>
                            @error('skill_id')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Competência</label>
                            <select name="skill_competency_id" x-model="competency" required
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Selecione...</option>
                                <template x-for="competencyOption in competencies" :key="competencyOption.id">
                                    <option :value="String(competencyOption.id)"
                                        :selected="String(competencyOption.id) === String(competency)"
                                        x-text="competencyOption.name"></option>
                                </template>
                            </select>
                            @error('skill_competency_id')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                            <select name="status"
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($initialStatus === $value)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Responsável</label>
                            <select name="assigned_to"
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Definir depois</option>
                                @foreach ($teamMembers as $member)
                                    <option value="{{ $member->id }}" @selected((string) $initialAssigned === (string) $member->id)>
                                        {{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Planejado
                                para</label>
                            <input type="text" name="planned_at" value="{{ $plannedAtValue }}"
                                placeholder="dd/mm/aaaa hh:mm" data-calendar-datetime
                                class="w-full dark:!bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('planned_at')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Título</label>
                            <input type="text" name="title" value="{{ $initialTitle }}" required
                                class="w-full dark:!bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('title')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Descrição</label>
                            <textarea name="description" rows="3"
                                class="w-full dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ $initialDescription }}</textarea>
                            @error('description')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Itens da tarefa</h4>
                            <button type="button"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border hover:bg-[#ff8800] dark:hover:!bg-[#ff8800] border-gray-300 text-sm text-gray-700"
                                @click="addItem()">
                                <i class="fa-solid fa-plus"></i>
                                Adicionar item
                            </button>
                        </div>

                        <template x-if="!items.length">
                            <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                                Nenhum item adicionado. Clique em “Adicionar item” para criar a primeira subtarefa.
                            </div>
                        </template>

                        <template x-for="(item, index) in items" :key="item.uid">
                            <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-[#262626]">
                                <input type="hidden" :name="`items[${index}][id]`" :value="item.id ?? ''">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título do
                                            item</label>
                                        <input type="text" :name="`items[${index}][title]`" x-model="item.title"
                                            required
                                            class="w-full dark:!bg-[#171717] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                                        <select :name="`items[${index}][skill_competency_id]`"
                                            x-model="item.skill_competency_id" :disabled="!skill"
                                            class="w-full dark:!bg-[#171717] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-500">
                                            <option value="" x-text="primaryCompetencyOptionLabel()"></option>
                                            <template x-for="competencyOption in competencies"
                                                :key="competencyOption.id">
                                                <option :value="String(competencyOption.id)"
                                                    :selected="String(competencyOption.id) === String(item.skill_competency_id)"
                                                    x-text="competencyOption.name"></option>
                                            </template>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Deixe em branco para herdar a competência
                                            principal da tarefa.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                                        <select :name="`items[${index}][assigned_to]`" x-model="item.assigned_to"
                                            class="w-full dark:!bg-[#171717] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                            <option value="">Definir depois</option>
                                            @foreach ($teamMembers as $member)
                                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do
                                        item</label>
                                    <textarea rows="2" :name="`items[${index}][description]`" x-model="item.description"
                                        class="w-full  dark:!bg-[#171717] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                                </div>

                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="uppercase tracking-widest">Item #<span
                                            x-text="index + 1"></span></span>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-red-600 hover:text-red-700"
                                        @click="removeItem(index)">
                                        <i class="fa-solid fa-trash"></i>
                                        Remover
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                            class="inline-flex items-center gap-2 px-5 py-4 rounded-md border text-sm bg-red-500 text-white border-red-500 hover:bg-transparent hover:text-red-500 hover:border-red-500"
                            data-modal-hide="edit-task-modal-{{ $task->id }}">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="btn btn-mmcriativos inline-flex items-center gap-2 px-5 py-2.5 text-white rounded-md text-sm font-medium">
                            <i class="fa-duotone fa-solid fa-pen-circle fa-2x icon-project"></i>
                            Atualizar tarefa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
