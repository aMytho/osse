#!/usr/bin/env bash
echo -e "\n🎵 Osse Music Server\n"

if [ "$EUID" -ne 0 ]; then
  echo "🔐 Osse needs elevated privileges to bind to ports 80/443."
  echo "You may be prompted for your (sudo) password."
  sudo -v
fi

if [ ! -f .env ]; then
    echo "❌ Missing .env file. A file will be copied from the example for you."
    cp .env.example .env
    echo "File copied!"
fi

set -a
source .env
set +a

require() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "❌ $1 is required"
            echo "👉 You can install it here: $2"
            exit 1
        }
}

require frankenphp "https://frankenphp.dev/docs/#getting-started"
require node "https://nodejs.org"
require go "https://go.dev/dl"

PNPM=$(command -v pnpm || command -v npm)
[ -z "$PNPM" ] && echo "❌ pnpm or npm required" && exit 1

PIDS=()

cleanup() {
    echo "🛑 Stopping Osse"
    for pid in "${PIDS[@]}"; do
        kill "$pid" 2>/dev/null || true
    done
}
trap cleanup EXIT INT TERM

generate_caddy() {
    echo "🧩 Generating Caddyfile"
    rm -f Caddyfile
    envsubst < deployment/Caddyfile.template > Caddyfile

    # Use angular dev server in dev, use build files in prod.
    if [ "$OSSE_ENV" = "dev" ]; then
        REPLACE="reverse_proxy http://localhost:4200"
    else
        REPLACE="try_files {path} /index.html\nfile_server"
    fi
    sed -i "s+WEB_FRONTEND_TEMPLATE+$REPLACE+" Caddyfile
}

start_broadcast() {
    echo "📡 Starting broadcast server"
    (cd osse-broadcast && go run .) &
    PIDS+=($!)
}

start_frontend_dev() {
    echo "🌐 Starting frontend dev server"
    (cd osse-web && $PNPM run start) &
    PIDS+=($!)
}

copy_api_env() {
    echo "🏗️  Building API"

    # If the file exists, get the encryption key to add to the new file
    OSSE_ENCRYPTION_KEY=""
    if [ -e osse-core/.env ]; then
        echo "ENV Exists. Copying APP_KEY to new file..."
        OSSE_ENCRYPTION_KEY="$(grep '^APP_KEY' osse-core/.env)"
    fi

    # Remove the old file if one exists
    rm -f osse-core/.env

    # Copy the example .env
    cp osse-core/.env.example osse-core/.env

    # Replace the app key
    if [ -z "$OSSE_ENCRYPTION_KEY" ]; then
        (cd osse-core && frankenphp php-cli artisan key:generate)
    else
        sed -i "s+APP_KEY.*+$OSSE_ENCRYPTION_KEY+" osse-core/.env
        sed -i "s+APP_ENV.*+APP_ENV=$OSSE_ENV+" osse-core/.env
    fi

    # Add user vars to end of api env
    echo -e "OSSE_DIRECTORIES=\"$OSSE_DIRECTORIES\"\nALLOW_REGISTRATION=\"$OSSE_ALLOW_REGISTRATION\"" >> osse-core/.env
    echo "Osse .env file generated"
}

optimize_api() {
    (cd osse-core && frankenphp php-cli artisan config:cache)
    (cd osse-core && frankenphp php-cli artisan config:optimize)
}

run_api_migrations() {
    (cd osse-core && frankenphp php-cli artisan migrate)
}

build_frontend() {
    echo "🏗️  Building frontend"
    (cd osse-web && $PNPM i && $PNPM run build)
}

build_broadcast() {
    echo '🏗️ Building broadcast server'
    (cd osse-broadcast && go mod tidy && go build -o bin/osse-broadcast)
    echo 'Finished building broadcast server'
}

start_frankenphp() {
    echo "🚀 Starting Frankenphp (Web Server)"
    sudo frankenphp run --config Caddyfile &
    PIDS+=($!)
}

run_broadcast() {
    echo "📡 Starting broadcast server"
    (cd osse-broadcast && ./bin/osse-broadcast) &
    PIDS+=($!)
}

case "$1" in
    dev)
        copy_api_env
        generate_caddy
        start_broadcast
        start_frontend_dev
        start_frankenphp
        wait
        ;;
    run)
        run_api_migrations
        optimize_api
        generate_caddy
        run_broadcast
        start_frankenphp
        wait
        ;;
    build)
        run_api_migrations
        build_frontend
        build_broadcast
        copy_api_env
        ;;
    # Access php cli
    php-cli)
        cd osse-core && frankenphp php-cli "${@:2}"
    ;;
    *)
        echo "Usage: ./osse {dev|run|build}"
        exit 1
        ;;
esac

