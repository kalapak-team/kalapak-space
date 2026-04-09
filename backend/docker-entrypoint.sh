#!/bin/sh

cd /var/www/html

# Use Render's PORT or default to 8000
PORT=${PORT:-8000}

# Export SSL mode for psql (Neon requires SSL)
export PGSSLMODE="${DB_SSLMODE:-require}"

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
# DATABASE: Clean schema reset, then migrate
# ============================================================
echo "==> [DB] Connection info:"
echo "    DB_HOST=${DB_HOST}"
echo "    DB_PORT=${DB_PORT}"
echo "    DB_DATABASE=${DB_DATABASE}"
echo "    DB_USERNAME=${DB_USERNAME}"
echo "    DB_SSLMODE=${DB_SSLMODE}"
echo "    PGSSLMODE=${PGSSLMODE}"

# Step 1: Drop and recreate schema via psql (Neon-safe with SSL)
echo "==> [DB] Step 1: DROP SCHEMA public CASCADE via psql..."
PGPASSWORD="${DB_PASSWORD}" psql \
    -h "${DB_HOST}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME}" \
    -d "${DB_DATABASE}" \
    -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA public; GRANT ALL ON SCHEMA public TO \"${DB_USERNAME}\"; GRANT ALL ON SCHEMA public TO PUBLIC;" \
    2>&1

if [ $? -eq 0 ]; then
    echo "==> [DB] Schema reset OK via psql"
else
    echo "==> [DB] psql failed — trying db:wipe fallback..."
    php artisan db:wipe --force --no-interaction 2>&1 || true
fi

# Step 2: Verify the schema is truly empty
echo "==> [DB] Step 2: Verifying schema is empty..."
PGPASSWORD="${DB_PASSWORD}" psql \
    -h "${DB_HOST}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME}" \
    -d "${DB_DATABASE}" \
    -c "SELECT tablename FROM pg_tables WHERE schemaname = 'public';" \
    2>&1

PGPASSWORD="${DB_PASSWORD}" psql \
    -h "${DB_HOST}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME}" \
    -d "${DB_DATABASE}" \
    -c "SELECT sequencename FROM pg_sequences WHERE schemaname = 'public';" \
    2>&1

# Step 3: Run migrations with verbose output to see REAL errors
echo "==> [DB] Step 3: Running migrate --seed..."
php artisan migrate --seed --force --no-interaction -vvv 2>&1

MIGRATE_EXIT=$?

if [ $MIGRATE_EXIT -ne 0 ]; then
    echo ""
    echo "==> [DB] !!! Migration failed (exit code: $MIGRATE_EXIT) !!!"
    echo "==> [DB] Dumping remaining tables after failure:"
    PGPASSWORD="${DB_PASSWORD}" psql \
        -h "${DB_HOST}" \
        -p "${DB_PORT:-5432}" \
        -U "${DB_USERNAME}" \
        -d "${DB_DATABASE}" \
        -c "\dt" \
        2>&1
    echo "==> [DB] Testing raw CREATE TABLE via psql..."
    PGPASSWORD="${DB_PASSWORD}" psql \
        -h "${DB_HOST}" \
        -p "${DB_PORT:-5432}" \
        -U "${DB_USERNAME}" \
        -d "${DB_DATABASE}" \
        -c "CREATE TABLE _test_create (id BIGSERIAL PRIMARY KEY, name VARCHAR(255) UNIQUE); DROP TABLE IF EXISTS _test_create;" \
        2>&1
    echo "==> [DB] Raw CREATE TABLE test exit code: $?"
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
