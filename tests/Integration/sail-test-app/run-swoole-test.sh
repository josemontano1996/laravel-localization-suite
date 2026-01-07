#!/bin/bash
set -e

# run-swoole-test.sh
# Automates the setup and execution of the SWOOLE driver concurrency test using Octane/Swoole in WSL.

START_TIME=$SECONDS

# Default values
TOTAL_REQUESTS=100
CONCURRENCY=50

# Parse flags
while getopts "t:c:" opt; do
  case $opt in
    t) TOTAL_REQUESTS="$OPTARG" ;;
    c) CONCURRENCY="$OPTARG" ;;
    *) echo "Usage: $0 [-t total_requests] [-c concurrency]" >&2; exit 1 ;;
  esac
done

echo "--------------------------------------------------"
echo "Setting up environment for SWOOLE driver test (Octane/Swoole)..."
echo "--------------------------------------------------"

# Pre-check Docker
if ! docker info > /dev/null 2>&1; then
    echo "--------------------------------------------------"
    echo "ERROR: Docker is not running or not accessible."
    echo "Please ensure Docker Desktop is started and integrated with WSL."
    echo "--------------------------------------------------"
    exit 1
fi

# 1. Setup environment
# Backup .env and config/octane.php if they exist
if [ -f .env ]; then
    cp .env .env.bak
fi
if [ -f config/octane.php ]; then
    cp config/octane.php config/octane.php.bak
fi

# Cleanup function to be called on exit
cleanup() {
    echo "--------------------------------------------------"
    echo "Cleaning up..."
    # Clear any SAIL_COMMAND to avoid affecting other runs
    if [ -f .env ]; then
        sed -i '/SAIL_COMMAND=/d' .env
    fi
    ./vendor/bin/sail down

    # Restore backups
    if [ -f .env.bak ]; then
        mv .env.bak .env
    fi
    if [ -f config/octane.php.bak ]; then
        mv config/octane.php.bak config/octane.php
    fi
    echo "Done."
    echo "--------------------------------------------------"
}

# Set trap to ensure cleanup runs even on error
trap cleanup EXIT

# Set driver to swoole
if grep -q "LOCALIZATION_DRIVER=" .env; then
    sed -i 's/LOCALIZATION_DRIVER=.*/LOCALIZATION_DRIVER=swoole/' .env
else
    echo "LOCALIZATION_DRIVER=swoole" >> .env
fi

# Ensure supported_locales are set in config/app.php
if ! grep -q "'supported_locales'" config/app.php; then
    sed -i "/'fallback_locale' =>/a \    'supported_locales' => ['en', 'es', 'fr']," config/app.php
fi

# 2. Start containers
echo "Starting Laravel Sail..."
./vendor/bin/sail down --remove-orphans
./vendor/bin/sail up -d

# 3. Install/Configure Octane for Swoole
echo "Ensuring Octane/Swoole are configured..."
./vendor/bin/sail artisan octane:install --server=swoole --no-interaction

# Inject Swoole hook flags into config/octane.php if not present
if ! grep -q "'hook_flags' => SWOOLE_HOOK_ALL" config/octane.php; then
    echo "Enabling all Swoole hooks in config/octane.php..."
    sed -i "/return \[/a \    'swoole' => [\n        'options' => [\n            'hook_flags' => SWOOLE_HOOK_ALL,\n        ],\n    ]," config/octane.php
fi

# 4. Restart with Octane enabled (Swoole)
echo "Configuring Octane in .env..."
sed -i '/SAIL_COMMAND=/d' .env
# Enable Swoole with 1 worker to strictly test state isolation within a single process.
echo 'SAIL_COMMAND="php artisan octane:start --server=swoole --host=0.0.0.0 --port=80 --workers=1"' >> .env

echo "Restarting Sail with Octane (Swoole)..."
./vendor/bin/sail down
./vendor/bin/sail up -d

echo "Waiting for Octane to be ready (20s)..."
current_wait=0
timeout=20
while ! ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80 > /dev/null && [ $current_wait -lt $timeout ]; do
    echo "Wait for port 80... ($current_wait/$timeout)"
    sleep 2
    current_wait=$((current_wait + 2))
done

# Verify Swoole environment
if ! ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80 | grep -iq "swoole"; then
    echo "Checking server response headers..."
    ./vendor/bin/sail exec laravel.test curl -s -I http://localhost:80
fi

echo "Clearing Laravel cache..."
./vendor/bin/sail artisan optimize:clear

# 5. Run the high-concurrency test inside container
echo "Launching concurrency test (Swoole + Octane) ($TOTAL_REQUESTS requests, $CONCURRENCY concurrency)..."
if ! ./vendor/bin/sail php concurrent_bleedtest.php -t "$TOTAL_REQUESTS" -c "$CONCURRENCY"; then
    echo "--------------------------------------------------"
    echo "ERROR: Test execution failed."
    echo "Dumping container logs for diagnosis:"
    ./vendor/bin/sail logs --tail=50 laravel.test
    echo "--------------------------------------------------"
fi

echo "Test complete."
END_TIME=$SECONDS
DURATION=$((END_TIME - START_TIME))
echo "Total script duration: ${DURATION}s"
echo "--------------------------------------------------"
