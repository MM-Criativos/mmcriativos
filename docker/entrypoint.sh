#!/bin/sh
set -e

cd /app

# Se o .env não existe dentro do container, cria a partir das env vars do runtime
if [ ! -f /app/.env ]; then
  echo "[entrypoint] /app/.env not found. Creating from environment variables..."

  # Gera um .env somente com variáveis relevantes (evita lixo de NGINX/PHP internos)
  cat > /app/.env <<'EOF'
APP_NAME=${APP_NAME}
APP_ENV=${APP_ENV}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}

APP_LOCALE=${APP_LOCALE}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE}
APP_FAKER_LOCALE=${APP_FAKER_LOCALE}

LOG_CHANNEL=${LOG_CHANNEL}
LOG_LEVEL=${LOG_LEVEL}

DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=${SESSION_DRIVER}
SESSION_LIFETIME=${SESSION_LIFETIME}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE}
SESSION_SAME_SITE=${SESSION_SAME_SITE}
SESSION_DOMAIN=${SESSION_DOMAIN}

CACHE_DRIVER=${CACHE_DRIVER}
QUEUE_CONNECTION=${QUEUE_CONNECTION}
FILESYSTEM_DISK=${FILESYSTEM_DISK}

MAIL_MAILER=${MAIL_MAILER}
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME=${MAIL_FROM_NAME}

CONTACT_TO=${CONTACT_TO}

REDIS_CLIENT=${REDIS_CLIENT}

IMAGE_MAX_WIDTH=${IMAGE_MAX_WIDTH}
IMAGE_MAX_HEIGHT=${IMAGE_MAX_HEIGHT}
IMAGE_QUAL=${IMAGE_QUAL}
PNG_COMPRESSION=${PNG_COMPRESSION}

VIDEO_MAX_WIDTH=${VIDEO_MAX_WIDTH}
VIDEO_MAX_HEIGHT=${VIDEO_MAX_HEIGHT}
VIDEO_CRF=${VIDEO_CRF}
VIDEO_PRESET=${VIDEO_PRESET}
VIDEO_AAC_BITRATE=${VIDEO_AAC_BITRATE}
VIDEO_POSTER_TIME=${VIDEO_POSTER_TIME}
FFMPEG_BIN=${FFMPEG_BIN}
FFPROBE_BIN=${FFPROBE_BIN}

RECAPTCHA_SITE_KEY=${RECAPTCHA_SITE_KEY}
RECAPTCHA_SECRET_KEY=${RECAPTCHA_SECRET_KEY}
EOF

  # Expande variáveis no arquivo
  # (envsubst vem no alpine; aqui pode não ter. Então fazemos substituição segura com sh)
  # Como usamos ${VAR}, o shell já expande ao gravar? Não, por isso usamos aqui:
  # Reescreve o arquivo expandindo com 'eval echo' linha a linha
  tmp="/app/.env.tmp"
  : > "$tmp"
  while IFS= read -r line; do
    # ignora linhas vazias
    if [ -z "$line" ]; then
      echo "" >> "$tmp"
      continue
    fi
    # expande ${VAR}
    eval "echo \"$line\"" >> "$tmp"
  done < /app/.env
  mv "$tmp" /app/.env

  echo "[entrypoint] .env created at /app/.env"
else
  echo "[entrypoint] /app/.env already exists. Keeping it."
fi

# Permissões (evita problemas de cache/session)
chown -R application:application /app/storage /app/bootstrap/cache || true
chmod -R 775 /app/storage /app/bootstrap/cache || true

# Limpa caches pra garantir que config/session peguem o env atual
php artisan optimize:clear || true

# Agora chama o entrypoint original da imagem
exec /entrypoint
