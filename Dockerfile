# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia apenas composer (cache mais rápido)
COPY composer.json composer.lock ./

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copia todo o projeto
COPY . .

# Gera caches do Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache


# ==========================
# FASE 2 - NODE BUILDER (Vite)
# ==========================
FROM node:18 AS node_builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

RUN npm run build


# ==========================
# FASE 3 - FINAL IMAGE
# ==========================
FROM webdevops/php-nginx:8.2 AS final
WORKDIR /app

# Copia backend compilado
COPY --from=php_builder /app /app

# Copia arquivos Vite gerados
COPY --from=node_builder /app/public/build /app/public/build

# Document root do Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# Storage link (não dá erro se já existir)
RUN php artisan storage:link || true

EXPOSE 80

