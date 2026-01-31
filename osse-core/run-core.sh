# Only for docker

cd osse-core

# Generate app key if needed
OSSE_ENCRYPTION_KEY="$(grep '^APP_KEY=base64' .env)"
if [ -z "$OSSE_ENCRYPTION_KEY" ]; then
    php artisan key:generate
fi

# Cache config and run migrations
php artisan key:generate
php artisan config:cache
php artisan event:cache
php artisan migrate

cd ..

frankenphp run --config Caddyfile
