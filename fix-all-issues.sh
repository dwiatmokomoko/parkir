#!/bin/bash

echo "==================================="
echo "Fixing Laravel Issues"
echo "==================================="

cd /var/www/html/parkir

# Fix permissions more aggressively
echo "[1/5] Fixing directory permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/logs

# Create log file manually
echo "[2/5] Creating log file..."
sudo touch storage/logs/laravel-2026-03-11.log
sudo chown www-data:www-data storage/logs/laravel-2026-03-11.log
sudo chmod 666 storage/logs/laravel-2026-03-11.log

# Install Redis PHP extension
echo "[3/5] Installing Redis PHP extension..."
sudo apt-get update
sudo apt-get install -y php8.3-redis redis-server

# Restart PHP-FPM
echo "[4/5] Restarting PHP-FPM..."
sudo systemctl restart php8.3-fpm

# Clear all caches
echo "[5/5] Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "==================================="
echo "Done! Now test with: php artisan"
echo "==================================="
