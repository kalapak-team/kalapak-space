#!/bin/sh

cd /var/www/html || exit 1

# Render injects PORT (usually 10000). Local Docker uses 8000.
PORT=${PORT:-10000}

export PGSSLMODE="${DB_SSLMODE:-prefer}"

# Redis fallback: if CACHE_DRIVER=redis but no password, fall back to file.
if [ "$CACHE_DRIVER" = "redis" ] || [ "$SESSION_DRIVER" = "redis" ]; then
  if [ "$APP_ENV" = "production" ] && [ -z "$REDIS_PASSWORD" ]; then
    echo "==> [Redis] No REDIS_PASSWORD in production — using file/sync drivers."
    export CACHE_DRIVER=file
    export QUEUE_CONNECTION=sync
  fi
fi

# Web routes use the session middleware. Upstash Redis is for cache/queue only —
# database sessions are reliable on Render and avoid Redis session driver failures.
if [ "$APP_ENV" = "production" ]; then
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
  if [ -z "${RUN_MIGRATIONS}" ] && [ "${APP_ENV}" = "production" ]; then
    RUN_MIGRATIONS=1
  fi
  if [ "${RUN_MIGRATIONS}" = "1" ] || [ "${RUN_MIGRATIONS}" = "true" ]; then
    # Skip heavy tinker schema bootstrap on every boot (OOM risk on Render free).
    # Only migrate, with a tight memory cap so the OOM killer prefers this child.
    echo "==> [DB] Running migrations (delayed, memory-capped)..."
    if command -v timeout >/dev/null 2>&1; then
      timeout "${MIGRATE_TIMEOUT:-90}" php -d memory_limit=128M artisan migrate --force --no-interaction 2>&1 || true
    else
      php -d memory_limit=128M artisan migrate --force --no-interaction 2>&1 || true
    fi
  fi
}

# Production on Render free tier (~512MB): never run migrate/config:cache
# concurrently with artisan serve — that OOMs and crash-loops (Cloudflare 502).
if [ "$APP_ENV" = "production" ]; then
  echo "==> Caching config..."
  php -d memory_limit=128M artisan config:cache 2>&1 || true

  # Delay migrations so health checks pass and RSS settles before migrate runs.
  (
    sleep "${MIGRATE_DELAY_SECONDS:-60}"
    run_migrations
  ) &

  echo "==> Listening on 0.0.0.0:${PORT} (health: /ping.php, Laravel: /up)"
  exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
fi

run_migrations
echo "==> Listening on 0.0.0.0:${PORT} (health: /ping.php, Laravel: /up)"
exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
