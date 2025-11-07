# Operacao continua

## Filas
- Driver padrao: `database` (`.env.example:36-44`). Garanta tabelas `jobs` e `failed_jobs`.
- Worker recomendado:
  ```bash
  php artisan queue:work --tries=3 --max-time=3600
  ```
- Em desenvolvimento, `composer run dev` ja sobe `php artisan queue:listen --tries=1` para feedback rapido (`composer.json:45-47`).
- Reinicie workers a cada deploy com `php artisan queue:restart`.

## Logs e monitoramento
- Canal default `stack` com storage local (`.env.example:18-21`). Configure syslog/Sentry conforme necessidade.
- Para tail em tempo real use `php artisan pail --timeout=0` (incluso no script `composer run dev`).
- Ative notificacoes quando o log crescer demais ou conter excecoes criticas.

## Scheduler
- Adicione no cron do servidor:
  ```
  * * * * * cd /var/www/mmcriativos && php artisan schedule:run >> /dev/null 2>&1
  ```
- Coloque dentro das tasks agendadas qualquer rotina de limpeza, envio de emails ou atualizacoes de status pendentes.

## Armazenamento
- `FILESYSTEM_DISK=local` por padrao (`.env.example:37-39`). Configure S3/Wasabi se necessario ajustando `AWS_*`.
- Execute `php artisan storage:link` apos provisionar servidores web para expor uploads.
- Tenha backup frequente de `storage/app`, banco de dados e `.env`.

## Saude das aplicacoes
- Endpoints criticos: `/admin`, `/dashboard`, `/modal-content/*`, `/public/briefing/*`.
- Verifique se as rotas publicas retornam 200 e se o painel autentica com fluxo Breeze.
- Scripts `composer run test` e `npm run build` devem permanecer verdes antes de liberar release.
