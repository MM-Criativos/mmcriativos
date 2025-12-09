# ================================
# FASE 1 - BUILDER
# ================================
FROM node:20 AS node_builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js .
COPY tailwind.config.js .
COPY postcss.config.js .

RUN npm run build


# ================================
# FASE 2 - PHP BUILDER
# ================================
FROM webdevops/php-nginx:8.2 AS php_builder
WORKDIR /app

# Copia composer primeiro
COPY composer.json composer.lock ./

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copia tudo do projeto
COPY . .

# Copia o build gerado pelo Node
COPY --from=node_builder /app/public ./public

# Gera caches Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache


# ================================
# FASE FINAL
# ================================
FROM webdevops/php-nginx:8.2

WORKDIR /app

# Copia tudo já pronto do builder PHP
COPY --from=php_builder /app /app

ENV WEB_DOCUMENT_ROOT=/app/public

RUN php artisan storage:link || true

EXPOSE 80
