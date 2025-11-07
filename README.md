# mmcriativos

Suite interna da agencia MM Criativos para gerir pipeline comercial, execucao de projetos digitais e formularios publicos conectados aos clientes. O repositorio contem o painel administrativo completo, o site institucional alimentado por dados e as rotinas que fecham o ciclo de briefing ate entrega.

## Principais recursos
- Painel administrativo para cadastrar clientes, projetos, skills e tarefas de desenvolvimento com agrupamento por competencia (`resources/views/admin/projects/steps/develepoment/tasks.blade.php`).
- Fluxo comercial com planos, orcamentos, eventos e pagamentos automatizados (`app/Models/Budget.php`).
- Site publico responsavel por paginas institucionais, formularios de contato e coleta de briefing (`routes/web.php` e `app/Http/Controllers/Site`).
- Modulos qualitativos que registram percepcao do cliente por meio das regras de briefing (`app/Http/Controllers/Site/PublicBriefingController.php`).
- Scripts padronizados para subir servidor, fila, logs com Pail e Vite (`composer.json`).

## Tecnologias
- PHP 8.2 + Laravel 12 com Eloquent, Jobs em fila (driver database) e Blade.
- Front-end com Vite 7, Tailwind CSS, Alpine.js e Axios.
- Banco relacional (MySQL ou SQLite para desenvolvimento) com migracoes versionadas, inclusive `project_tasks`.
- Ferramentas auxiliares: Laravel Pail para logs em tempo real, queue:listen, ffmpeg opcional para midia (`.env.example`).

## Setup rapido
1. Copie o arquivo de exemplo: `cp .env.example .env` (ou use `copy` no Windows) e configure banco, filas e e-mail.
2. Rode o script de bootstrap completo:
   ```bash
   composer run setup
   ```
   O script instala dependencias PHP/Node, gera a chave e executa migracoes e build inicial.
3. Para ambiente interativo, execute `composer run dev` e acompanhe servidor HTTP, listener da fila, Pail e Vite em paralelo.
4. Rode a suite de testes quando aplicar alteracoes: `composer run test`.

Caso prefira passos manuais, instale dependencias com `composer install` e `npm install`, gere a chave (`php artisan key:generate`), execute `php artisan migrate --force` e finalize com `npm run build` ou `npm run dev`.

## Scripts uteis
| Comando | Descricao |
| --- | --- |
| `composer run setup` | Provisiona dependencias, copia `.env`, gera chave e roda migracoes + build. |
| `composer run dev` | Ativa `php artisan serve`, `queue:listen`, `pail` e `npm run dev` via `concurrently`. |
| `composer run test` | Limpa configuracoes em cache e executa `php artisan test`. |
| `npm run dev` | Build incremental com Vite e Tailwind. |
| `npm run build` | Build de producao minificado. |

## Estrutura de pastas
- `app/` - Codigo de dominio (Models, Controllers, Jobs). Veja `app/Models/ProjectTask.php` e `app/Models/Budget.php`.
- `resources/views/` - Interfaces Blade do painel, site publico e formularios (ex.: `resources/views/admin/projects/steps/develepoment`).
- `database/migrations/` - Definicoes de schema, incluindo `2025_11_06_204906_create_project_tasks_table.php`.
- `routes/` - Entradas HTTP (admin, site, APIs publicas) detalhadas em `routes/web.php`.
- `docs/` - Nova documentacao modular (onboarding, arquitetura, dominio e operacoes).

## Documentacao
A documentacao viva mora em `docs/`. Comece pelo indice em `docs/README.md` e navegue pelos guias de onboarding (`docs/getting-started`), arquitetura (`docs/architecture`), dominio (`docs/domain`) e operacoes (`docs/operations`). Consulte tambem o glossario para alinhar termos de negocio.

## Contribuicao
1. Abra uma branch com escopo claro.
2. Atualize/adicione testes relevantes (`composer run test`).
3. Registre mudancas relevantes na documentacao em `docs/`.
4. Abra o PR descrevendo impacto, migracoes e passos de validacao.

## Licenca
Projeto privado do time MM Criativos. Nao distribuir sem autorizacao.
