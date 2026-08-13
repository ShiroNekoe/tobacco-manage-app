#!/bin/bash
set -e

echo "==> [Fly.io Entrypoint] Initializing environment..."

# 1. Ensure /data directory exists and has proper permissions for www-data (SQLite)
mkdir -p /data
if [ ! -f /data/database.sqlite ]; then
    echo "==> [Fly.io Entrypoint] Initializing /data/database.sqlite..."
    touch /data/database.sqlite
fi

chown -R www-data:www-data /data
chmod -R 775 /data

# 2. Ensure Laravel storage and bootstrap cache directories have proper permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Create storage link if not already linked
php /var/www/html/artisan storage:link || true

# 4. Clear old caches to ensure fresh environment variable binding
php /var/www/html/artisan config:clear || true
php /var/www/html/artisan route:clear || true
php /var/www/html/artisan view:clear || true

# 5. Run database migrations safely on persistent volume
echo "==> [Fly.io Entrypoint] Running database migrations..."
php /var/www/html/artisan migrate --force --no-interaction

# 6. Seed default users / initial data if database is fresh (safe with firstOrCreate)
echo "==> [Fly.io Entrypoint] Ensuring initial seed data..."
php /var/www/html/artisan db:seed --force --no-interaction || true

# 7. Cache config and routes for optimal production performance
php /var/www/html/artisan config:cache || true
php /var/www/html/artisan route:cache || true
php /var/www/html/artisan view:cache || true

echo "==> [Fly.io Entrypoint] Starting Apache server..."
exec apache2-foreground "$@"
