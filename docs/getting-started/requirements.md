# Requisitos de ambiente

## Plataformas suportadas
- Linux e WSL2 (desenvolvimento principal).
- macOS 13+ com Homebrew.
- Windows 11 rodando PHP nativo ou via Laragon (setup atual mostrado em `README`).

## Dependencias obrigatorias

### Backend
- PHP 8.2 com extensoes padrao do Laravel (BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML).
- Composer 2.6+ para gerenciar os pacotes definidos em `composer.json`.
- Banco relacional com suporte a chaves estrangeiras (MySQL 8 / MariaDB 10.5+ recomendado). Para desenvolvimento rapido e testes automatizados aceitamos SQLite (`.env.example:23-29`), mas recursos como `project_tasks` dependem de chaves.

### Frontend
- Node.js 20+ e npm 10+ (ou pnpm compativel) para rodar Vite 7, Tailwind 3 e Alpine (`package.json:5-19`).

### Servicos auxiliares
- Driver de fila `database` (default). Garanta que as tabelas `jobs` e `failed_jobs` existam e mantenha um `php artisan queue:listen` ativo (`composer.json:45-47`).
- Cache, sessao e queue usam banco de dados por padrao (`.env.example:30-44`). Ajuste `CACHE_STORE`, `SESSION_DRIVER` e `QUEUE_CONNECTION` se optar por Redis ou outro provedor.
- Redis/ElastiCache opcional caso configure `REDIS_CLIENT=phpredis` (`.env.example:45-48`).
- Servidor de e-mail SMTP ou provider de testes. O fallback mantem `MAIL_MAILER=log` (`.env.example:50-57`).

### Ferramentas de midia
- `ffmpeg` e `ffprobe` no PATH quando usar as rotinas de otimizacao de video (`.env.example:70-84`). Sem eles, defina `FFMPEG_BIN`/`FFPROBE_BIN` como caminhos validos ou desative features dependentes.

## Acessos
- Crie um usuario admin via seed/migracao ou painel atual.
- Configure `APP_URL`, `CONTACT_TO` e campos de integracao antes de publicar endpoints publicos.
