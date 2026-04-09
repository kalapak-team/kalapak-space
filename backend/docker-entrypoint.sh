#!/bin/sh

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
# DATABASE: Full schema reset using psql, then migrate
# ============================================================
echo "==> [DB] Checking env vars..."
echo "==> DB_HOST=${DB_HOST}"
echo "==> DB_PORT=${DB_PORT}"
echo "==> DB_DATABASE=${DB_DATABASE}"
echo "==> DB_USERNAME=${DB_USERNAME}"

echo "==> [DB] Dropping and recreating public schema via psql..."
PGPASSWORD="${DB_PASSWORD}" psql \
    -h "${DB_HOST}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME}" \
    -d "${DB_DATABASE}" \
    -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA public; GRANT ALL ON SCHEMA public TO \"${DB_USERNAME}\"; GRANT ALL ON SCHEMA public TO PUBLIC;" \
    2>&1

if [ $? -eq 0 ]; then
    echo "==> [DB] Schema reset successful!"
else
    echo "==> [DB] psql schema reset failed — trying Laravel db:wipe..."
    php artisan db:wipe --force --no-interaction 2>&1 || true
fi

echo "==> [DB] Running migrations with seed (verbose)..."
php artisan migrate --seed --force --no-interaction -v 2>&1

if [ $? -ne 0 ]; then
    echo "==> [DB] Migration STILL failed after schema reset!"
    echo "==> [DB] Listing existing tables for debug..."
    PGPASSWORD="${DB_PASSWORD}" psql \
        -h "${DB_HOST}" \
        -p "${DB_PORT:-5432}" \
        -U "${DB_USERNAME}" \
        -d "${DB_DATABASE}" \
        -c "\dt" \
        2>&1
    echo "==> [DB] Listing existing sequences..."
    PGPASSWORD="${DB_PASSWORD}" psql \
        -h "${DB_HOST}" \
        -p "${DB_PORT:-5432}" \
        -U "${DB_USERNAME}" \
        -d "${DB_DATABASE}" \
        -c "SELECT sequencename FROM pg_sequences WHERE schemaname = 'public';" \
        2>&1
fi

# Cache for production performance
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Create storage link
php artisan storage:link 2>/dev/null || true

# Set permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "==> Starting Laravel on port ${PORT}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT}
