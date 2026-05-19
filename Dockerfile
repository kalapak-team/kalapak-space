# Render: Docker context = repo root (.), Dockerfile path = Dockerfile
FROM richarvey/nginx-php-fpm:3.1.6

USER root

RUN apk add --no-cache \
    postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql gd zip intl bcmath exif pcntl mbstring

COPY backend/ /var/www/html

WORKDIR /var/www/html

ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

RUN chmod +x /var/www/html/scripts/*.sh 2>/dev/null || true \
    && chmod -R 775 storage bootstrap/cache

CMD ["/start.sh"]
