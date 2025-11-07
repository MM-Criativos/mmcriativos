# Projetos e tarefas

## Estrutura de dados
- Tabela `project_tasks` (`database/migrations/2025_11_06_204906_create_project_tasks_table.php:11-29`):
  - FK obrigatoria para `projects`.
  - FK opcionais para `skills` e `skill_competencies` (com `onDelete('set null')`).
  - Campos de negocio: `title`, `description`, `status` (`pending`, `in_progress`, `done`), `assigned_to`, `progress_notes` e `completed_at`.
- Cada tarefa pertence a um `project` e pode ter um `assigned_to` apontando para `users`.

## Modelo
- `app/Models/ProjectTask.php:12-98`:
  - Expoe constantes `STATUS_*` e o array `STATUSES` para labels.
  - Relacionamentos: `project()`, `skill()`, `competency()` (para `skill_competency_id`) e `assignedUser()`.
  - Helpers `isCompleted`, `markAsCompleted`, `markInProgress` e `markPending` encapsulam mudancas de status/`completed_at`.

## UI e fluxo
- Listagem principal: `resources/views/admin/projects/steps/develepoment/tasks.blade.php:1-185`.
  - Agrupa tarefas por skill (`$project->tasks->groupBy(...)`), exibe tabs por status e badges configuradas em `$statusBadges`.
  - Conta com modal de criacao (`include('create')`) e modais inline de edicao (`include('edit')`).
- Criacao: `resources/views/admin/projects/steps/develepoment/create.blade.php:1-149`.
  - Form exige skill, competencia e responsavel opcional, alem de descricao/notas.
  - Usa Alpine para filtrar competencias conforme a skill selecionada.
  - Bloqueia cadastro quando nao existem skills cadastradas (`$skillOptions->isEmpty()`).
- Edicao: `resources/views/admin/projects/steps/develepoment/edit.blade.php:1-153`.
  - Cada card abre modal proprio com validacao isolada via `$errorBag = 'projectTasksUpdate_'.$task->id`.
  - Permite alterar skill/competencia, status, responsavel e textos, alem de excluir tarefa.

## Regras de negocio
- Skills e competencias precisam estar cadastradas antes de criar tarefas (ver `resources/views/admin/projects/steps/develepoment/create.blade.php:23-33`).
- `assigned_to` e opcional; quando nulo os cards exibem "Nao atribuido".
- Atualizacoes de status devem usar os helpers do modelo quando disparadas em jobs/servicos para garantir consistencia de `completed_at`.
- Ao remover um projeto as tarefas sao excluidas em cascata (`Schema::create(...)->foreignId('project_id')->constrained()->onDelete('cascade')`).
