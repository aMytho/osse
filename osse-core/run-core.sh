# Only for docker

# Cache config and run migrations
cd osse-core
php artisan config:cache
php artisan event:cache
php artisan migrate


cd ..


frankenphp run --config Caddyfile
