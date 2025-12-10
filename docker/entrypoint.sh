#!/bin/bash
set -e

echo "Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear || true

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Importing data from JSON files..."
# Only import if data files exist and tables are empty
if [ -d "database/seeders/data" ] && [ "$(ls -A database/seeders/data 2>/dev/null)" ]; then
    USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null || echo "0")
    if [ "$USER_COUNT" = "0" ] || [ "$USER_COUNT" = "" ]; then
        echo "Database is empty, importing data..."
        php artisan db:import-json --path=database/seeders/data --fresh || true
    else
        echo "Database already has data ($USER_COUNT users), skipping import"
    fi
fi

echo "Caching config for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
