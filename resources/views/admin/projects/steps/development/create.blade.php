@php
    $statusOptions = \App\Models\ProjectTask::STATUSES;
    $skillOptions =
        $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);
    $initialTaskItems = collect(old('items', []))
        ->map(function ($item, $index) {
            return [
                'uid' => $item['uid'] ?? ($item['id'] ?? 'tmp_' . $index . '_' . uniqid()),
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
    $taskItemsErrors = collect(optional($errors->getBag('projectTasksStore'))->getMessages() ?? [])
        ->filter(fn($messages, $key) => str_starts_with($key, 'items.'))
        ->flatten();
@endphp

<div x-cloak x-show="createTaskModal" x-transition.opacity
    class="fixed inset-0 z-40 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black bg-opacity-40" @click="createTaskModal = false"></div>

    <div class="relative bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
        @keydown.escape.window="createTaskModal = false">
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Nova tarefa de desenvolvimento</h3>
                <p class="text-sm text-gray-500">Cadastre tarefas com skill, compet&ecirc;ncia e respons&aacute;vel
                    definido.</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" @click="createTaskModal = false">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            @if ($skillOptions->isEmpty())
                <div class="border border-dashed border-orange-300 rounded-lg bg-orange-50 text-orange-700 text-sm p-4">
                    Cadastre skills e competencias no módulo Habilidades para liberar o cadastro de tarefas.
                </div>
            @else
                <form method="POST" action="{{ route('admin.projects.tasks.store', $project) }}"
                    data-ajax="create-task"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{
                        options: @js($skillOptions),
                        skill: @js(old('skill_id')),
                        competency: @js(old('skill_competency_id')),
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
                                uid: `new_${this.nextUid++}`,
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
                    }" x-init="ensureCompetency()">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                        <select name="skill_id" x-model="skill" @change="ensureCompetency()" required
                            class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="">Selecione...</option>
                            <template x-for="option in options" :key="option.id">
                                <option :value="String(option.id)" x-text="option.name"></option>
                            </template>
                        </select>
                        @error('skill_id', 'projectTasksStore')
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
                        @error('skill_competency_id', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        @error('title', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
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
                            class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data planejada</label>
                        <input type="date" name="planned_at" value="{{ old('planned_at') }}"
                            class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        @error('planned_at', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descri&ccedil;&atilde;o</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">{{ old('description') }}</textarea>
                        @error('description', 'projectTasksStore')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Itens da tarefa</p>
                                <p class="text-xs text-gray-500">Monte subtarefas espec&iacute;ficas para distribuir
                                    responsabilidades.</p>
                            </div>
                            <button type="button" @click="addItem()"
                                class="btn-mmcriativos inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fa-duotone fa-solid fa-list-check icon-project"></i>
                                Adicionar item
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
                                Nenhum item adicionado. Clique em &ldquo;Adicionar item&rdquo; para criar a primeira
                                subtarefa.
                            </div>
                        </template>

                        <template x-for="(item, index) in items" :key="item.uid">
                            <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-white dark:bg-black">
                                <input type="hidden" :name="`items[${index}][id]`" :value="item.id ?? ''">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título do
                                            item</label>
                                        <input type="text" :name="`items[${index}][title]`" x-model="item.title"
                                            required
                                            class="w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                                        <select :name="`items[${index}][skill_competency_id]`"
                                            x-model="item.skill_competency_id" :disabled="!skill"
                                            class="w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-500">
                                            <option value="" x-text="primaryCompetencyOptionLabel()"></option>
                                            <template x-for="competencyOption in competencies"
                                                :key="competencyOption.id">
                                                <option :value="String(competencyOption.id)"
                                                    x-text="competencyOption.name"></option>
                                            </template>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Deixe em branco para herdar a
                                            compet&ecirc;ncia principal da tarefa.</p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Respons&aacute;vel</label>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descri&ccedil;&atilde;o
                                        do item</label>
                                    <textarea rows="2" :name="`items[${index}][description]`" x-model="item.description"
                                        class="w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
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

                    <div class="md:col-span-2 flex items-center justify-center gap-3">
                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-md px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500"
                            @click="createTaskModal = false">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="btn-mmcriativos inline-flex items-center gap-2 px-5 py-2.5 rounded-md">
                            <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                            Adicionar tarefa
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
