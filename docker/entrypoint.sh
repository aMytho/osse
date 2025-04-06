#!/bin/sh

# This file is ran by docker only.
# Don't modify these, modify the .env file in the root directory instead


# These are for the docker build only. If the user runs them manual production script, they are set there instead.
# Modifying these in docker could have unforseen consquences, such as things not working.
#
export APP_ENV=production

export OSSE_URL_SERVER="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_SERVER_PORT}"
export OSSE_URL_API="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_API_PORT}"
export OSSE_BROADCAST_HOST="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_BROADCAST_PORT}"
export OSSE_DOCKER_BROADCAST="osse_broadcast"

# Choose the correct Caddyfile based on OSSE_PROTOCOL
if [ "$OSSE_PROTOCOL" = "https" ]; then
    export CADDYFILE="docker/Caddyfile-https"
else
    export CADDYFILE="docker/Caddyfile-http"
fi

echo "Using Caddyfile: $CADDYFILE"

# Ensure storage & cache directories are writable
chmod -R 777 storage bootstrap/cache

export REDIS_CLIENT=phpredis
export REDIS_HOST=valkey
export REDIS_PASSWORD=null
export REDIS_PORT=6379

# Check if we need to run the setup.
if [ "$OSSE_RUN_SETUP" = "true" ]; then
    composer install --no-dev --optimize-autoloader
    frankenphp php-cli artisan key:generate --force
    frankenphp php-cli artisan view:cache
    frankenphp php-cli artisan route:cache

    # Make the storage/cache/database if they don't exist.
    mkdir storage -p
    mkdir storage/framework/cache -p
    mkdir storage/framework/sessions -p
    mkdir storage/framework/views -p
else
  if [ ! -f "/tmp/osse_setup" ]; then
    composer install --no-dev --optimize-autoloader
    frankenphp php-cli artisan key:generate --force
    frankenphp php-cli artisan view:cache
    frankenphp php-cli artisan route:cache

    # Make the storage/cache/database if they don't exist.
    mkdir storage -p
    mkdir storage/framework/cache -p
    mkdir storage/framework/sessions -p
    mkdir storage/framework/views -p

    # Dont rerun command.
    touch "/tmp/osse_setup"
  else
    echo "Setup already complete, skipping."
  fi
fi


# Cache the env and run migrations.
# Generate encryption key and cache the views.
frankenphp php-cli artisan config:cache
frankenphp php-cli artisan migrate --force

# Run the server and queue worker (jobs)
frankenphp run --config "$CADDYFILE" & frankenphp php-cli artisan queue:work --tries=3 --timeout=0
