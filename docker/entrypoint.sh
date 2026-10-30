#!/bin/sh
set -e

echo "Optimizing package discovery..."
php artisan package:discover --ansi

echo "Preparing Laravel upload and storage directories..."
mkdir -p \
    storage/app/private \
    storage/app/private/livewire-tmp \
    storage/app/public \
    storage/app/livewire-tmp \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R u+rwX,g+rwX storage bootstrap/cache
php artisan storage:link --force

echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force

exec "$@"
