# Use this Dockerfile when Render "Docker context" is the repo root (.)
# Settings: Dockerfile Path = Dockerfile, Docker context = .
#
# If Docker context is "backend", use backend/Dockerfile instead.

FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    git curl zip unzip libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev icu-dev postgresql-dev oniguruma-dev \
    postgresql-client coreutils

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

RUN echo 'upload_max_filesize = 20M' > /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'post_max_size = 25M' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY backend/ .

ENV APP_KEY=base64:ZW1wdHkta2V5LWZvci1kb2NrZXItYnVpbGQ=
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan package:discover --ansi

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint.sh"]
