# ============================================================
# FASE 1 - BUILDER (instala dependências e prepara aplicação)
# ============================================================
FROM webdevops/php-nginx:8.2 AS builder

WORKDIR /app

# Copia tudo primeiro
COPY . .

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Otimizações Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# ============================================================
# FASE 2 - FINAL (imagem enxuta e pronta para rodar)
# ============================================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia tudo da fase "builder"
COPY --from=builder /app /app

# Define raiz do documento para o Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# Link storage
RUN php artisan storage:link || true

EXPOSE 80
