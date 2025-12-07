# 1. Fase de Build (para instalar dependências)
FROM webdevops/php-nginx:8.2 AS builder

# Define o diretório de trabalho dentro do container
WORKDIR /app

# Copia os arquivos de configuração do Composer e o código
COPY composer.* ./
COPY . .

# Executa as instalações necessárias (como no seu 'command' anterior)
RUN composer install --no-dev --optimize-autoloader

# Limpa caches
RUN php artisan optimize

# 2. Fase Final (Runtime)
# Usa uma imagem menor ou a mesma, mas a partir do estado final do 'builder'
# Isso garante que a imagem final contenha o código e as dependências instaladas.
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia o código com as dependências instaladas da fase 'builder'
COPY --from=builder /app /app

# Define a raiz do documento para o Nginx
ENV WEB_DOCUMENT_ROOT /app/public

# Adiciona o comando de entrada que executa as migrações e o supervisor
# Este comando garante que o Laravel esteja pronto antes de iniciar o servidor
CMD ["sh", "-c", "php artisan migrate --force && supervisord -c /opt/docker/etc/supervisor.conf"]
