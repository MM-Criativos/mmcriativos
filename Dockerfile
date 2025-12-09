# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia tudo (necessário para artisan existir)
COPY . .

# Instala dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# NÃO roda artisan optimize aqui!
# NÃO roda artisan optimize:clear aqui!


# ==========================
# FASE 2 - NODE BUILDER
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

ENV WEB_DOCUMENT_ROOT=/app/public

# Ajusta permissões para storage e cache
RUN chown -R application:application /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Symlink do storage
RUN php artisan storage:link || true

EXPOSE 80
