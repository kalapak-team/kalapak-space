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
# DATABASE: Nuclear reset via raw SQL then migrate
# ============================================================
# Laravel's migrate:fresh only drops tables. On Render's PostgreSQL,
# orphaned sequences/types from failed migrations can block CREATE TABLE.
# Solution: DROP the entire public schema and recreate it.
# ============================================================
echo "==> Resetting database schema (DROP SCHEMA public CASCADE)..."
php -r "
try {
    \$dsn = 'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');
    \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    \$pdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
    \$pdo->exec('CREATE SCHEMA public');

    \$user = getenv('DB_USERNAME');
    \$pdo->exec('GRANT ALL ON SCHEMA public TO \"' . \$user . '\"');
    \$pdo->exec('GRANT ALL ON SCHEMA public TO PUBLIC');

    echo \"==> Schema reset OK\n\";
} catch (Exception \$e) {
    echo '==> Schema reset error: ' . \$e->getMessage() . \"\n\";
    echo \"==> Falling back to db:wipe...\n\";
}
" 2>&1

echo "==> Running migrations with seed..."
php artisan migrate --seed --force --no-interaction 2>&1

if [ $? -ne 0 ]; then
    echo "==> [!] Migration failed. Trying db:wipe then migrate..."
    php artisan db:wipe --force --no-interaction 2>&1 || true
    php artisan migrate --seed --force --no-interaction 2>&1
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
