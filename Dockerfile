# Laravel with PHP 8.4
FROM php:8.4-fpm-alpine

# Install system dependencies and image processing libraries for GD
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libxml2-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    curl \
    nodejs \
    npm

# Configure and install required PHP extensions including gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    bcmath \
    zip \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set application working directory
WORKDIR /var/www

# Copy application source files
COPY . .

# Install production dependencies
RUN composer install \
    --no-interaction \
    --optimize-autoloader \
    --no-dev \
    --no-scripts

# Discover Laravel packages and publish Livewire JavaScript assets
RUN php artisan package:discover --ansi \
    && php artisan livewire:publish --assets

# Build Vite frontend assets
RUN npm install \
    && npm run build

# Install Nginx and Supervisor configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-upload.ini
COPY docker/supervisord.conf /etc/supervisord.conf

# Create Nginx FastCGI temporary directories
# and assign permissions to the Nginx worker user
RUN mkdir -p /var/lib/nginx/tmp/fastcgi \
    && chown -R www-data:www-data /var/lib/nginx/tmp \
    && chmod 700 /var/lib/nginx/tmp \
    && chmod 700 /var/lib/nginx/tmp/fastcgi

# Set Laravel writable directory permissions
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache \
    /var/www/public

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Add these lines near the bottom of your existing Dockerfile
RUN echo "upload_max_filesize = 70M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 70M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Install the container entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Run the entrypoint on container startup
ENTRYPOINT ["entrypoint.sh"]

# Start Nginx and PHP-FPM through Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
