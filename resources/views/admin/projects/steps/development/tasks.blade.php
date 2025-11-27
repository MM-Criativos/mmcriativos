<style>
    .badge-inprogress {
        background-color: #ff8800;
        color: #fff;
    }

    .badge-completed {
        background-color: #008800;
        color: #fff;
    }

    .badge-pendent {
        background-color: #ff0000;
        color: #fff;
    }
</style>

@php
    $teamMembers = $teamMembers instanceof \Illuminate\Support\Collection ? $teamMembers : collect($teamMembers ?? []);
    $skillOptions =
        $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);

    $statusBadges = \App\Models\ProjectTask::STATUS_BADGES;

    $statusTabs = [
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'Em progresso',
        \App\Models\ProjectTask::STATUS_PENDING => 'Não iniciado',
        \App\Models\ProjectTask::STATUS_DONE => 'Completo',
    ];

    $taskGroups = $project->tasks->groupBy(fn($task) => $task->skill_id ?? 'sem-skill');
    $canCreateTasks = $skillOptions->isNotEmpty();
@endphp


<section class="space-y-8" x-data="{ createTaskModal: {{ $errors->hasBag('projectTasksStore') ? 'true' : 'false' }} }">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Tarefas de desenvolvimento</h3>
            <p class="text-sm text-gray-500">Cadastre e organize as entregas por áreas e acompanhe o status.</p>
            @unless ($canCreateTasks)
                <p class="text-xs text-orange-600 mt-1">
                    Cadastre skills e competências no módulo Habilidades para liberar o cadastro de tarefas.
                </p>
            @endunless
        </div>
        <div class="flex items-center gap-3">

            <span class="text-sm text-gray-500">
                {{ $project->tasks->count() }} {{ \Illuminate\Support\Str::plural('tarefa', $project->tasks->count()) }}
            </span>
            <button type="button" @class([
                'btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-md border',
                'opacity-60 cursor-not-allowed' => !$canCreateTasks,
            ]) @click="createTaskModal = true"
                @if (!$canCreateTasks) disabled title="Cadastre skills e competências primeiro." @endif>
                <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                Criar tarefa
            </button>
            <a href="{{ route('admin.project-tasks.completed', ['project_id' => $project->id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border btn-mmcriativos">
                <i class="fa-duotone fa-solid fa-list-check icon-project"></i>
                Tarefas concluídas
            </a>
        </div>
    </div>

    @include('admin.projects.steps.development.create', [
        'project' => $project,
        'skillOptions' => $skillOptions,
        'teamMembers' => $teamMembers,
    ])

    @include('admin.projects.steps.development.partials.tasks-list', [
        'project' => $project,
        'teamMembers' => $teamMembers,
        'skillOptions' => $skillOptions,
        'taskGroups' => $taskGroups,
        'statusTabs' => $statusTabs,
        'statusBadges' => $statusBadges,
    ])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                return;
            }

            const taskBadgeBaseClass =
                'inline-flex items-center justify-center min-w-[110px] px-4 py-1.5 rounded-full text-xs font-medium text-center whitespace-nowrap';
            const itemBadgeBaseClass = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
            const ajaxInitialized = new WeakSet();
            const taskListUrl = '{{ route('admin.projects.steps.development.tasks.list', $project) }}';
            const tasksListSelector = '[data-tasks-list]';

            initAjaxForms();

            async function submitAjaxForm(form) {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.setAttribute('aria-busy', 'true');
                }

                try {
                    const response = await fetch(form.action, {
                        method: (form.method || 'POST').toUpperCase(),
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'Erro ao atualizar o status');
                    }

                    switch (form.dataset.ajax) {
                        case 'complete-task':
                            handleTaskCompletion(form, payload);
                            break;
                        case 'toggle-item':
                            handleItemToggle(form, payload);
                            break;
                        case 'create-task':
                            await handleTaskCreation(form, payload);
                            break;
                        case 'update-task':
                            await handleTaskUpdate(form, payload);
                            break;
                    }
                } catch (error) {
                    console.error(error);
                    alert(error.message || 'Erro ao atualizar o status da tarefa.');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.removeAttribute('aria-busy');
                    }
                }
            }

            function initAjaxForms(root = document) {
                root.querySelectorAll('form[data-ajax]').forEach((form) => {
                    if (ajaxInitialized.has(form)) {
                        return;
                    }
                    ajaxInitialized.add(form);
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        await submitAjaxForm(form);
                    });
                });
            }

            function handleTaskCompletion(form, payload) {
                const taskCard = form.closest('[data-project-task-card]');
                if (!taskCard) {
                    return;
                }

                const badge = taskCard.querySelector('[data-task-status-badge]');
                if (badge) {
                    badge.className = `${taskBadgeBaseClass} ${payload.badgeClasses ?? ''}`.trim();
                    badge.textContent = payload.badgeText ?? badge.textContent;
                }

                const completionSection = taskCard.querySelector('[data-task-completion-section]');
                if (completionSection) {
                    const summary = document.createElement('span');
                    summary.className =
                        'inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-green-700 text-white border-green-200';
                    const completedAt = payload.completedAt || 'data não registrada';
                    summary.innerHTML = `<i class="fa-solid fa-flag-checkered"></i> Concluída em ${completedAt}`;
                    completionSection.innerHTML = '';
                    completionSection.appendChild(summary);
                }

                (payload.items ?? []).forEach((itemPayload) => {
                    const itemForm = taskCard.querySelector(
                        `form[data-ajax="toggle-item"][data-item-id="${itemPayload.id}"]`);
                    if (itemForm) {
                        handleItemToggle(itemForm, itemPayload);
                    }
                });
            }

            function handleItemToggle(form, payload) {
                const itemCard = form.closest('[data-item-card]');
                const badge = itemCard?.querySelector('[data-item-badge]');
                const doneText = itemCard?.querySelector('[data-item-done-text]');
                const toggleButton = form.querySelector('[data-item-toggle-btn]');

                if (badge) {
                    badge.className = `${itemBadgeBaseClass} ${payload.badgeClasses ?? ''}`.trim();
                    badge.textContent = payload.badgeLabel ?? badge.textContent;
                }

                if (doneText) {
                    if (payload.showDoneAt) {
                        doneText.textContent = payload.doneAtText ?? '';
                        doneText.style.display = 'block';
                    } else {
                        doneText.style.display = 'none';
                    }
                }

                if (toggleButton) {
                    toggleButton.className = payload.buttonClasses ?? toggleButton.className;
                    toggleButton.innerHTML =
                        `<i class="fa-solid ${payload.buttonIcon ?? 'fa-check'}"></i> ${payload.buttonText ?? 'Finalizar'}`;
                }
            }

            async function handleTaskCreation(form, payload) {
                const container = document.querySelector(
                    `[data-task-status-container="${payload.skillKey}::${payload.statusKey}"]`);
                if (container) {
                    const emptyState = container.querySelector('[data-empty-state]');
                    if (emptyState) {
                        emptyState.remove();
                    }
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = payload.html;
                    const card = wrapper.firstElementChild;
                    if (card) {
                        container.appendChild(card);
                        window.Alpine?.initTree?.(card);
                        initAjaxForms(card);
                    }
                    updateStatusCount(payload.skillKey, payload.statusKey, 1);
                } else {
                    await refreshTaskList();
                }
                resetCreateForm(form);
                closeCreateModal();
            }

            async function handleTaskUpdate(form, payload) {
                await refreshTaskList(payload.html ?? null);
                const card = form.closest('[data-project-task-card]');
                if (card?.__x) {
                    card.__x.$data.modalOpen = false;
                }
            }

            function updateStatusCount(skillKey, statusKey, delta = 1) {
                const counter = document.querySelector(
                    `[data-task-status-count="${skillKey}::${statusKey}"]`);
                if (!counter) {
                    return;
                }
                const current = parseInt(counter.textContent, 10) || 0;
                counter.textContent = current + delta;
            }

            function resetCreateForm(form) {
                form.reset();
                const alpineComponent = form.__x?.$data;
                if (alpineComponent) {
                    alpineComponent.skill = '';
                    alpineComponent.competency = '';
                    alpineComponent.items = [];
                    alpineComponent.nextUid = Date.now();
                    alpineComponent.ensureCompetency?.();
                }
            }

            function closeCreateModal() {
                const sectionRoot = document.querySelector('section[x-data]');
                if (sectionRoot?.__x) {
                    sectionRoot.__x.$data.createTaskModal = false;
                }
            }

            function replaceTaskList(html) {
                const list = document.querySelector(tasksListSelector);
                if (!list) {
                    return;
                }
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                const newList = wrapper.firstElementChild;
                if (!newList) {
                    return;
                }
                list.replaceWith(newList);
                window.Alpine?.initTree?.(newList);
                initAjaxForms(newList);
            }

            async function refreshTaskList(html = null) {
                if (html) {
                    replaceTaskList(html);
                    return;
                }
                try {
                    const response = await fetch(taskListUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });
                    if (!response.ok) {
                        throw new Error('Não foi possível recarregar a lista de tarefas.');
                    }
                    const payload = await response.json();
                    replaceTaskList(payload.html);
                } catch (error) {
                    console.error(error);
                }
            }
        });
    </script>
