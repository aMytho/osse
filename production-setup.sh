#!/bin/bash

# This is the script to run Osse. If you are trying to develop Osse, DO NOT use this script!
# Feel free to change any of the .env variables below. If you make a change, restart osse for the changes to take affect.
# Lines with # at the beginning are comments and are not read by the shell.

# HTTPS support. Use one or the other (http or https), not both.
export OSSE_PROTOCOL=http
#export OSSE_PROTOCOL=https

# The hostname of the pc/server that is running Osse.
# For most users, this is localhost. To access the server, use http://localhost in your browser.
# If you want to access Osse from a different device, change it to your local IP. This is usually something like 192.168.0.5
# To access osse from another device, set the env and enter your local IP address in the address bar. You can access it from your server the same way. (localhost won't work anymore)
# There is a commented out example. Only one host can be active at a time.
export OSSE_HOST=localhost
# export OSSE_HOST=192.168.0.5
#export OSSE_HOST=my-app.example.com

# This is the port Osse will serve the website from. 80 is the default because it isn't required to be entered into the URL bar.
export OSSE_SERVER_PORT=80
# This port is used for the HTTPS website.
export OSSE_SERVER_PORT_SECURE=443
# This port is used for the API
export OSSE_API_PORT=9000
# This port is used for the HTTPS API.
export OSSE_API_PORT_SECURE=9001
# This port is used for websockets (Laravel Reverb) over WS or WSS
export OSSE_REVERB_PORT=9003

# Set storage path for logs and cache. The DB is also here, but you can move it with the below env variable.
export LARAVEL_STORAGE_PATH="~/.osse"
# Set the path to the database.
export DB_DATABASE="~/.osse/osse.sqlite"
# Set osse executable location. By default, it is with this shell script. If you move it, update the location.
OSSE_EXECUTABLE="./osse"

# The paths to scan for music. See examples below. Only absolute paths are supported (no ~ or env vars). Separate directories with comma.
export OSSE_DIRECTORIES=""
# export OSSE_DIRECTORIES="/home/me/Music,/mnt/server1/files"
# If true, allow new accounts to be created. Once you make your account, set this to false.
export allowRegistration=true

# Do not edit anything below this line! ------------------------------- If you made it this far, you can run the script!

# Set the envs for caddy
export OSSE_URL_SERVER="http://${OSSE_HOST}:${OSSE_SERVER_PORT}"
export OSSE_URL_SERVER_SECURE="https://${OSSE_HOST}:${OSSE_SERVER_PORT_SECURE}"
export OSSE_URL_API="http://${OSSE_HOST}:${OSSE_API_PORT}"
export OSSE_URL_API_SECURE="https://${OSSE_HOST}:${OSSE_API_PORT_SECURE}"

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
# Run migrations
"$OSSE_EXECUTABLE" php-cli artisan migrate

echo "Server will be available on $OSSE_URL_SERVER and $OSSE_URL_SERVER_SECURE (if https enabled)"

# Starts osse. We run the queue (scan jobs), Reverb (websockets), and Laravel.
trap 'kill %1; kill %2' SIGINT
"$OSSE_EXECUTABLE" php-cli artisan queue:work --tries=3 --timeout=0 | tee 1.log | sed -e 's/^/[Osse Queue] /' & "$OSSE_EXECUTABLE" php-cli artisan reverb:start | tee 2.log | sed -e 's/^/[Osse Reverb] /' & sudo -E "$OSSE_EXECUTABLE" php-server | tee 3.log | sed -e 's/^/[Osse] /'
# This method of starting multiple commands was from this lovely person https://unix.stackexchange.com/a/204619 - Thanks!
