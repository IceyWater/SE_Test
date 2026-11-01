#!/bin/sh
set -eu
cd /var/www/html
: "${APP_KEY:?Set a persistent Laravel APP_KEY in Render Environment before deploying.}"
: "${APP_URL:?Set APP_URL to the HTTPS Render service URL.}"
case "${PORT:-10000}" in ''|*[!0-9]*) echo 'PORT must be numeric.' >&2; exit 1;; esac
sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-10000}>/" /etc/apache2/sites-available/000-default.conf
mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Runtime secrets must not be baked into the image or regenerated each restart.
php artisan config:clear
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi
php artisan config:cache
php artisan view:cache
exec apache2-foreground
