#!/bin/bash

# WARNING: This is a legacy script. Only use it as a reference. It probably won't work.
# Use docker or the manual install on the github wiki instead.

# Script is used in docker production only. Don't modify anything here.

# HTTPS support. Use one or the other (http or https), not both.
export OSSE_PROTOCOL=http
#export OSSE_PROTOCOL=https

# The hostname of the pc/server that is running Osse.
# For most users, this is localhost. To access the server, use http://localhost in your browser.
# If you want to access Osse from a different device, change it to your local IP. This is usually something like 192.168.0.5
# To access osse from another device, set the env and enter your local IP address in the address bar. You can access it from your server the same way. (localhost won't work anymore)
# There are several commented out examples. Only one host can be active at a time. DO NOT add an ending slash.
export OSSE_HOST=localhost
# export OSSE_HOST=192.168.0.5
# export OSSE_HOST=my-app.example.com

# This is the port Osse will serve the website from. 80 is the default because it isn't required to be entered into the URL bar.
export OSSE_SERVER_PORT=80
# This port is used for the API
export OSSE_API_PORT=9000
# This port is used for SSE (server sent events) by osse-broadcast. This port is used internally only.
export OSSE_BROADCAST_INTERNAL_PORT=9002
# This port will be externally available. Users will use this port to connect to osse-broadcast.
export OSSE_BROADCAST_PORT=9003

# This is the host and port of the redis server. DO NOT include the redis:// protocol.
export OSSE_REDIS_HOST="localhost:6379"

# Set storage path for logs and cache. The DB is also here, but you can move it with the below env variable.
export LARAVEL_STORAGE_PATH="~/.osse"
# Set the path to the database.
export DB_DATABASE="~/.osse/osse.sqlite"
# Set osse executable location. By default, it is with this shell script. If you move it, update the location.
export OSSE_EXECUTABLE="./osse"
# Set osse-broadcast executable location. Make sure you match your CPU arch. For most users, it is amd64.
export OSSE_BROADCAST_EXECUTABLE="./osse-broadcast-linux-amd64"
# export OSSE_BROADCAST_EXECUTABLE="./osse-broadcast-linux-arm64"

# The paths to scan for music. See examples below. Only absolute paths are supported (no ~ or env vars). Separate directories with comma.
export OSSE_DIRECTORIES=""
# export OSSE_DIRECTORIES="/home/me/Music,/mnt/server1/files"
# If true, allow new accounts to be created. Once you make your account, set this to false.
export allowRegistration=true

# Do not edit anything below this line! ------------------------------- If you made it this far, you can run the script!

# Set the envs for caddy
export OSSE_URL_SERVER="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_SERVER_PORT}"
export OSSE_URL_API="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_API_PORT}"

# Choose the correct Caddyfile based on OSSE_PROTOCOL
if [ "$OSSE_PROTOCOL" = "https" ]; then
    export CADDYFILE="docker/Caddyfile-https"
else
    export CADDYFILE="docker/Caddyfile-http"
fi

# Set the envs for osse-broadcast. It is not made available externally. Caddy will reverse proxy the connection to allow access.
export OSSE_BROADCAST_URL="localhost:${OSSE_BROADCAST_INTERNAL_PORT}"
# This URL will be sent to clients as the public osse broadcast URL.
export OSSE_BROADCAST_HOST="${OSSE_PROTOCOL}://${OSSE_HOST}:${OSSE_BROADCAST_PORT}"
if [ "${OSSE_PROTOCOL}" == "http" ]; then
  export OSSE_ALLOWED_ORIGIN="${OSSE_HOST}:${OSSE_SERVER_PORT}"
else
  export OSSE_ALLOWED_ORIGIN="${OSSE_HOST}:${OSSE_SERVER_PORT_SECURE}"
fi
# Check if the port ends with 443 or 80 and remove the port from OSSE_ALLOWED_ORIGIN. Browsers remove it automatically so its a different origin.
if [[ "${OSSE_ALLOWED_ORIGIN}" =~ :80$ || "${OSSE_ALLOWED_ORIGIN}" =~ :443$ ]]; then
  OSSE_ALLOWED_ORIGIN="${OSSE_HOST}"
fi

# Evaluate filepaths
eval "OSSE_EXECUTABLE=$OSSE_EXECUTABLE"
eval "LARAVEL_STORAGE_PATH=$LARAVEL_STORAGE_PATH"
eval "DB_DATABASE=$DB_DATABASE"

# Make the storage/cache/database if they don't exist.
mkdir $LARAVEL_STORAGE_PATH -p
mkdir "$LARAVEL_STORAGE_PATH"/storage -p
mkdir "$LARAVEL_STORAGE_PATH"/framework/cache -p
mkdir "$LARAVEL_STORAGE_PATH"/framework/sessions -p
mkdir "$LARAVEL_STORAGE_PATH"/framework/views -p
mkdir "$(dirname "$DB_DATABASE")" -p
touch "$DB_DATABASE"

# Ask for sudo now, since the command output can make it appear as though the password input was missed.
echo 'Starting Osse...'
sudo -v

# Check if the server is executable, and make it executable if it's not.
if [[ ! -x "$OSSE_EXECUTABLE" ]]; then  # Check if the file is NOT executable
  echo "Osse is not executable. Making it executable."
  sudo chmod +x "$OSSE_EXECUTABLE"  # Grant execute permission.
fi

# Loads the new env variables
"$OSSE_EXECUTABLE" php-cli artisan config:cache
# Run optimizations
echo 'Starting optimizations...'
"$OSSE_EXECUTABLE" php-cli artisan optimize
# Run migrations
echo 'Running database migrations...'
"$OSSE_EXECUTABLE" php-cli artisan migrate

echo "Server will be available on $OSSE_URL_SERVER and $OSSE_URL_SERVER_SECURE (if https enabled)"

# Starts osse. We run the queue (scan jobs), Reverb (websockets), and Laravel.
trap 'kill %1; kill %2' SIGINT
"$OSSE_EXECUTABLE" php-cli artisan queue:work --tries=3 --timeout=0 | tee 1.log | sed -e 's/^/[Osse Queue] /' & "$OSSE_BROADCAST_EXECUTABLE" | tee 2.log | sed -e 's/^/[Osse Broadcast] /' & sudo -E "$OSSE_EXECUTABLE" run --config "$CADDYFILE" | tee 3.log | sed -e 's/^/[Osse] /'
# This method of starting multiple commands was from this lovely person https://unix.stackexchange.com/a/204619 - Thanks!
