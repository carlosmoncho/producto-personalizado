#!/bin/bash
set -e

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Running Laravel seeders..."
php artisan db:seed --force || true

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
