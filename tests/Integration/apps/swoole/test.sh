#!/bin/bash
set -e

# Ensure we are in the script's directory
cd "$(dirname "$0")"

# 0. Ensure package is up to date
if [ ! -d "vendor" ]; then
    echo "vendor directory not found. Running composer install..."
    composer install
else
    echo "Updating local package to latest version..."
    composer update josemontano1996/laravel-localization-suite
fi

# 1. Start Sail
./vendor/bin/sail up -d

# 2. Ensure Octane is installed
./vendor/bin/sail artisan octane:install --server=swoole --no-interaction

# 3. Clear cache
./vendor/bin/sail artisan optimize:clear

# 4. Wait for Octane (Swoole) to be ready
echo "Waiting for Octane (Swoole) to be ready..."
timeout=20
current_wait=0
while ! ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80 > /dev/null && [ $current_wait -lt $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

# 5. Run concurrency test
TOTAL=${1:-100}
CONCURRENCY=${2:-50}
./vendor/bin/sail php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"

# 6. Stop Sail
./vendor/bin/sail down
