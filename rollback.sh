#!/bin/bash

# Parking Payment System Rollback Script
# This script rolls back to a previous deployment

set -e

# Configuration
DEPLOY_DIR="/var/www/parking-payment-system"
BACKUP_DIR="/var/backups/parking-payment-system"
LOG_FILE="/var/log/parking-payment-system/rollback.log"

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

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

log "Starting rollback process..."

# List available backups
log "Available backups:"
ls -lh "$BACKUP_DIR"/backup_*.tar.gz | tail -5

# Ask user to select backup
echo "Enter the backup filename to restore (e.g., backup_2024-01-15_10-30-45.tar.gz):"
read -r BACKUP_FILE

if [ ! -f "$BACKUP_DIR/$BACKUP_FILE" ]; then
    error_exit "Backup file not found: $BACKUP_DIR/$BACKUP_FILE"
fi

log "Restoring from backup: $BACKUP_FILE"

# Step 1: Stop services
log "Step 1: Stopping services..."
systemctl stop php8.2-fpm || warning "Failed to stop PHP-FPM"
supervisorctl stop parking-payment-system:* || warning "Failed to stop queue workers"
success "Services stopped"

# Step 2: Backup current state
log "Step 2: Backing up current state..."
CURRENT_BACKUP="$BACKUP_DIR/current_state_$(date +%Y-%m-%d_%H-%M-%S).tar.gz"
tar -czf "$CURRENT_BACKUP" -C "$(dirname "$DEPLOY_DIR")" "$(basename "$DEPLOY_DIR")" || warning "Failed to backup current state"
success "Current state backed up to: $CURRENT_BACKUP"

# Step 3: Restore from backup
log "Step 3: Restoring from backup..."
cd "$(dirname "$DEPLOY_DIR")"
rm -rf "$(basename "$DEPLOY_DIR")"
tar -xzf "$BACKUP_DIR/$BACKUP_FILE" || error_exit "Failed to extract backup"
success "Backup restored"

# Step 4: Restart services
log "Step 4: Restarting services..."
systemctl start php8.2-fpm || error_exit "Failed to start PHP-FPM"
systemctl reload nginx || error_exit "Failed to reload Nginx"
supervisorctl start parking-payment-system:* || warning "Failed to start queue workers"
success "Services restarted"

log "Rollback completed successfully!"
success "Application has been rolled back!"

exit 0
