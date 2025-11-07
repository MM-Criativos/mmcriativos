# Glossario

| Termo | Definicao | Referencias |
| --- | --- | --- |
| Projeto | Registro principal que conecta cliente, plano, orcamento e etapas de execucao. Controla tarefas, briefing e paginas publicas. | `app/Models/Project.php`, `routes/web.php` |
| Skill | Area de especialidade cadastrada para categorizar entregas e conectar competencias especificas. | `app/Models/Skill.php:12-52` |
| Competencia | Subcategoria de uma skill usada para granular tarefas e alocar responsaveis com proficiencia adequada. | `app/Models/Skill.php:21-37` |
| Tarefa de projeto | Item operacional associado a um projeto + skill + competencia, com status e responsavel rastreados. | `database/migrations/2025_11_06_204906_create_project_tasks_table.php:11-29`, `app/Models/ProjectTask.php:12-98` |
| Briefing Regua | Perguntas/criterios qualitativos respondidos pelo cliente para medir percepcao ou andamento. | `app/Http/Controllers/Site/PublicBriefingController.php:12-65`, `App\Models\PlanningBriefingRegua` |
| Briefing Response | Persistencia das respostas de briefing (valor numerico + comentario) ligadas ao cliente e ao projeto. | `app/Http/Controllers/Site/PublicBriefingController.php:33-52`, `App\Models\PlanningBriefingResponse` |
| Plano | Pacote de servicos e precos que orienta orcamentos e cronogramas comerciais. | `app/Models/Plan.php` |
| Orcamento (Budget) | Proposta financeira composta por itens, eventos e pagamentos, com helpers para calcular totais e descontos. | `app/Models/Budget.php:12-200` |
| KPI Comercial | Indicadores acompanhados no modulo `Admin\Commercial\KpiController` para mensurar pipeline. | `routes/web.php:32-36` |
| Pagina publica | Paginas institucionais ou dinamicas renderizadas pelo namespace `App\Http\Controllers\Site`, alimentadas por dados do painel. | `routes/web.php:57-110`, `resources/views/pages/` |
