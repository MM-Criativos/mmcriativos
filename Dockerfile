# 1. Fase de Build (para instalar dependências)
FROM webdevops/php-nginx:8.2 AS builder

# Define o diretório de trabalho dentro do container
WORKDIR /app

# ----------------------------------------------------------------------
# AÇÃO CRÍTICA: Instalação do Netcat (NC) - Necessário se for usar o loop 'nc'
USER root
RUN apt-get update && apt-get install -y netcat-openbsd --no-install-recommends && rm -rf /var/lib/apt-get/lists/*
USER application
# ----------------------------------------------------------------------

# Copia os arquivos de configuração do Composer e o código
COPY composer.* ./
COPY . .

# ----------------------------------------------------------------------
# REMOVIDO: RUN composer install... foi removido daqui
# e movido para o 'command' do docker-compose.yml para execução no deploy.
# REMOVIDO: RUN php artisan optimize
# ----------------------------------------------------------------------

# 2. Fase Final (Runtime)
FROM webdevops/php-nginx:8.2 AS final

WORKDIR /app

# Copia o código com as dependências instaladas da fase 'builder'
COPY --from=builder /app /app

# Define a raiz do documento para o Nginx
ENV WEB_DOCUMENT_ROOT /app/public
