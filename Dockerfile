# ==========================
# FASE 1 - PHP BUILDER
# ==========================
FROM webdevops/php-nginx:8.2 AS php_builder

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist


# ==========================
# FASE 2 - NODE BUILDER
# ==========================
FROM node:18 AS node_builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build


# ==========================
# FASE 3 - FINAL
# ==========================
FROM webdevops/php-nginx:8.2

WORKDIR /app

COPY --from=php_builder /app /app
COPY --from=node_builder /app/public/build /app/public/build

ENV WEB_DOCUMENT_ROOT=/app/public

RUN chown -R application:application /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

RUN php artisan storage:link || true

EXPOSE 80
