#!/bin/sh

# This file is ran by docker only.
# Don't modify these, modify the .env file in the root directory instead


# These are for the docker build only. If the user runs them manual production script, they are set there instead.
# Modifying these in docker could have unforseen consquences, such as things not working.
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

# Set the caddyfile in supervisord
echo "Using Caddyfile: $CADDYFILE"
sed -i "s|__CADDYFILE__|$CADDYFILE|g" docker/supervisor.conf

# Ensure storage & cache directories are writable
chmod -R 777 storage bootstrap/cache
# Make sure user data dir exists
mkdir -p "/osse-data"

# Ensure the database exists
if [ ! -f "/osse-data/database.sqlite" ]; then
  touch "/osse-data/database.sqlite"
  chown root:root "/osse-data/database.sqlite"
fi

# Ensure logs file exists
if [ ! -f "/osse-data/laravel.log" ]; then
  touch "/osse-data/laravel.log"
  chown root:root "/osse-data/laravel.log"
fi

# Ensure storage dirs exist
export OSSE_PRIVATE_STORAGE="/osse-data/storage/private"
export OSSE_PUBLIC_STORAGE="/osse-data/storage/public"
mkdir -p "$OSSE_PRIVATE_STORAGE"
mkdir -p "$OSSE_PUBLIC_STORAGE"

# Docker sets the app key from the host .env, but we want to use the one in the container .env file we just made.
unset APP_KEY

# Wait for redis (valkey) to go online
until nc -z valkey 6379; do
  echo "Waiting for Valkey..."
  sleep 1
done

# Cache the env and run migrations.
# Generate encryption key and cache the views.
php artisan config:cache
php artisan migrate --force

# Run the server and queue worker (jobs)
echo "Starting Osse"
exec /usr/bin/supervisord -c docker/supervisor.conf
