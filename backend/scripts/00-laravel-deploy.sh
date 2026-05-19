#!/usr/bin/env bash
# Runs once before nginx starts. Keep this fast — slow starts look like "infinite loading".

cd /var/www/html

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
php artisan migrate --force --no-interaction || echo "==> WARN: migrations failed"

echo "==> Deploy script done"
