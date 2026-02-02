# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

# Copia o código (artisan precisa existir)
COPY . .

# Instala dependências PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# NÃO roda artisan optimize aqui
# NÃO roda artisan config/cache aqui


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
FROM webdevops/php-nginx:8.2

WORKDIR /app

# Copia backend pronto
COPY --from=php_builder /app /app

# Copia build do Vite
COPY --from=node_builder /app/public/build /app/public/build

# Documento público
ENV WEB_DOCUMENT_ROOT=/app/public

# Copia entrypoint custom (RESPONSÁVEL PELO .env)
COPY docker/entrypoint.sh /entrypoint-custom.sh
RUN chmod +x /entrypoint-custom.sh

# Permissões corretas
RUN chown -R application:application /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Symlink de storage (não falha build)
RUN php artisan storage:link || true

# Usa o entrypoint custom → depois chama o original da imagem
ENTRYPOINT ["/entrypoint-custom.sh"]

EXPOSE 80
