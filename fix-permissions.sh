#!/bin/bash

# Fix Laravel storage and cache permissions
echo "Fixing Laravel storage and cache permissions..."

# Navigate to project directory
cd /var/www/html/parkir

# Create necessary directories if they don't exist
sudo mkdir -p storage/logs
sudo mkdir -p storage/framework/cache
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/framework/views
sudo mkdir -p bootstrap/cache

# Set ownership to web server user
sudo chown -R www-data:www-data storage bootstrap/cache

# Set proper permissions
sudo chmod -R 775 storage bootstrap/cache

# Ensure the current user can also write
sudo usermod -a -G www-data moko

echo "Permissions fixed!"
echo ""
echo "Now run these commands:"
echo "1. composer install"
echo "2. php artisan config:clear"
echo "3. php artisan cache:clear"
echo "4. php artisan view:clear"
