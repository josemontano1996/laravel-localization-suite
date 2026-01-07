#!/bin/bash
set -e

# run-native-test.sh
# Automates the setup and execution of the native driver concurrency test.

echo "--------------------------------------------------"
echo "Setting up environment for NATIVE driver test..."
echo "--------------------------------------------------"

# Pre-check Docker
if ! docker info > /dev/null 2>&1; then
    echo "--------------------------------------------------"
    echo "ERROR: Docker is not running or not accessible."
    echo "Please ensure Docker Desktop is started and the background service is running."
    echo "--------------------------------------------------"
    exit 1
fi

# 1. Setup environment
# Backup .env if it exists
if [ -f .env ]; then
    cp .env .env.bak
fi

# Export Sail defaults for direct Docker Compose usage
export WWWGROUP=${WWWGROUP:-1000}
export WWWUSER=${WWWUSER:-1000}

# Ensure LOCALIZATION_DRIVER is set to native
if grep -q "LOCALIZATION_DRIVER=" .env; then
    sed -i 's/LOCALIZATION_DRIVER=.*/LOCALIZATION_DRIVER=native/' .env
else
    echo "LOCALIZATION_DRIVER=native" >> .env
fi

# Ensure supported_locales are set in config/app.php
if ! grep -q "'supported_locales'" config/app.php; then
    sed -i "/'fallback_locale' =>/a \    'supported_locales' => ['en', 'es', 'fr']," config/app.php
fi

# 2. Start/Restart containers
echo "Restarting Docker containers..."
MSYS_NO_PATHCONV=1 docker compose down --remove-orphans
if ! MSYS_NO_PATHCONV=1 docker compose up -d; then
    echo "--------------------------------------------------"
    echo "ERROR: Failed to start Docker containers."
    echo "Check if port 80 is already in use by another application."
    echo "--------------------------------------------------"
    exit 1
fi

# 3. Wait for the server to be ready and clear cache
echo "Waiting for Sail containers to be ready..."
sleep 5
echo "Clearing Laravel cache..."
MSYS_NO_PATHCONV=1 docker compose exec laravel.test php artisan optimize:clear

# 4. Run the high-concurrency test inside container
echo "Launching concurrency test inside container..."
if ! MSYS_NO_PATHCONV=1 docker compose exec laravel.test php concurrent_bleedtest.php; then
    echo "--------------------------------------------------"
    echo "ERROR: Test execution failed."
    echo "--------------------------------------------------"
fi

# 5. Cleanup
echo "--------------------------------------------------"
echo "Cleaning up..."
docker compose down

# Restore .env backup if it existed
if [ -f .env.bak ]; then
    mv .env.bak .env
fi

echo "Test complete."
echo "--------------------------------------------------"
