#!/bin/bash
set -e

# Clear host-side artifacts
rm -f storage/logs/octane-server-state.json
rm -f bootstrap/cache/*.php

echo "Updating dependencies (no-scripts)..."
composer update --no-scripts --no-interaction --ignore-platform-req=ext-openswoole

# Ensure .env exists
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi


# Ensure sqlite database file exists and is writable inside the runtime folder
mkdir -p database
if [ ! -f database/database.sqlite ]; then
    echo "Creating database/database.sqlite"
    touch database/database.sqlite
fi
chmod 0666 database/database.sqlite || true

echo "Starting Sail in-memory..."
./vendor/bin/sail down
./vendor/bin/sail build && ./vendor/bin/sail up -d

# Crucial: Octane needs its binaries synced
./vendor/bin/sail artisan octane:install --server=swoole

echo "Optimizing application for concurrency test..."
./vendor/bin/sail artisan key:generate --force || true
./vendor/bin/sail artisan migrate --force || true

./vendor/bin/sail artisan optimize:clear

./vendor/bin/sail artisan optimize
./vendor/bin/sail artisan event:cache

echo "Waiting for Octane..."
timeout=30
current_wait=0
while ! curl -s -I http://localhost > /dev/null && [ $current_wait -lt $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
    # If we are failing, show the last few lines of the log to see WHY it's null
    if [ $((current_wait % 10)) -eq 0 ]; then
        ./vendor/bin/sail logs --tail=5
    fi
done

echo "Running Concurrency Test (Array Mode)..."
./vendor/bin/sail php concurrent_bleedtest.php -t 200 -c 50

rm .env

