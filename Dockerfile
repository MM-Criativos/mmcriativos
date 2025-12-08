# 1. Fase de Build (para instalar dependências)
FROM webdevops/php-nginx:8.2 AS builder

# Define o diretório de trabalho dentro do container
WORKDIR /app

# ----------------------------------------------------------------------
# AÇÃO CRÍTICA: Instalação do Netcat (NC)
# Isso corrige o erro "sh: nc: not found"
USER root
RUN apt-get update && apt-get install -y netcat-openbsd --no-install-recommends && rm -rf /var/lib/apt/lists/*
USER application
# ----------------------------------------------------------------------

# Copia os arquivos de configuração do Composer e o código
COPY composer.* ./
COPY . .

# Executa as instalações necessárias (como no seu 'command' anterior)
RUN composer install --no-dev --optimize-autoloader

# Limpa caches
RUN php artisan optimize

# 2. Fase Final (Runtime)
# Usa a mesma imagem base, mas a partir do estado final do 'builder'
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia o código com as dependências instaladas da fase 'builder'
COPY --from=builder /app /app

# Define a raiz do documento para o Nginx
ENV WEB_DOCUMENT_ROOT /app/public

# ----------------------------------------------------------------------
# REMOVIDO: O comando de inicialização (CMD) foi removido daqui.
# Ele será definido no 'command' do docker-compose.yml
# para que possamos usar o for loop de espera ativa.
# ----------------------------------------------------------------------
