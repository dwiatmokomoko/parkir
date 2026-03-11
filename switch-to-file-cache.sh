#!/bin/bash

echo "==================================="
echo "Switching to File-Based Cache"
echo "==================================="

cd /var/www/html/parkir

# Backup current .env
echo "[1/4] Backing up .env..."
cp .env .env.backup

# Switch to file-based cache
echo "[2/4] Updating .env configuration..."
sed -i 's/CACHE_DRIVER=redis/CACHE_DRIVER=file/' .env
sed -i 's/SESSION_DRIVER=redis/SESSION_DRIVER=file/' .env
sed -i 's/QUEUE_CONNECTION=redis/QUEUE_CONNECTION=database/' .env

# Clear all caches
echo "[3/4] Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Test artisan
echo "[4/4] Testing artisan..."
php artisan --version

echo ""
echo "==================================="
echo "Done! File-based cache is active"
echo "==================================="
