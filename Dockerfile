###############################################
# STAGE 1 - PHP / COMPOSER BUILD
###############################################
FROM webdevops/php-nginx:8.2 AS php-builder

WORKDIR /app

# Copia composer primeiro para cache eficiente
COPY composer.json composer.lock ./

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copia todo o resto do projeto
COPY . .

# Gera caches Laravel (depende dos arquivos completos)
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache


###############################################
# STAGE 2 - NODE / VITE BUILD
###############################################
FROM node:20 AS node-builder

WORKDIR /app

# Copia apenas arquivos necessários do Vite primeiro
COPY package.json package-lock.json* ./

RUN npm install

# Copia todo o projeto
COPY . .

# Builda o Vite (gerará /public/build)
RUN npm run build


###############################################
# STAGE 3 - FINAL IMAGE
###############################################
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia o app PHP completo do builder
COPY --from=php-builder /app /app

# Copia os assets buildados pelo Vite
COPY --from=node-builder /app/public/build /app/public/build

# Garante permissões de storage e cache
RUN chmod -R 777 /app/storage /app/bootstrap/cache

# Link de storage
RUN php artisan storage:link || true

# Nginx root
ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 80
