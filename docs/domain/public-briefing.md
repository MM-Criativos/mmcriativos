# Briefing publico

## Objetivo
Permitir que clientes alimentem percepcoes e respostas qualitativas diretamente de um link publico, sem acesso ao painel. Os dados alimentam interpretacoes internas e historico de relacionamento.

## Endpoints
- `PublicBriefingController@perception` carrega o projeto + cliente e devolve a view `public/briefing/perception` com a rota de submit (`app/Http/Controllers/Site/PublicBriefingController.php:12-19`).
- `PublicBriefingController@savePerception` processa o POST (`routes/web.php` define estas rotas publicas).

## Validacao
- Espera `responses` como array; cada item contem `value` (inteiro opcional) e `comment` (string opcional).
- Se o projeto nao possuir `client_id` a acao retorna `back()->with('status', ...)` informando falha (`PublicBriefingController::savePerception`).

## Persistencia
- Operacao roda em `DB::transaction` para garantir consistencia (`app/Http/Controllers/Site/PublicBriefingController.php:33-54`):
  1. Extrai os IDs enviados e busca os registros `PlanningBriefingRegua`.
  2. Para cada resposta valida, faz `firstOrNew` em `PlanningBriefingResponse` com chaves (`project_id`, `client_id`, `briefing_regua_id`).
  3. Atualiza `value` e `comment`.

## Experience do cliente
- Apos salvar, renderiza `public.briefing.perception-success` com mensagem amistosa, delay de redirecionamento e destino vindo de `config('app.url')` (`PublicBriefingController::savePerception`:55-64).
- Texto padrao agradece e informa que a equipe entrara em contato.

## Consideracoes
- Sempre gere tokens publicos unicos por projeto quando compartilhar o link.
- Valide se o cliente esta autorizado antes de exibir informacoes sensiveis (atualmente o controller apenas verifica existencia de `client_id`).
- Audite as respostas importantes (ex.: criar eventos ou notificar equipe comercial) para manter contexto atualizado.
