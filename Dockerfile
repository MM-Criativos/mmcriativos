# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia tudo de uma vez (necessário para artisan existir)
COPY . .

# Instala dependências do PHP
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

# Copia o backend gerado pelo PHP builder
COPY --from=php_builder /app /app

# Copia build do Vite
COPY --from=node_builder /app/public/build /app/public/build

# Define root do NGINX
ENV WEB_DOCUMENT_ROOT=/app/public

# Corrige permissões necessárias do Laravel
RUN chown -R application:application /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Cria o symlink do storage (não quebra se já existir)
RUN php artisan storage:link || true

EXPOSE 80
