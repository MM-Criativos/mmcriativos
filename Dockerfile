# ================================
# FASE 1 - BUILDER
# ================================
FROM webdevops/php-nginx:8.2 AS builder

WORKDIR /app

# Copia o projeto inteiro (ANTES do composer!)
COPY . .

# Instala dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Gera caches do Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache


# ================================
# FASE 2 - FINAL
# ================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia tudo do builder (inclui vendor e caches)
COPY --from=builder /app /app

# Define o document root do Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# Garante que o storage link exista
RUN php artisan storage:link || true

EXPOSE 80
