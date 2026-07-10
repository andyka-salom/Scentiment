#!/bin/bash
set -e

# Ensure required storage directories exist
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cache configuration & routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache || true

# Run database migrations
php artisan migrate --force

# Create storage symlink if not exists
php artisan storage:link 2>/dev/null || true

# Start Apache
exec apache2-foreground
