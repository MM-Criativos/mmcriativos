# Arquitetura geral

## Camada HTTP
- Todas as rotas web vivem em `routes/web.php`, separadas por namespaces para Admin, Site e Comercial. O arquivo registra mais de 30 controllers, incluindo `Admin\ProjectTaskController`, `Admin\Commercial\KpiController` e `Site\ModalController`.
- Middlewares do Breeze protegem `/dashboard` e rotas administrativas (`routes/web.php:116-210`).
- Formularios publicos (briefing, contato, modal) ficam sob `App\Http\Controllers\Site`.

## Backend
- Laravel 12 orquestra controllers, policies e jobs. Models importantes:
  - `app/Models/ProjectTask.php:12-98` define constantes de status, relacionamentos (projeto, skill, competencia, usuario) e helpers (`markAsCompleted`, etc.).
  - `app/Models/Budget.php:12-200` concentra relacionamentos com planos, itens e pagamentos e o metodo `calculateTotals` para distribuir descontos/impostos.
  - `app/Models/Skill.php:12-52` agrupa competencias e disponibiliza `icon_class`.
- As filas usam o driver `database` por padrao e sao consumidas via `php artisan queue:listen --tries=1` (vide script `composer run dev`).

## Banco de dados
- Migracoes residem em `database/migrations/`. Exemplo: `2025_11_06_204906_create_project_tasks_table.php:11-29` cria a tabela `project_tasks` com FKs para `projects`, `skills`, `skill_competencies` e `users`, alem de campos de notas e `completed_at`.
- Sessions, cache e filas tambem utilizam tabelas conforme configurado em `.env.example:30-44`.
- Seeds especificos podem ser adicionados em `database/seeders/` (nao incluso aqui).

## Frontend
- Todas as views sao Blade. Interfaces ricas (como tarefas) combinam Blade + Alpine.js para tabs, modais e validacoes (`resources/views/admin/projects/steps/develepoment/tasks.blade.php:1-185`).
- O build roda via Vite 7, com Tailwind CSS, @tailwindcss/forms e Alpine registrados em `package.json:5-19`.
- Assets compilados sao servidos via `npm run dev` em desenvolvimento e `npm run build` em producao.

## Observabilidade
- `laravel/pail` fornece tail dos logs em tempo real (`composer.json:15,45-47`).
- O script `composer run dev` tambem garante que filas, servidor HTTP e logs fiquem sincronizados.
- Ajuste `.env` para apontar `LOG_CHANNEL=stack` e para configurar destino de emails (`.env.example:18-57`).
