#!/bin/bash
set -e

# Clear host-side artifacts
rm -f storage/logs/octane-server-state.json
rm -f bootstrap/cache/*.php

echo "Installing dependencies (no-scripts)..."
composer install --no-scripts --no-interaction

echo "Starting Sail in-memory..."
./vendor/bin/sail down -v
./vendor/bin/sail up -d --build

# Crucial: Octane needs its binaries synced
./vendor/bin/sail artisan octane:install --server=swoole --force

echo "Optimizing application for concurrency test..."
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

./vendor/bin/sail down
