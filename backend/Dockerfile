FROM php:8.3-cli-alpine

# Install system dependencies (coreutils = GNU timeout for entrypoint migrations)
RUN apk add --no-cache \
    git curl zip unzip libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev icu-dev postgresql-dev oniguruma-dev \
    nodejs npm postgresql-client coreutils

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

# Increase PHP upload limits
RUN echo 'upload_max_filesize = 20M' > /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'post_max_size = 25M' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (Docker layer cache)
COPY composer.json composer.lock ./

# Install deps before app source (scripts run after full COPY below)
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY . .

# Discover packages + optimized autoload (required for artisan on a fresh image)
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan package:discover --ansi

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint.sh"]
