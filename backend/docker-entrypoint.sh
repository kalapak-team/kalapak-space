#!/bin/sh

cd /var/www/html || exit 1

# Render injects PORT (usually 10000). Local Docker uses 8000.
PORT=${PORT:-10000}

export PGSSLMODE="${DB_SSLMODE:-prefer}"

# Render free (~512MB): Redis TLS + migrate + artisan serve OOMs and crash-loops.
# Prefer file/sync unless explicitly overridden with ALLOW_REDIS=1.
if [ "$APP_ENV" = "production" ] && [ "${ALLOW_REDIS}" != "1" ]; then
  echo "==> [Memory] Forcing CACHE_DRIVER=file QUEUE_CONNECTION=sync (set ALLOW_REDIS=1 to use Redis)."
  export CACHE_DRIVER=file
  export CACHE_STORE=file
  export QUEUE_CONNECTION=sync
  export SESSION_DRIVER=file
elif [ "$CACHE_DRIVER" = "redis" ] || [ "$CACHE_STORE" = "redis" ] || [ "$SESSION_DRIVER" = "redis" ]; then
  if [ "$APP_ENV" = "production" ] && [ -z "$REDIS_PASSWORD" ]; then
    echo "==> [Redis] No REDIS_PASSWORD in production — using file/sync drivers."
    export CACHE_DRIVER=file
    export CACHE_STORE=file
    export QUEUE_CONNECTION=sync
    export SESSION_DRIVER=file
  else
    if [ "$CACHE_DRIVER" = "redis" ] || [ "$CACHE_STORE" = "redis" ]; then
      export CACHE_DRIVER="${CACHE_DRIVER:-redis}"
      export CACHE_STORE="${CACHE_STORE:-redis}"
    fi
    echo "==> [Redis] Upstash enabled (cache=${CACHE_STORE:-$CACHE_DRIVER}, session=${SESSION_DRIVER})."
  fi
fi

# Legacy: database sessions only when explicitly opted in (redis sessions preferred with ALLOW_REDIS=1).
if [ "$APP_ENV" = "production" ] && [ "${ALLOW_DB_SESSIONS}" = "1" ] && [ "${ALLOW_REDIS}" != "1" ]; then
  export SESSION_DRIVER=database
fi

if [ ! -f vendor/autoload.php ]; then
  echo "==> Installing composer dependencies..."
  if [ "$APP_ENV" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
  else
    composer install --optimize-autoloader --no-interaction
  fi
fi

if [ -z "$APP_KEY" ]; then
  echo "==> APP_KEY missing — generating..."
  php artisan key:generate --force --no-interaction 2>&1 || true
fi

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

run_migrations() {
  echo "==> [DB] DB_HOST=${DB_HOST} DB_DATABASE=${DB_DATABASE}"
  echo "==> [DB] Running migrations (memory-capped)..."
  if command -v timeout >/dev/null 2>&1; then
    timeout "${MIGRATE_TIMEOUT:-90}" php -d memory_limit=96M artisan migrate --force --no-interaction 2>&1 || true
  else
    php -d memory_limit=96M artisan migrate --force --no-interaction 2>&1 || true
  fi
}

# Run migrations before serving so tables (e.g. sessions) exist on first request.
# Set MIGRATE_DELAY_SECONDS>0 only on tiny hosts where migrate+serve OOMs.
if [ "$APP_ENV" = "production" ]; then
  if [ "${RUN_MIGRATIONS}" = "1" ] || [ "${RUN_MIGRATIONS}" = "true" ]; then
    if [ "${MIGRATE_DELAY_SECONDS:-0}" -gt 0 ] 2>/dev/null; then
      (
        sleep "${MIGRATE_DELAY_SECONDS}"
        run_migrations
      ) &
    else
      run_migrations
    fi
  else
    echo "==> Skipping migrations (set RUN_MIGRATIONS=1 to enable)."
  fi

  echo "==> Caching config..."
  php -d memory_limit=96M artisan config:cache 2>&1 || true

  echo "==> Listening on 0.0.0.0:${PORT} (health: /ping.php, Laravel: /up)"
  exec php -d memory_limit=192M artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
fi

if [ "${RUN_MIGRATIONS}" = "1" ] || [ "${RUN_MIGRATIONS}" = "true" ]; then
  run_migrations
fi
echo "==> Listening on 0.0.0.0:${PORT} (health: /ping.php, Laravel: /up)"
exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
