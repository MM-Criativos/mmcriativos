@php
    $errorBag = 'projectTasksUpdate_' . $task->id;
    $statusOptions = \App\Models\ProjectTask::STATUSES;
    $skillOptionsCollection =
        $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);
    $bagErrors = $errors->getBag($errorBag);
    $bagHasErrors = $bagErrors->any();
    $initialSkillValue = $bagHasErrors ? old('skill_id') : (string) ($task->skill_id ?? '');
    $initialCompetencyValue = $bagHasErrors ? old('skill_competency_id') : (string) ($task->skill_competency_id ?? '');
    $initialAssignedValue = $bagHasErrors ? old('assigned_to') : $task->assigned_to;
    $initialStatusValue = $bagHasErrors ? old('status', $task->status) : $task->status;
    $initialPlannedValue = $bagHasErrors ? old('planned_at') : optional($task->planned_at)->format('Y-m-d');
    $initialTitleValue = $bagHasErrors ? old('title') : $task->title;
    $initialDescriptionValue = $bagHasErrors ? old('description') : $task->description;
    $initialItemsSource = $bagHasErrors
        ? old('items', [])
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
                'uid' => $item['uid'] ?? ($item['id'] ?? 'existing_' . $index . '_' . uniqid()),
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
    $taskItemsErrors = collect($bagErrors?->getMessages() ?? [])
        ->filter(fn($messages, $key) => str_starts_with($key, 'items.'))
        ->flatten();
@endphp

<div x-cloak x-show="modalOpen"
    class="fixed inset-0 z-30 flex items-center justify-center px-4 py-8 bg-black bg-opacity-50">
    <div @click.away="modalOpen = false" @keydown.escape.window="modalOpen = false"
        class="bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
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
                <form id="task-update-{{ $task->id }}" method="POST"
                    action="{{ route('admin.project-tasks.update', $task) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    data-ajax="update-task"
                x-data="{
                    options: @js($skillOptionsCollection->values()),
                    skill: '',
                    competency: '',
                    initialSkill: @js($initialSkillValue),
                    initialCompetency: @js($initialCompetencyValue),
                    items: @js($initialTaskItems),
                    itemsInitialized: false,
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
                        if (!this.itemsInitialized) {
                            this.itemsInitialized = true;
                            return;
                        }
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
                            uid: `task_${this.nextUid++}`,
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
                    },
                    initState() {
                        this.skill = this.initialSkill || '';
                        this.$nextTick(() => {
                            this.competency = this.initialCompetency || '';
                            this.ensureCompetency();
                        });
                    }
                }" x-init="initState()">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                    <select name="skill_id" x-model="skill" @change="ensureCompetency()" required
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Selecione...</option>
                        @foreach ($skillOptionsCollection as $option)
                            <option value="{{ $option['id'] }}" @selected((string) $initialSkillValue === (string) $option['id'])>{{ $option['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('skill_id', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                    <select name="skill_competency_id" x-model="competency" required
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Selecione...</option>
                        <template x-for="competencyOption in competencies" :key="competencyOption.id">
                            <option :value="String(competencyOption.id)" x-text="competencyOption.name"></option>
                        </template>
                    </select>
                    @error('skill_competency_id', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="title" value="{{ $initialTitleValue }}" required
                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                    @error('title', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($initialStatusValue === $value)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select name="assigned_to"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Definir depois</option>
                        @foreach ($teamMembers as $member)
                            <option value="{{ $member->id }}" @selected((string) $initialAssignedValue === (string) $member->id)>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data planejada</label>
                    <input type="date" name="planned_at" value="{{ $initialPlannedValue }}"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                    @error('planned_at', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ $initialDescriptionValue }}</textarea>
                    @error('description', $errorBag)
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Itens vinculados</p>
                            <p class="text-xs text-gray-500">Atualize, inclua ou remova subtarefas sem sair deste modal.
                            </p>
                        </div>
                        <button type="button" @click="addItem()"
                            class="btn-mmcriativos inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fa-duotone fa-solid fa-list-check icon-project"></i>
                            Adicionar Item
                        </button>
                    </div>

                    @if ($taskItemsErrors->isNotEmpty())
                        <div class="bg-red-50 border border-red-100 text-xs text-red-600 rounded-md p-3 space-y-1">
                            @foreach ($taskItemsErrors as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif

                    <template x-if="!items.length">
                        <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                            Esta tarefa ainda não tem itens cadastrados.
                        </div>
                    </template>

                    <template x-for="(item, index) in items" :key="item.uid">
                        <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-white dark:bg-black">
                            <input type="hidden" :name="`items[${index}][id]`" :value="item.id ?? ''">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Título do item</label>
                                    <input type="text" :name="`items[${index}][title]`" x-model="item.title" required
                                        class="w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                                    <select :name="`items[${index}][skill_competency_id]`"
                                        x-model="item.skill_competency_id" :disabled="!skill"
                                        class="w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-500">
                                        <option value="" x-text="primaryCompetencyOptionLabel()"
                                            :selected="String(item.skill_competency_id) === ''"></option>
                                        <template x-for="competencyOption in competencies" :key="competencyOption.id">
                                            <option :value="String(competencyOption.id)" x-text="competencyOption.name"
                                                :selected="String(item.skill_competency_id) === String(competencyOption.id)">
                                            </option>
                                        </template>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Deixe em branco para herdar a competência
                                        principal da tarefa.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                                    <select :name="`items[${index}][assigned_to]`" x-model="item.assigned_to"
                                        class="w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Definir depois</option>
                                        @foreach ($teamMembers as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do item</label>
                                <textarea rows="2" :name="`items[${index}][description]`" x-model="item.description"
                                    class="w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="uppercase tracking-widest">Item #<span x-text="index + 1"></span></span>
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

            </form>

            <div
                class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between border-t border-gray-100 pt-4">
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
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">
                            <i class="fa-solid fa-trash"></i>
                            Excluir
                        </button>
                    </form>

                    <button type="submit" form="task-update-{{ $task->id }}"
                        class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm">
                        <i class="fa-duotone fa-solid fa-arrow-down-to-arc icon-project mr-1"></i>
                        Salvar alterações
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
