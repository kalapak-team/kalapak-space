#!/bin/sh
set -e

cd /var/www/html

# Use Render's PORT or default to 8000
PORT=${PORT:-8000}

# Install composer dependencies if vendor is missing
if [ ! -f vendor/autoload.php ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate app key only if not already set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction 2>/dev/null || true
fi

# Clear config cache
php artisan config:clear 2>/dev/null || true

# ============================================================
# DATABASE MIGRATION STRATEGY
# ============================================================
# FRESH_DB=true  → drops ALL tables and re-seeds (first deploy / broken DB)
# FRESH_DB=false → runs normal incremental migrations (default)
#
# Set FRESH_DB=true in Render Environment Variables for first deploy,
# then remove it or set to false after the database is working.
# ============================================================
if [ "$FRESH_DB" = "true" ]; then
    echo "==> FRESH_DB=true: Dropping all tables and re-migrating with seed..."
    php artisan migrate:fresh --seed --force --no-interaction
    echo "==> Database rebuilt successfully! Set FRESH_DB=false now."
else
    echo "==> Running incremental migrations..."
    php artisan migrate --force --no-interaction
fi

# Clear application cache (safe after migrations)
php artisan cache:clear 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

# Set permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "Starting Laravel server on port ${PORT}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT}
