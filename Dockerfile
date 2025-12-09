# ============================================
# BASE IMAGE
# ============================================
FROM webdevops/php-nginx:8.2

# Set working directory
WORKDIR /app

# ============================================
# COPIA TODO O PROJETO (necessário para artisan existir)
# ============================================
COPY . .

# ============================================
# INSTALA DEPENDÊNCIAS PHP
# ============================================
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ============================================
# PERMISSÕES LARAVEL
# ============================================
RUN chown -R application:application /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# ============================================
# BUILD DO FRONT (Vite)
# ============================================
RUN npm install && npm run build

# ============================================
# CACHE DO LARAVEL
# ============================================
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan cached packages:discover || true

RUN php artisan optimize

# ============================================
# STORAGE LINK
# ============================================
RUN php artisan storage:link || true

# Define root do nginx
ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 80
