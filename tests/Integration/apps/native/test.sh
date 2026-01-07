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

# 2. Clear cache
./vendor/bin/sail artisan optimize:clear

# 3. Run concurrency test
# Total and concurrency can be overridden via flags if needed, 
# but we'll use sensible defaults for the matrix.
TOTAL=${1:-100}
CONCURRENCY=${2:-50}

./vendor/bin/sail php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"

# 4. Stop Sail
./vendor/bin/sail down
