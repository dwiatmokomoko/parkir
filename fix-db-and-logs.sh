#!/bin/bash

echo "==================================="
echo "Fixing Database SSL and Log Permissions"
echo "==================================="

cd /var/www/html/parkir

# Fix log permissions
echo "[1/4] Fixing log file permissions..."
sudo chmod 777 storage/logs
sudo chmod 666 storage/logs/*.log 2>/dev/null || true
sudo chown -R www-data:www-data storage/logs

# Update .env for correct DB settings
echo "[2/4] Updating database configuration..."
sed -i 's/DB_SSLMODE=disable/DB_SSLMODE=prefer/' .env

# Clear config cache
echo "[3/4] Clearing configuration cache..."
php artisan config:clear

# Test database connection
echo "[4/4] Testing database connection..."
php artisan migrate:status

echo ""
echo "==================================="
echo "Done! Try migration again"
echo "==================================="
