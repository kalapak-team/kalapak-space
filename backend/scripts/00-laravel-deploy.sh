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

# Render routes traffic to $PORT (e.g. 10000). start.sh copies conf before scripts run,
# so patch the live nginx config as well as the repo copy.
PORT="${PORT:-10000}"
for NGINX_CONF in \
  "/etc/nginx/sites-available/default.conf" \
  "/var/www/html/conf/nginx/nginx-site.conf"
do
  if [ -f "$NGINX_CONF" ]; then
    sed -i "s/listen 80;/listen ${PORT};/" "$NGINX_CONF"
    sed -i "s/listen \[::\]:80 default ipv6only=on;/listen [::]:${PORT} default ipv6only=on;/" "$NGINX_CONF" 2>/dev/null || true
    echo "==> nginx listen port -> ${PORT} (${NGINX_CONF})"
  fi
done

echo "==> Deploy script done"
