# ============================================================
# 1) NODE BUILDER - compila assets do Vite
# ============================================================
FROM node:20 AS nodebuilder

WORKDIR /app

# Copia arquivos necessários para instalar dependências
COPY package*.json ./
RUN npm install

# Copia todo o projeto
COPY . .

# Compila para produção
RUN npm run build



# ============================================================
# 2) PHP BUILDER - instala dependências PHP
# ============================================================
FROM webdevops/php-nginx:8.2 AS phpbuilder

WORKDIR /app

# Copia somente composer.json e composer.lock
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Agora copia o projeto completo
COPY . .

# Copia os assets compilados pela etapa Node
COPY --from=nodebuilder /app/public ./public

# Gera caches do Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache



# ============================================================
# 3) FINAL IMAGE - aplicação rodando
# ============================================================
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia tudo já preparado
COPY --from=phpbuilder /app /app

# Define raiz onde o nginx deve servir a aplicação
ENV WEB_DOCUMENT_ROOT=/app/public

# Cria o storage link (ignorar erro se já existir)
RUN php artisan storage:link || true

EXPOSE 80
