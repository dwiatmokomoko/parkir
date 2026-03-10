#!/bin/bash

# Deployment Script untuk Parking Payment System
# Server: 100.119.174.120
# User: moko
# Path: /var/www/html/parkir

set -e

echo "=========================================="
echo "Parking Payment System - Deployment Script"
echo "=========================================="
echo ""

# Configuration
SERVER_IP="100.119.174.120"
SERVER_USER="moko"
SERVER_PORT="22"
SERVER_PATH="/var/www/html/parkir"
REMOTE_NAME="production"

echo "[1/8] Checking git status..."
git status

echo ""
echo "[2/8] Pushing to GitHub..."
git push origin main

echo ""
echo "[3/8] Connecting to server and pulling latest code..."
ssh -p $SERVER_PORT $SERVER_USER@$SERVER_IP << 'EOF'
  cd /var/www/html/parkir
  echo "Current directory: $(pwd)"
  
  # Pull latest code from GitHub
  echo "Pulling latest code from GitHub..."
  git pull origin main
  
  # Install PHP dependencies
  echo "Installing PHP dependencies..."
  composer install --no-dev --optimize-autoloader
  
  # Install Node dependencies
  echo "Installing Node dependencies..."
  npm ci
  
  # Build frontend assets
  echo "Building frontend assets..."
  npm run build
  
  # Copy environment file if not exists
  if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    php artisan key:generate
  fi
  
  # Run database migrations
  echo "Running database migrations..."
  php artisan migrate --force
  
  # Clear caches
  echo "Clearing caches..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  
  # Set permissions
  echo "Setting permissions..."
  chmod -R 775 storage bootstrap/cache
  
  # Restart services
  echo "Restarting services..."
  sudo systemctl restart php8.2-fpm
  sudo systemctl reload nginx
  
  echo "Deployment completed successfully!"
EOF

echo ""
echo "[4/8] Deployment completed!"
echo ""
echo "=========================================="
echo "Server Information:"
echo "  IP: $SERVER_IP"
echo "  User: $SERVER_USER"
echo "  Port: $SERVER_PORT"
echo "  Path: $SERVER_PATH"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Configure .env file on server with database credentials"
echo "2. Configure Midtrans API keys"
echo "3. Setup SSL certificate with Let's Encrypt"
echo "4. Configure Nginx for production"
echo "5. Setup database backups"
echo ""
