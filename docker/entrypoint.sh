#!/bin/sh

# This file is ran by docker only.
# Don't modify these, modify the docker compose file instead


# These are for the docker build only. If the user runs them manual production script, they are set there instead.
# Modifying these in docker could have unforseen consquences, such as things not working.
#
export APP_ENV=production
export OSSE_HOST=localhost # This can be your local IP, or URL (example.com)
#export OSSE_PROTOCOL=https # This can be http or https.
# export OSSE_SERVER_PORT=80
# # This port is used for the API
# export OSSE_API_PORT=9005
# This port is used for SSE (server sent events) by osse-broadcast. This port is used internally only.
export OSSE_BROADCAST_INTERNAL_PORT=9002
# This port will be externally available. Users will use this port to connect to osse-broadcast.
export OSSE_BROADCAST_PORT=9003

export OSSE_URL_SERVER="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_SERVER_PORT}"
export OSSE_URL_API="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_API_PORT}"
# TODO: Pull from docker env
export OSSE_BROADCAST_HOST="http://localhost:9004"
export OSSE_BROADCAST_INTERNAL_PORT=9003

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

# frankenphp php-cli artisan key:generate
frankenphp php-cli artisan config:cache
frankenphp php-cli artisan route:cache
# frankenphp php-cli artisan view:cache
frankenphp php-cli artisan migrate --force

# Run the server and queue worker (jobs)
frankenphp run --config "$CADDYFILE" & frankenphp php-cli artisan queue:work --tries=3 --timeout=0
