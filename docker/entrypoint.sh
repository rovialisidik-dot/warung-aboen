#!/bin/sh
set -e

# Wait for database if needed
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection at $DB_HOST:$DB_PORT..."
fi

# Run database migrations if in production and database configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# Cache configuration, routes, and views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Execute supervisor to start PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
