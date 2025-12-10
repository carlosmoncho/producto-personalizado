#!/bin/bash
set -e

echo "Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear || true

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Running Laravel seeders..."
php artisan db:seed --force || true

echo "Caching config for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
