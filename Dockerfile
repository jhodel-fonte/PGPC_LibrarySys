# Match the modern requirements of your Laravel 13 dependencies
FROM php:8.4-fpm-alpine

# Install system dependencies, PostgreSQL dev libraries, and libzip
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libxml2-dev \
    libzip-dev \
    curl

# Install PHP extensions including zip (required by tallstackui)
RUN docker-php-ext-install pdo pdo_pgsql bcmath zip

# Get the latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing project files
COPY . .

# Install production dependencies and optimize Laravel
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Copy server configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Setup permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80 for Render
EXPOSE 80

# Copy and set up the entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the entrypoint script to run on startup
ENTRYPOINT ["entrypoint.sh"]

# Run supervisor to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
