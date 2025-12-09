# ================================
# FASE 1 - BUILDER
# ================================
FROM webdevops/php-nginx:8.2 AS builder

WORKDIR /app

# Dependências do sistema
RUN apt-get update && apt-get install -y curl git unzip libzip-dev && \
    docker-php-ext-install zip

# Instala Node 20 (necessário pro Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Copia tudo do projeto
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Instala dependências JS e builda assets
RUN npm ci && npm run build

# Gera caches do Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache


# ================================
# FASE 2 - FINAL
# ================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia tudo já compilado do builder
COPY --from=builder /app /app

# DocumentRoot Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# Garante o storage link
RUN php artisan storage:link || true

EXPOSE 80
