#!/bin/sh

cd /var/www/html || exit 1

# Render sets PORT (often 10000); default matches Render's typical assignment.
PORT=${PORT:-10000}

export PGSSLMODE="${DB_SSLMODE:-prefer}"

if [ ! -f vendor/autoload.php ]; then
  echo "==> Installing composer dependencies..."
  composer install --no-dev --optimize-autoloader --no-interaction
fi

if [ -z "$APP_KEY" ]; then
  echo "==> APP_KEY missing — generating..."
  php artisan key:generate --force --no-interaction 2>&1 || true
fi

php artisan package:discover --ansi 2>&1 || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> [DB] DB_HOST=${DB_HOST} DB_DATABASE=${DB_DATABASE} DB_SSLMODE=${DB_SSLMODE:-prefer}"

if [ -z "${RUN_MIGRATIONS}" ] && [ "${APP_ENV}" = "production" ]; then
  RUN_MIGRATIONS=1
fi
if [ "${RUN_MIGRATIONS}" = "1" ] || [ "${RUN_MIGRATIONS}" = "true" ]; then
  (
    echo "==> [DB] Migrations (background, max ${MIGRATE_TIMEOUT:-90}s)..."
    if command -v timeout >/dev/null 2>&1; then
      timeout "${MIGRATE_TIMEOUT:-90}" php artisan migrate --force --no-interaction 2>&1
    else
      php artisan migrate --force --no-interaction 2>&1
    fi
  ) &
fi

if [ -z "$REDIS_PASSWORD" ]; then
  if [ "$CACHE_DRIVER" = "redis" ] || [ "$SESSION_DRIVER" = "redis" ]; then
    echo "==> [Redis] No REDIS_PASSWORD — using file/sync drivers."
    export CACHE_DRIVER=file
    export SESSION_DRIVER=file
    export QUEUE_CONNECTION=sync
  fi
fi

php artisan --version 2>&1 || echo "==> WARN: artisan not ready (server will still start)"

echo "==> Listening on 0.0.0.0:${PORT} (ping: /ping.php, Laravel: /up)"
exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
