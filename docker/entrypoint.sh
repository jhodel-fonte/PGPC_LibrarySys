#!/bin/sh
set -e

echo "Optimizing package discovery..."
php artisan package:discover --ansi

echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force

exec "$@"
