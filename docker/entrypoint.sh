#!/bin/sh
set -e

# Wait for database if needed
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection at $DB_HOST:$DB_PORT..."
fi

# Run database migrations and seeders if in production
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
    # Seed if users table is empty
    php artisan db:seed --force || true
fi

# Cache configuration, routes, and views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Fix permissions for storage, cache, and database
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    touch /var/www/html/database/database.sqlite
fi
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Execute supervisor to start PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
