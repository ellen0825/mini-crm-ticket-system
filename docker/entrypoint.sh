#!/bin/sh
set -e

# Copy env if not present
[ -f .env ] || cp .env.docker .env

# Generate app key if missing
php artisan key:generate --no-interaction --force 2>/dev/null || true

# Create SQLite file if using SQLite
if grep -q "DB_CONNECTION=sqlite" .env; then
    mkdir -p database
    touch database/database.sqlite
fi

# Run migrations and seed
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction 2>/dev/null || true

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Cache config for production
php artisan config:cache
php artisan route:cache

# Start PHP-FPM in background, then Nginx in foreground
php-fpm -D
exec nginx -g "daemon off;"
