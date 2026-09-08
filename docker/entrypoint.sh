#!/bin/sh

set -e

echo "=== Preparing Laravel storage ==="

mkdir -p /var/www/storage/app/private
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs

touch /var/www/storage/logs/laravel.log

chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

echo "=== Laravel permission cache ==="
php artisan permission:cache-reset

echo "=== Laravel optimize ==="
php artisan optimize

echo "=== Laravel storage link ==="
php artisan storage:link || true

echo "=== Starting server ==="
exec "$@"