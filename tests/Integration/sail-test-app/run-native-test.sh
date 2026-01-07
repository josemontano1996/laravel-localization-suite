#!/bin/bash
set -e

# run-native-test.sh
# Automates the setup and execution of the native driver concurrency test in WSL.

echo "--------------------------------------------------"
echo "Setting up environment for NATIVE driver test..."
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
# Backup .env if it exists
if [ -f .env ]; then
    cp .env .env.bak
fi

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
echo "Restarting Laravel Sail..."
./vendor/bin/sail down --remove-orphans
./vendor/bin/sail up -d

# 3. Wait for the server to be ready and clear cache
echo "Waiting for Sail containers to be ready..."
sleep 5
echo "Clearing Laravel cache..."
./vendor/bin/sail artisan optimize:clear

# 4. Run the high-concurrency test inside container
echo "Launching concurrency test inside container..."
if ! ./vendor/bin/sail php concurrent_bleedtest.php; then
    echo "--------------------------------------------------"
    echo "ERROR: Test execution failed."
    echo "--------------------------------------------------"
fi

# 5. Cleanup
echo "--------------------------------------------------"
echo "Cleaning up..."
./vendor/bin/sail down

# Restore .env backup if it existed
if [ -f .env.bak ]; then
    mv .env.bak .env
fi

echo "Test complete."
echo "--------------------------------------------------"
