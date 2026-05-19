#!/usr/bin/env bash
set -e

cd /var/www/html

echo "==> Composer install"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Laravel bootstrap"
php artisan package:discover --ansi || true

if [ -z "$REDIS_PASSWORD" ]; then
  if [ "$CACHE_DRIVER" = "redis" ] || [ "$SESSION_DRIVER" = "redis" ]; then
    echo "==> Redis password missing — file/sync drivers"
    export CACHE_DRIVER=file
    export SESSION_DRIVER=file
    export QUEUE_CONNECTION=sync
  fi
fi

if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
fi

echo "==> Migrations"
php artisan migrate --force --no-interaction || echo "==> WARN: migrations failed (service will still start)"

echo "==> Deploy script done"
