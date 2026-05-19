# Render: nginx + PHP-FPM — https://render.com/docs/deploy-php-laravel-docker
FROM richarvey/nginx-php-fpm:3.1.6

USER root

RUN apk add --no-cache \
    postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql gd zip intl bcmath exif pcntl mbstring

WORKDIR /var/www/html

# Install dependencies at build time (fast container start on Render).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY . .

ENV APP_KEY=base64:ZW1wdHkta2V5LWZvci1kb2NrZXItYnVpbGQ=
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan package:discover --ansi

ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=0
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

COPY docker-entrypoint.sh /docker-entrypoint.sh

RUN chmod +x /docker-entrypoint.sh /var/www/html/scripts/*.sh 2>/dev/null || true \
    && chmod -R 775 storage bootstrap/cache

# Use artisan serve on $PORT — avoids nginx→php-fpm 502 on Render when FPM fails to start.
CMD ["/docker-entrypoint.sh"]
