<style>
    .skill-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        font-size: 11px;
        font-weight: 600;

        padding: 4px 10px;
        border-radius: 6px;

        background-color: #ffe9d0;
        /* bem suave no tema claro */
        color: #ff8800;
        border: 1px solid #ff8800;

        user-select: none;
        pointer-events: none;
        /* garante que não é clicável */
    }

    /* Ícone duotone dentro da badge */
    .skill-badge .fa-duotone {
        font-size: 12px;
    }

    /* Competência mais suave visualmente */
    .skill-badge .skill-comp {
        color: #d46a00;
    }

    /* DARK MODE */
    .dark .skill-badge {
        background-color: rgba(255, 136, 0, 0.15);
        color: #ff8800;
        border-color: #ff8800;
    }

    .dark .skill-badge .skill-comp {
        color: #ffbb66;
    }

    .kanban-delete-overlay {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.65);
        z-index: 999;
    }

    .kanban-delete-overlay[data-visible="true"] {
        display: flex;
    }

    .kanban-delete-panel {
        width: 100%;
        max-width: 456px;
        border-radius: 20px;
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px rgba(15, 23, 42, 0.65);
    }
</style>

@extends('layouts.app')

@php
    use App\Models\ProjectTask;

    $title = 'Kanban';
    $subTitle = 'Gerencie suas tarefas rapidamente';
    $navbarTabs = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-duotone fa-list-check',
            'route' => route('admin.tasks.index'),
            'active' => request()->routeIs('admin.tasks.index'),
        ],
        [
            'label' => 'Calendário',
            'icon' => 'fa-duotone fa-calendar-days',
            'route' => route('admin.tasks.calendar'),
            'active' => request()->routeIs('admin.tasks.calendar'),
        ],
        [
            'label' => 'Kanban',
            'icon' => 'fa-duotone fa-chart-kanban',
            'route' => route('admin.tasks.kanban'),
            'active' => request()->routeIs('admin.tasks.kanban'),
        ],
        [
            'label' => 'Finalizadas',
            'icon' => 'fa-duotone fa-circle-check',
            'route' => route('admin.tasks.completed'),
            'active' => request()->routeIs('admin.tasks.completed'),
        ],
    ];
    $kanbanStatusClasses = [
        ProjectTask::STATUS_PENDING => 'pending-card',
        ProjectTask::STATUS_IN_PROGRESS => 'progress-card',
        ProjectTask::STATUS_DONE => 'done-card',
    ];
@endphp

