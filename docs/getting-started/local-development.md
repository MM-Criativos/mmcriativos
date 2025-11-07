# Ambiente local

## 1. Clonar o repositorio
```bash
git clone git@github.com:mmcriativos/mmcriativos.git
cd mmcriativos
```

## 2. Configurar variaveis
1. Copie o arquivo de exemplo: `cp .env.example .env` (Linux/mac) ou `copy .env.example .env` (Windows).
2. Ajuste `APP_URL`, credenciais de banco e e-mail.
3. Defina o driver desejado para cache, fila e sessao (`.env.example:30-44`).

## 3. Script completo
Rode o script integrado para provisionar tudo de uma vez:
```bash
composer run setup
```
Passos cobertos: `composer install`, copia do `.env` (caso nao exista), `php artisan key:generate`, `php artisan migrate --force`, `npm install` e `npm run build` (`composer.json:35-43`).

## 4. Alternativa manual
Se preferir granularidade:
```bash
composer install
npm install
php artisan key:generate
php artisan migrate
npm run dev   # ou npm run build
```
Lembre de criar `storage` link (`php artisan storage:link`) se for servir upload local.

## 5. Processos ativos
- `composer run dev` inicializa simultaneamente `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0` e `npm run dev` via `npx concurrently` (`composer.json:45-47`). Ideal para desenvolvimento diario.
- Se preferir dividir, execute cada comando em terminais separados.

## 6. Testes
Execute a suite completa:
```bash
composer run test
```
O script limpa caches (`php artisan config:clear --ansi`) antes de rodar `php artisan test` (`composer.json:48-51`).

## 7. Dados iniciais
- Use `php artisan migrate:fresh --seed` quando existirem seeders padrao.
- Cadastre pelo painel as skills e competencias antes de criar tarefas (`resources/views/admin/projects/steps/develepoment/create.blade.php` exige essas listas).
