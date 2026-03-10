# Deployment Guide - Parking Payment System

## Overview

This guide provides step-by-step instructions for deploying the Parking Payment System to a production environment.

## Prerequisites

- Ubuntu 20.04 LTS or later
- PHP 8.2 with FPM
- PostgreSQL 15+
- Redis 6+
- Nginx
- Composer
- Node.js 18+
- Supervisor (for queue workers)
- Git

## System Requirements

### Server Specifications

- **CPU**: 4 cores minimum
- **RAM**: 8GB minimum
- **Storage**: 100GB SSD minimum
- **Bandwidth**: 100 Mbps minimum

### Software Versions

- PHP: 8.2+
- PostgreSQL: 15+
- Redis: 6+
- Nginx: 1.18+
- Node.js: 18+

## Installation Steps

### 1. Server Setup

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
    postgresql postgresql-contrib redis-server nginx supervisor \
    git curl wget certbot python3-certbot-nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Database Setup

```bash
# Create database user
sudo -u postgres psql -c "CREATE USER parking_user WITH PASSWORD 'secure_password_here';"

# Create database
sudo -u postgres psql -c "CREATE DATABASE parking_payment_db OWNER parking_user;"

# Enable required extensions
sudo -u postgres psql -d parking_payment_db -c "CREATE EXTENSION IF NOT EXISTS uuid-ossp;"
sudo -u postgres psql -d parking_payment_db -c "CREATE EXTENSION IF NOT EXISTS pg_trgm;"

# Configure PostgreSQL for SSL
# Edit /etc/postgresql/15/main/postgresql.conf
# Set: ssl = on
# Set: ssl_cert_file = '/etc/ssl/certs/ssl-cert-snakeoil.pem'
# Set: ssl_key_file = '/etc/ssl/private/ssl-cert-snakeoil.key'

sudo systemctl restart postgresql
```

### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/parking-payment-system
sudo chown -R www-data:www-data /var/www/parking-payment-system

# Clone repository
cd /var/www/parking-payment-system
sudo -u www-data git clone https://github.com/your-org/parking-payment-system.git .

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci

# Copy environment file
sudo -u www-data cp .env.production .env

# Generate application key
sudo -u www-data php artisan key:generate

# Build frontend assets
sudo -u www-data npm run build

# Run migrations
sudo -u www-data php artisan migrate --force

# Create storage directories
sudo -u www-data mkdir -p storage/app/reports
sudo -u www-data mkdir -p storage/logs
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Web Server Configuration

```bash
# Copy Nginx configuration
sudo cp nginx.conf /etc/nginx/sites-available/parking-payment-system
sudo ln -s /etc/nginx/sites-available/parking-payment-system /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Enable SSL with Let's Encrypt
sudo certbot certonly --nginx -d parkir.dishub.go.id

# Reload Nginx
sudo systemctl reload nginx
```

### 5. Queue Workers Setup

```bash
# Copy supervisor configuration
sudo cp supervisor.conf /etc/supervisor/conf.d/parking-payment-system.conf

# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start parking-payment-system:*
```

### 6. Backup Configuration

```bash
# Create backup directory
sudo mkdir -p /var/backups/parking-payment-system/database
sudo chown -R www-data:www-data /var/backups/parking-payment-system

# Copy backup script
sudo cp backup-database.sh /usr/local/bin/
sudo chmod +x /usr/local/bin/backup-database.sh

# Add cron job for daily backups
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-database.sh
```

### 7. Logging Configuration

```bash
# Create log directory
sudo mkdir -p /var/log/parking-payment-system
sudo chown -R www-data:www-data /var/log/parking-payment-system

# Configure log rotation
sudo tee /etc/logrotate.d/parking-payment-system > /dev/null <<EOF
/var/log/parking-payment-system/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.2-fpm > /dev/null 2>&1 || true
    endscript
}
EOF
```

## Deployment Process

### Automated Deployment

```bash
# Make deploy script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### Manual Deployment

```bash
# Pull latest code
cd /var/www/parking-payment-system
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
sudo supervisorctl restart parking-payment-system:*
```

## Rollback Procedure

```bash
# Make rollback script executable
chmod +x rollback.sh

# Run rollback
./rollback.sh
```

## Monitoring and Maintenance

### Health Checks

```bash
# Check application status
curl https://parkir.dishub.go.id/api/health

# Check queue workers
sudo supervisorctl status parking-payment-system:*

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()
```

### Performance Optimization

```bash
# Enable OPcache
# Edit /etc/php/8.2/fpm/conf.d/10-opcache.ini
# Set: opcache.enable=1
# Set: opcache.memory_consumption=256

# Enable Redis caching
# Already configured in .env.production

# Optimize database
sudo -u postgres psql -d parking_payment_db -c "VACUUM ANALYZE;"
```

### Backup Verification

```bash
# List backups
ls -lh /var/backups/parking-payment-system/database/

# Test restore (on test server)
gunzip -c backup_file.sql.gz | psql -U parking_user -d parking_payment_db_test
```

## Security Hardening

### SSL/TLS Configuration

```bash
# Update SSL certificates
sudo certbot renew --force-renewal

# Test SSL configuration
curl -I https://parkir.dishub.go.id
```

### Firewall Configuration

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow PostgreSQL (internal only)
sudo ufw allow from 127.0.0.1 to any port 5432
```

### Database Security

```bash
# Restrict PostgreSQL access
# Edit /etc/postgresql/15/main/pg_hba.conf
# Ensure only local connections are allowed

# Create read-only user for backups
sudo -u postgres psql -c "CREATE USER backup_user WITH PASSWORD 'backup_password';"
sudo -u postgres psql -c "GRANT CONNECT ON DATABASE parking_payment_db TO backup_user;"
sudo -u postgres psql -d parking_payment_db -c "GRANT USAGE ON SCHEMA public TO backup_user;"
sudo -u postgres psql -d parking_payment_db -c "GRANT SELECT ON ALL TABLES IN SCHEMA public TO backup_user;"
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Error

```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Check connection
psql -h localhost -U parking_user -d parking_payment_db

# Check .env database configuration
grep DB_ .env
```

#### 2. Queue Workers Not Processing

```bash
# Check supervisor status
sudo supervisorctl status parking-payment-system:*

# Check queue
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

#### 3. High Memory Usage

```bash
# Check PHP-FPM processes
ps aux | grep php-fpm

# Adjust PHP-FPM configuration
# Edit /etc/php/8.2/fpm/pool.d/www.conf
# Adjust: pm.max_children, pm.start_servers, pm.min_spare_servers, pm.max_spare_servers
```

## Support and Documentation

- **API Documentation**: See API_ROUTES_DOCUMENTATION.md
- **Technical Documentation**: See README.md
- **Issue Tracking**: GitHub Issues
- **Support Email**: support@dishub.go.id

## Maintenance Schedule

- **Daily**: Database backups at 2:00 AM
- **Weekly**: Security updates check (Sundays)
- **Monthly**: Performance optimization and cleanup
- **Quarterly**: Security audit and penetration testing

## Disaster Recovery

### Recovery Time Objective (RTO)

- **Critical Systems**: 1 hour
- **Non-critical Systems**: 4 hours

### Recovery Point Objective (RPO)

- **Database**: 1 hour (daily backups)
- **Application Code**: 15 minutes (git repository)

### Recovery Procedure

1. Restore database from latest backup
2. Restore application code from git
3. Run migrations
4. Verify data integrity
5. Restart services
6. Perform health checks

---

**Last Updated**: 2024-01-15
**Version**: 1.0
