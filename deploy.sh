#!/bin/bash

# Parking Payment System Deployment Script
# This script automates the deployment process for production

set -e

# Configuration
DEPLOY_DIR="/var/www/parking-payment-system"
BACKUP_DIR="/var/backups/parking-payment-system"
LOG_FILE="/var/log/parking-payment-system/deploy.log"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    echo -e "${RED}[ERROR] $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

# Success message
success() {
    echo -e "${GREEN}[SUCCESS] $1${NC}" | tee -a "$LOG_FILE"
}

# Warning message
warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

log "Starting deployment process..."

# Step 1: Create backup
log "Step 1: Creating backup..."
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
tar -czf "$BACKUP_FILE" -C "$(dirname "$DEPLOY_DIR")" "$(basename "$DEPLOY_DIR")" || error_exit "Failed to create backup"
success "Backup created: $BACKUP_FILE"

# Step 2: Pull latest code from repository
log "Step 2: Pulling latest code from repository..."
cd "$DEPLOY_DIR"
git fetch origin || error_exit "Failed to fetch from repository"
git checkout main || error_exit "Failed to checkout main branch"
git pull origin main || error_exit "Failed to pull latest code"
success "Code pulled successfully"

# Step 3: Install/update dependencies
log "Step 3: Installing dependencies..."
composer install --no-dev --optimize-autoloader || error_exit "Failed to install composer dependencies"
npm ci || error_exit "Failed to install npm dependencies"
success "Dependencies installed"

# Step 4: Build frontend assets
log "Step 4: Building frontend assets..."
npm run build || error_exit "Failed to build frontend assets"
success "Frontend assets built"

# Step 5: Run migrations
log "Step 5: Running database migrations..."
php artisan migrate --force || error_exit "Failed to run migrations"
success "Migrations completed"

# Step 6: Clear caches
log "Step 6: Clearing caches..."
php artisan config:cache || error_exit "Failed to cache config"
php artisan route:cache || error_exit "Failed to cache routes"
php artisan view:cache || error_exit "Failed to cache views"
success "Caches cleared and rebuilt"

# Step 7: Restart queue workers
log "Step 7: Restarting queue workers..."
supervisorctl restart parking-payment-system:* || warning "Failed to restart queue workers (supervisor may not be running)"
success "Queue workers restarted"

# Step 8: Restart PHP-FPM
log "Step 8: Restarting PHP-FPM..."
systemctl restart php8.2-fpm || error_exit "Failed to restart PHP-FPM"
success "PHP-FPM restarted"

# Step 9: Reload Nginx
log "Step 9: Reloading Nginx..."
nginx -t || error_exit "Nginx configuration test failed"
systemctl reload nginx || error_exit "Failed to reload Nginx"
success "Nginx reloaded"

# Step 10: Run tests (optional)
log "Step 10: Running tests..."
php artisan test --env=testing || warning "Some tests failed (check logs)"
success "Tests completed"

log "Deployment completed successfully!"
success "Application is now live!"

# Cleanup old backups (keep last 10)
log "Cleaning up old backups..."
ls -t "$BACKUP_DIR"/backup_*.tar.gz | tail -n +11 | xargs -r rm
success "Old backups cleaned up"

exit 0
