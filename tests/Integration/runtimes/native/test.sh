#!/bin/bash
set -e

# Ensure we are in the script's directory
cd "$(dirname "$0")"

# 0. Ensure package is up to date
if [ ! -d "vendor" ]; then
    echo "vendor directory not found. Running composer install..."
    composer install
fi

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

# 1. Start Sail
./vendor/bin/sail down
./vendor/bin/sail build --no-cache && ./vendor/bin/sail up -d

# 2. Wait for server to be ready
echo "Waiting for server to be ready..."
timeout=20
current_wait=0
while ! ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80 > /dev/null && [ $current_wait -lt $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

# Generate application key (if missing) and run migrations before clearing caches
./vendor/bin/sail artisan key:generate --force || true
./vendor/bin/sail artisan migrate --force || true

./vendor/bin/sail artisan optimize:clear

# 3. Run concurrency test
# Total and concurrency can be overridden via flags if needed,
# but we'll use sensible defaults for the matrix.
TOTAL=${1:-50}
CONCURRENCY=${2:-50}

./vendor/bin/sail php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"

# 4. Stop Sail
./vendor/bin/sail down
