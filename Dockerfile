# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia tudo de uma vez (necessário para artisan existir)
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

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

ENV WEB_DOCUMENT_ROOT=/app/public

RUN php artisan storage:link || true

EXPOSE 80
