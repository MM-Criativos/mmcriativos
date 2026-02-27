# Deploy

## Checklist pre-deploy
- Garanta que `APP_ENV`, `APP_URL`, `APP_KEY` e credenciais de banco/servicos estao definidos no `.env` do servidor.
- Configure `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER` conforme a infraestrutura (DB, Redis, etc).
- Crie as pastas `storage` com permissoes de escrita pelo usuario do PHP-FPM/Apache.
- Para uploads persistentes em Docker/Coolify, monte um volume em `/app/storage/app/public`.
- Em producao, use `FILESYSTEM_DISK=public` no `.env`.
- Verifique se `ffmpeg`/`ffprobe` estao instalados quando usar as rotinas de video (`.env.example:70-84`).

## Persistencia de uploads
- O projeto salva imagens/videos no disco `public` do Laravel (`storage/app/public`).
- As URLs publicas saem como `/storage/...`, portanto o link simbolico `public/storage -> storage/app/public` precisa existir.
- Em ambiente Docker este link deve ser criado no build/start do container e o volume precisa apontar para `/app/storage/app/public`.
- Se necessario, recrie manualmente com `php artisan storage:link`.

## Pipeline sugerido
1. **Baixar codigo**: `git pull` na branch de producao.
2. **Dependencias**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```
3. **Configs e cache**:
   ```bash
   php artisan config:clear
   php artisan migrate --force
   php artisan optimize
   ```
4. **Filas e scheduler**:
   - Reinicie workers: `php artisan queue:restart` e em seguida `php artisan queue:work --tries=3`.
   - Configure cron para `php artisan schedule:run` a cada minuto.
5. **Logs**:
   - Aponte `LOG_CHANNEL=stack` ou provider equivalente.
   - Opcional: rode `php artisan pail --timeout=0` em um screen/tmux para observacao em tempo real (apoia debug rapido conforme usado em `composer run dev`).

## Validacao pos-deploy
- Acesse `/admin` e verifique autenticacao/Breeze.
- Cadastre uma tarefa dummy para confirmar relacionamento com skills e usuarios (`resources/views/admin/projects/steps/develepoment/create.blade.php`).
- Envie um formulario de briefing publico em staging para validar permissao e persistencia (`app/Http/Controllers/Site/PublicBriefingController.php`).
- Rode `composer run test` (opcional) quando o servidor permitir.
