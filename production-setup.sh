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

# Set storage paths and DB location.
export LARAVEL_STORAGE_PATH="~/.osse/storage"
export DB_DATABASE="~/.osse/osse.sqlite"

# Do not edit anything below this line! ----------------------------------------------------------------
export OSSE_URL_SERVER="http://${OSSE_HOST}:${OSSE_SERVER_PORT}"
export OSSE_URL_SERVER_SECURE="https://${OSSE_HOST}:${OSSE_SERVER_PORT_SECURE}"
export OSSE_URL_API="http://${OSSE_HOST}:${OSSE_API_PORT}"
export OSSE_URL_API_SECURE="https://${OSSE_HOST}:${OSSE_API_PORT_SECURE}"

echo 'Server URLs'
echo $OSSE_URL_API \n $OSSE_URL_API_SECURE \n $OSSE_URL_SERVER \n $OSSE_URL_SERVER_SECURE

# Loads the new env variables
frankenphp php-cli artisan config:cache

# Starts osse. We run the queue (scan jobs), Reverb (websockets), and Laravel.
trap 'kill %1; kill %2' SIGINT
frankenphp php-cli artisan queue:work --tries=3 --timeout=0 | tee 1.log | sed -e 's/^/[Osse Queue] /' & frankenphp php-cli artisan reverb:start | tee 2.log | sed -e 's/^/[Osse Reverb] /' & sudo -E frankenphp run | tee 3.log | sed -e 's/^/[Osse] /'
# This method of starting multiple commands was from this lovely person https://unix.stackexchange.com/a/204619 - Thanks!
