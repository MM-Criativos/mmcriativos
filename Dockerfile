# ============================================================
# FASE 1 - BUILDER (instala dependências e prepara aplicação)
# ============================================================
FROM webdevops/php-nginx:8.2 AS builder

WORKDIR /app

# Instala o Composer (já vem nessa imagem)
# Copia os arquivos essenciais do Composer primeiro (melhor cache)
COPY composer.json composer.lock ./

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Agora copia TODO o código
COPY . .

# Gera otimizações
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache


# ============================================================
# FASE 2 - FINAL (imagem enxuta e pronta para rodar)
# ============================================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia tudo do builder: código + vendor + caches
COPY --from=builder /app /app

# Define raiz do documento para o Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

# (Opcional) Linka storage – só funciona se storage tem permissão
RUN php artisan storage:link || true

# Expor porta (na verdade essa imagem já cuida disso)
EXPOSE 80
