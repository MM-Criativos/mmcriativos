# ============================================
# FASE 1 - BUILDER PHP
# ============================================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia projeto inteiro
COPY . .

# Instala dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


# ============================================
# FASE 2 - NODE BUILDER (Vite)
# ============================================
FROM node:18 AS node_builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

RUN npm run build


# ============================================
# FASE 3 - FINAL IMAGE
# ============================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia arquivos do PHP e vendor
COPY --from=php_builder /app /app

# Copia build do Vite
COPY --from=node_builder /app/public/build /app/public/build

# Document root
ENV WEB_DOCUMENT_ROOT=/app/public

# Corrige permissões
RUN mkdir -p /app/storage/framework/views \
    && mkdir -p /app/storage/framework/cache \
    && mkdir -p /app/storage/framework/sessions \
    && chmod -R 775 /app/storage \
    && chown -R application:application /app/storage

# Garante que o .env foi copiado antes de otimizar
RUN if [ -f /app/.env ]; then \
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan event:clear && \
    php artisan optimize; \
    fi

# Link simbólico para storage
RUN php artisan storage:link || true

EXPOSE 80
