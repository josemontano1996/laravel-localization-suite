#!/bin/bash
set -e

# Ensure we are in the script's directory
cd "$(dirname "$0")"

# 0. Ensure package is up to date
if [ ! -d "vendor" ]; then
    echo "vendor directory not found. Running composer install..."
    composer install
fi

# 1. Start Sail
./vendor/bin/sail down
./vendor/bin/sail up -d

# 2. Wait for server to be ready
echo "Waiting for server to be ready..."
timeout=20
current_wait=0
while ! ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80 > /dev/null && [ $current_wait -lt $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

# 3. Clear cache
./vendor/bin/sail artisan optimize:clear

# 3. Run concurrency test
# Total and concurrency can be overridden via flags if needed,
# but we'll use sensible defaults for the matrix.
TOTAL=${1:-50}
CONCURRENCY=${2:-50}

./vendor/bin/sail php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"

# 4. Stop Sail
./vendor/bin/sail down
