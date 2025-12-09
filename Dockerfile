# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia tudo (precisa do artisan)
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


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

# Backend do builder PHP
COPY --from=php_builder /app /app

# Build do Vite
COPY --from=node_builder /app/public/build /app/public/build

# Root do nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# Permissões para Laravel escrever em storage e cache
RUN chown -R application:application /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Limpa e recompila caches Laravel com o ambiente de produção
RUN php artisan optimize && \
    php artisan optimize:clear

# Cria symlink do storage (não quebra se já existir)
RUN php artisan storage:link || true

EXPOSE 80
