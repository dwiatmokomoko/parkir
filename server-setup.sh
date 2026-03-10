#!/bin/bash

# Server Setup Script untuk Parking Payment System
# Jalankan script ini di server untuk setup awal

set -e

echo "=========================================="
echo "Parking Payment System - Server Setup"
echo "=========================================="
echo ""

# Update system
echo "[1/10] Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
echo "[2/10] Installing required packages..."
sudo apt install -y \
    php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
    postgresql postgresql-contrib redis-server nginx supervisor \
    git curl wget certbot python3-certbot-nginx

# Install Composer
echo "[3/10] Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
echo "[4/10] Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Create application directory
echo "[5/10] Creating application directory..."
sudo mkdir -p /var/www/html/parkir
sudo chown -R $USER:$USER /var/www/html/parkir

# Clone repository
echo "[6/10] Cloning repository..."
cd /var/www/html/parkir
git clone https://github.com/dwiatmokomoko/parkir.git .

# Install dependencies
echo "[7/10] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "[8/10] Installing Node dependencies..."
npm ci

# Build frontend
echo "[9/10] Building frontend assets..."
npm run build

# Setup environment
echo "[10/10] Setting up environment..."
cp .env.example .env
php artisan key:generate

# Set permissions
chmod -R 775 storage bootstrap/cache

echo ""
echo "=========================================="
echo "Server setup completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Configure .env file with database credentials"
echo "2. Configure Midtrans API keys"
echo "3. Run: php artisan migrate"
echo "4. Run: php artisan db:seed"
echo "5. Setup Nginx configuration"
echo "6. Setup SSL certificate"
echo ""
