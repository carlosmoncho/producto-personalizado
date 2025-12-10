#!/bin/bash
# Deploy optimization script for Laravel production
# Run this after deploying new code

set -e

echo "🚀 Starting Laravel production optimization..."

# Clear all caches first
echo "📦 Clearing old caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Generating optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize composer autoloader
echo "📚 Optimizing composer autoloader..."
composer dump-autoload --optimize --no-dev

# Clear and warm up application cache
echo "🔥 Warming up application cache..."
php artisan optimize

# Restart queue workers to pick up new code
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Clear opcache if available
echo "🧹 Clearing opcache..."
if php -r "exit(function_exists('opcache_reset') ? 0 : 1);" 2>/dev/null; then
    php -r "opcache_reset();"
    echo "   Opcache cleared!"
else
    echo "   Opcache not available in CLI"
fi

echo ""
echo "✅ Optimization complete!"
echo ""
echo "📊 Cache Status:"
php artisan cache:status 2>/dev/null || echo "   (cache:status command not available)"
echo ""
echo "Remember to:"
echo "  1. Restart PHP-FPM: sudo systemctl restart php-fpm"
echo "  2. Restart queue workers: php artisan queue:restart"
echo "  3. Clear CDN cache if applicable"
