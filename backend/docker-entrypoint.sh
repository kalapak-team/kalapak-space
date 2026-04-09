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
# DATABASE: try migrate, if fails → nuke and rebuild
# ============================================================
echo "==> Attempting database migration..."
php artisan migrate --force --no-interaction 2>&1 || {
    echo ""
    echo "==> [!] Migration FAILED — auto-recovering with migrate:fresh --seed"
    echo ""
    php artisan migrate:fresh --seed --force --no-interaction
    echo "==> Database rebuilt successfully!"
}

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
