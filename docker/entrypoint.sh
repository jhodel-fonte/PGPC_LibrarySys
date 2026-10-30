#!/bin/sh
set -e

echo "Clearing stale Laravel caches..."
php artisan optimize:clear

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

echo "Preparing Nginx temporary upload directories..."
mkdir -p /var/lib/nginx/tmp/fastcgi /var/lib/nginx/tmp/client_body
chown -R www-data:www-data /var/lib/nginx/tmp
chmod 770 /var/lib/nginx/tmp /var/lib/nginx/tmp/fastcgi /var/lib/nginx/tmp/client_body
php artisan storage:link --force

echo "Verifying upload runtime configuration..."
php -r 'foreach (["upload_max_filesize", "post_max_size", "upload_tmp_dir"] as $key) { echo $key . "=" . (ini_get($key) ?: "(default)") . PHP_EOL; }'
test -w storage/app/private/livewire-tmp
test -w storage/app/public

echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force

exec "$@"
