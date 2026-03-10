#!/bin/bash

# Database Backup Script for Parking Payment System
# This script creates automated PostgreSQL backups

set -e

# Configuration
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:-parking_payment_db}"
DB_USER="${DB_USER:-postgres}"
BACKUP_DIR="/var/backups/parking-payment-system/database"
RETENTION_DAYS=30
LOG_FILE="/var/log/parking-payment-system/backup.log"
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

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

log "Starting database backup..."

# Create backup file
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sql.gz"

# Perform backup
log "Backing up database: $DB_NAME"
PGPASSWORD="$DB_PASSWORD" pg_dump \
    -h "$DB_HOST" \
    -p "$DB_PORT" \
    -U "$DB_USER" \
    -d "$DB_NAME" \
    --verbose \
    --no-password \
    --format=plain | gzip > "$BACKUP_FILE" || error_exit "Failed to backup database"

success "Database backup completed: $BACKUP_FILE"

# Verify backup
log "Verifying backup..."
if [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    success "Backup verified. Size: $BACKUP_SIZE"
else
    error_exit "Backup verification failed"
fi

# Encrypt backup (optional)
if command -v gpg &> /dev/null; then
    log "Encrypting backup..."
    gpg --symmetric --cipher-algo AES256 "$BACKUP_FILE" || warning "Failed to encrypt backup"
    rm "$BACKUP_FILE"
    BACKUP_FILE="$BACKUP_FILE.gpg"
    success "Backup encrypted"
fi

# Upload to S3 (optional)
if command -v aws &> /dev/null; then
    log "Uploading backup to S3..."
    aws s3 cp "$BACKUP_FILE" "s3://parking-payment-backups/database/" || warning "Failed to upload to S3"
    success "Backup uploaded to S3"
fi

# Cleanup old backups
log "Cleaning up old backups (older than $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "backup_*.sql.gz*" -mtime +$RETENTION_DAYS -delete
success "Old backups cleaned up"

log "Database backup process completed successfully!"

exit 0
