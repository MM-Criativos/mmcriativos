# Modulo comercial (planos e orcamentos)

## Objetivo
Gerenciar propostas comerciais ligadas aos servicos e planos ativos, incluindo calculo de totais, historico de eventos e formas de pagamento. Controllers do namespace `App\Http\Controllers\Admin\Commercial` expostos em `routes/web.php:32-44` cuidam do CRUD e dashboards.

## Estrutura principal
- `app/Models/Budget.php:12-200`
  - Campos preenchiveis: `service_id`, `plan_id`, `client_id`, snapshots de preco base, descontos, impostos e totais (one_time / monthly / yearly).
  - Relacionamentos:
    - `plan()`, `client()`, `service()` - contexto comercial.
    - `items()` - linhas de orcamento ordenadas por `sort`.
    - `events()` - timeline de interacoes (envios, abertura, aceite).
    - `payments()` e `selectedPayment()` - opcoes de pagamento, incluindo `is_selected`.
    - `project()` - liga o orcamento aprovado ao projeto executado.
  - Helpers:
    - `calculateTotals()` calcula subtotais por periodicidade, distribui descontos de item + global e aplica impostos.
    - `applyDiscounts()` atualiza desconto/juros e reexecuta `calculateTotals`.
    - `statusBadge()` devolve badge HTML com label traduzida.
    - `getGrandTotalAttribute()` agrega totais com base no snapshot.

## Fluxo comum
1. Cadastre um plano (`Admin\Commercial\PlanController`) e vincule servicos/beneficios.
2. Gere um orcamento selecionando cliente, plano e tabela de servicos.
3. Adicione itens (`BudgetItem`) definindo tipo (`percent`, `fixed`), quantidade, desconto e periodo.
4. Chame `calculateTotals()` apos editar itens para sincronizar os campos agregados.
5. Registre eventos (enviado, aberto, aceito) para alimentar graficos de funil.
6. Uma vez aprovado, associe o orcamento a um projeto (relacao `Budget::project()`).

## Pagamentos
- Pagamentos podem ser gerados automaticamente com valores rateados (ver `BudgetPayment`), escolhendo um como `is_selected`.
- `Budget` guarda desconto global (`discount_amount`) e percentuais de impostos (`tax_percent`) usados para recalcular sub/total.

## Dashboards e KPIs
- As rotas comerciais incluem dashboards (`Admin\Commercial\DashboardController`) e KPIs (`Admin\Commercial\KpiController`), alimentados pelos registros de orcamentos, eventos e planos (`routes/web.php:32-44`).
- O painel de KPIs é restrito a usuários com `role=admin`: o middleware do controller retorna 403 para demais perfis e as tabs/cartões da interface são escondidos para evitar acessos incorretos.
- Garanta que migracoes e seeds dessas entidades estejam atualizadas antes de publicar graficos.

