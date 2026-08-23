#!/bin/sh

# Exit immediately if any command fails
set -e

# Cache configurations for production speed
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Automatically run database migrations on Render
echo "Running database migrations..."
php artisan migrate --force

# Start the main container processes (Supervisor -> Nginx & PHP)
exec "$@"