@section('content')
    <div x-data="{ createTaskModal: false }" @keydown.escape.window="createTaskModal = false">
        <div class="grid grid-cols-12">
            <div class="col-span-12">
                <div class="overflow-x-auto scroll-sm pb-8">

                    <div class="kanban-wrapper min-w-[1560px]">
                        <div class="flex items-start gap-6" id="sortable-wrapper">
                            @foreach ($kanbanStatuses ?? [] as $status => $label)
                                <div class="w-[33.333%] kanban-item rounded-xl {{ $kanbanStatusClasses[$status] ?? '' }}">
                                    <div class="card p-0 rounded-xl overflow-hidden shadow-none border-0">
                                        <div class="card-body p-0 pb-6">
                                            <div class="flex flex-col gap-3 items-center ps-6 pt-6 pe-6">
                                                <h6 class="text-lg font-semibold mb-0 text-center w-full">
                                                    {{ $label }}</h6>
                                            </div>
                                            <div class="connectedSortable ps-6 pt-6 pe-6" data-status="{{ $status }}">
                                                @php
                                                    $columnTasks = $status === ProjectTask::STATUS_DONE
                                                        ? ($recentlyCompleted ?? collect())
                                                        : ($kanbanTasks->get($status) ?? collect());
                                                    $columnTasks = $columnTasks->sortByDesc(
                                                        $status === ProjectTask::STATUS_DONE ? 'completed_at' : 'updated_at'
                                                    );
                                                @endphp
                                                @forelse ($columnTasks as $task)
                                                    <div class="kanban-card bg-neutral-50 dark:bg-dark-3 p-4 rounded-lg mb-6"
                                                        id="kanban-{{ $task->id }}" data-task-id="{{ $task->id }}"
                                                        data-kanban-status="{{ $status }}">
                                                        <h6 class="kanban-title text-lg font-semibold mb-2">
                                                            {{ $task->title }}
                                                        </h6>
                                                        <p class="kanban-desc text-secondary-light">
                                                            {{ $task->description ?? '' }}
                                                        </p>
                                                        @if ($task->skill || $task->competency)
                                                            <span class="skill-badge mt-2">
                                                                <i class="fa-duotone fa-tags icon-project"></i>
                                                                @if ($task->skill)
                                                                    <span class="skill-name">
                                                                        {{ $task->skill->name }}
                                                                    </span>
                                                                @endif
                                                                @if ($task->competency)
                                                                    <span class="skill-comp">
                                                                        {{ $task->competency->competency }}
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        @endif
                                                        @foreach ($task->items as $item)
                                                            <details class="kanban-item-detail">
                                                                <summary
                                                                    class="kanban-item-detail__summary !text-[#000] dark:text-[#fff] mt-2">
                                                                    <span>{{ $item->title }}</span>
                                                                    <iconify-icon icon="lucide:chevron-down"
                                                                        class="kanban-item-detail__icon text-black dark:text-white">
                                                                    </iconify-icon>

                                                                </summary>
                                                                <div class="kanban-item-detail__body">
                                                                    @if ($item->description)
                                                                        <p
                                                                            class="kanban-desc text-secondary-light mt-2 mb-2">
                                                                            {{ $item->description }}
                                                                        </p>
                                                                    @endif
                                                                    @if ($item->competency)
                                                                        <span class="skill-badge mt-2">
                                                                            <i class="fa-duotone fa-tags icon-project"></i>
                                                                            <span class="skill-comp">
                                                                                {{ $item->competency->competency }}
                                                                            </span>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </details>
                                                        @endforeach
                                                        <div class="mt-3 flex items-center justify-between gap-2.5">
                                                            <div class="flex items-center justify-between gap-2.5">
                                                                <i class="fa-duotone fa-solid fa-calendar icon-project"></i>
                                                                <span class="start-date text-secondary-light">
                                                                    {{ optional($task->planned_at)->format('d M Y') ?? '--' }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center justify-between gap-2.5">
                                                                <button type="button"
                                                                    class="card-edit-button text-success-600"
                                                                    data-modal-target="edit-task-modal-{{ $task->id }}"
                                                                    data-modal-toggle="edit-task-modal-{{ $task->id }}">
                                                                    <i
                                                                        class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                                </button>
                                                                <button type="button"
                                                                    class="card-delete-button text-danger-600"
                                                                    data-delete-url="{{ route('admin.project-tasks.destroy', $task) }}">
                                                                    <i
                                                                        class="fa-duotone fa-solid fa-trash icon-project"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @include('admin.tasks.edit', [
                                                        'task' => $task,
                                                        'projects' => $projects,
                                                        'skills' => $skills,
                                                        'teamMembers' => $teamMembers,
                                                        'statusOptions' => $statusOptions,
                                                    ])
                                                @empty
                                                @endforelse
                                            </div>
                                            <button type="button"
                                                class="flex items-center gap-2 font-medium w-full text-primary-600 justify-center text-hover-primary-800 line-height-1"
                                                @click="createTaskModal = true">
                                                <iconify-icon icon="ph:plus-circle" class="icon text-xl"></iconify-icon>
                                                Adicionar Tarefa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.tasks.create', [
            'projects' => $projects,
            'skills' => $skills,
            'teamMembers' => $teamMembers,
            'statusOptions' => $statusOptions,
        ])

        <!-- Delete confirmation modal -->
        <div id="kanban-delete-modal" class="kanban-delete-overlay" data-visible="false">
            <div class="kanban-delete-panel">
                <form id="kanbanDeleteForm" method="POST" class="p-6 text-center space-y-4">
                    @csrf
                    @method('DELETE')
                    <span class="text-4xl text-danger-600">
                        <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                    </span>
                    <h6 class="text-lg font-semibold text-white">
                        Tem certeza que deseja excluir?
                    </h6>
                    <p class="text-sm text-gray-300">
                        Esta ação é irreversível.
                    </p>
                    <div class="flex justify-center gap-3">
                        <button type="button" data-dismiss-delete-modal
                            class="px-4 py-2 border border-gray-600 rounded-lg text-sm text-white/80">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let draggedCard = null;
        let dropPlaceholder = null;
        let dropTargetColumn = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const kanbanStatusUpdateBaseUrl = "{{ url('admin/tasks/kanban') }}";

        function ensurePlaceholder() {
            if (!dropPlaceholder) {
                dropPlaceholder = document.createElement("div");
                dropPlaceholder.className = "kanban-drop-placeholder";
            }
            return dropPlaceholder;
        }

        function removePlaceholder() {
            if (dropPlaceholder && dropPlaceholder.parentNode) {
                dropPlaceholder.parentNode.removeChild(dropPlaceholder);
            }
            dropTargetColumn = null;
        }

        function maybeUpdateTaskStatus(card, column) {
            if (!card || !column) {
                return;
            }
            const newStatus = column.dataset.status;
            const taskId = card.dataset.taskId;
            if (!newStatus || !taskId) {
                return;
            }
            if (card.dataset.kanbanStatus === newStatus) {
                return;
            }
            card.dataset.kanbanStatus = newStatus;
            sendStatusUpdateRequest(taskId, newStatus);
        }

        async function sendStatusUpdateRequest(taskId, newStatus) {
            if (!csrfToken || !taskId || !newStatus) {
                return;
            }
            try {
                await fetch(`${kanbanStatusUpdateBaseUrl}/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: newStatus
                    }),
                });
            } catch (error) {
                console.error('Failed to update kanban task status', error);
            }
        }

        function attachDragHandlers(card) {
            if (card._kanbanDragBound) {
                return;
            }

            card._kanbanDragBound = true;
            card.setAttribute("draggable", "true");

            card.addEventListener("dragstart", function(event) {
                draggedCard = card;
                card.classList.add("kanban-card--dragging");
                event.dataTransfer.effectAllowed = "move";
                event.dataTransfer.dropEffect = "move";
                const placeholder = ensurePlaceholder();
                placeholder.style.height = `${card.offsetHeight}px`;
                try {
                    event.dataTransfer.setData("text/plain", card.id || "");
                    event.dataTransfer.setDragImage(card, card.offsetWidth / 2, card.offsetHeight / 2);
                } catch (error) {
                    // Some browsers require setData even when not used.
                }
            });

            card.addEventListener("dragend", function() {
                card.classList.remove("kanban-card--dragging");
                draggedCard = null;
                removePlaceholder();
            });
        }

        function getDragAfterElement(column, y) {
            const draggableElements = [...column.querySelectorAll(".kanban-card:not(.kanban-card--dragging)")];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return {
                        offset,
                        element: child
                    };
                }
                return closest;
            }, {
                offset: Number.NEGATIVE_INFINITY
            }).element;
        }

        function bindColumn(column) {
            if (column._kanbanColumnBound) {
                return;
            }

            column._kanbanColumnBound = true;

            column.addEventListener("dragover", function(event) {
                event.preventDefault();
                if (!draggedCard) {
                    return;
                }
                event.dataTransfer.dropEffect = "move";
                const afterElement = getDragAfterElement(column, event.clientY);
                const placeholder = ensurePlaceholder();
                placeholder.style.height = `${draggedCard.offsetHeight}px`;
                if (placeholder.parentNode && placeholder.parentNode !== column) {
                    placeholder.parentNode.removeChild(placeholder);
                }
                if (afterElement == null) {
                    column.appendChild(placeholder);
                } else {
                    column.insertBefore(placeholder, afterElement);
                }
                dropTargetColumn = column;
            });

            column.addEventListener("drop", function(event) {
                event.preventDefault();
                if (draggedCard && dropPlaceholder && dropTargetColumn === column) {
                    column.insertBefore(draggedCard, dropPlaceholder);
                    maybeUpdateTaskStatus(draggedCard, column);
                }
                removePlaceholder();
            });
        }

        function refreshKanbanDragHandlers(root = document) {
            root.querySelectorAll(".connectedSortable").forEach(bindColumn);
            root.querySelectorAll(".kanban-card").forEach(attachDragHandlers);
        }

        refreshKanbanDragHandlers();
        window.refreshKanbanDragHandlers = refreshKanbanDragHandlers;
    });
</script>

<!--=========================== Delete & Duplicate js code start ==============================-->
<script>
    // Duplicate Item js
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".duplicate-button").forEach(button => {
            button.addEventListener("click", function() {
                // Find the closest card to the clicked button
                const card = this.closest(".kanban-item");
                // Clone the card
                const clone = card.cloneNode(true);
                // Append the cloned card to the parent container
                card.parentNode.appendChild(clone);

                if (window.refreshKanbanDragHandlers) {
                    window.refreshKanbanDragHandlers(clone);
                }

                // Add event listener to delete button of the cloned card
                clone.querySelector(".delete-button").addEventListener("click", function() {
                    clone.remove();
                });
            });
        });

        $(document).on("click", ".delete-button", function() {
            $(this).closest(".kanban-item").addClass("hidden");
        });
    });
</script>
<!--=========================== Delete & Duplicate js code End ==============================-->

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const overlay = document.getElementById("kanban-delete-modal");
        const deleteForm = document.getElementById("kanbanDeleteForm");
        if (!overlay || !deleteForm) {
            return;
        }

        const closeOverlay = () => {
            deleteForm.removeAttribute("action");
            overlay.dataset.visible = "false";
        };

        document.body.addEventListener("click", function(event) {
            const deleteButton = event.target.closest(".card-delete-button");
            if (!deleteButton) {
                return;
            }
            const url = deleteButton.dataset.deleteUrl;
            if (!url) {
                return;
            }
            deleteForm.setAttribute("action", url);
            overlay.dataset.visible = "true";
        });

        overlay.addEventListener("click", function(event) {
            if (event.target === overlay || event.target.closest("[data-dismiss-delete-modal]")) {
                event.preventDefault();
                closeOverlay();
            }
        });
    });
</script>
