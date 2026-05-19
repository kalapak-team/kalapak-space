# Render (repo root context): same as backend/Dockerfile — use if dashboard points here.
FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql gd zip intl bcmath exif pcntl mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY backend/ .

ENV APP_KEY=base64:ZW1wdHkta2V5LWZvci1kb2NrZXItYnVpbGQ=
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan package:discover --ansi

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY backend/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD ["/docker-entrypoint.sh"]
