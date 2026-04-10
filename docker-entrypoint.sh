#!/bin/sh
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/ not found, running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec php-fpm
