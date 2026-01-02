#!/usr/bin/env bash
echo -e "\n🎵 Osse Music Server\n"

if [ "$EUID" -ne 0 ]; then
  echo "🔐 Osse needs elevated privileges to bind to ports 80/443."
  echo "You may be prompted for your password."
  sudo -v
fi

if [ ! -f .env ]; then
    echo "❌ Missing .env file"
    exit 1
fi

set -a
source .env
set +a

require() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "❌ $1 is required"
            echo "👉 $2"
            exit 1
        }
}

require frankenphp "https://frankenphp.dev/docs/#getting-started"
require php "https://www.php.net/downloads"
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
}

start_broadcast() {
    echo "📡 Starting broadcast server"
    (cd osse-broadcast && go run .) &
    PIDS+=($!)
}

start_frontend_dev() {
    echo "🌐 Starting frontend dev server"
    # (cd osse-web && $PNPM run start) &
    # PIDS+=($!)
}

build_frontend() {
    echo "🏗️  Building frontend"
    (cd osse-web && $PNPM run build)
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

case "$1" in
    dev)
        generate_caddy
        start_broadcast
        start_frontend_dev
        start_frankenphp
        wait
        ;;
    run)
        generate_caddy
        start_broadcast
        start_frankenphp
        wait
        ;;
    build)
        build_frontend
        build_broadcast
        ;;
    *)
        echo "Usage: ./osse {dev|run|build}"
        exit 1
        ;;
esac

