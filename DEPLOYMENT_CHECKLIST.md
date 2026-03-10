# Deployment Checklist - Parking Payment System

## Server Information
- **IP Address**: 100.119.174.120
- **Port**: 22
- **Username**: moko
- **Application Path**: /var/www/html/parkir
- **Repository**: https://github.com/dwiatmokomoko/parkir.git

## Pre-Deployment Verification ✓

- [x] Code committed to GitHub
- [x] Deployment scripts created and pushed
- [x] Server folder created at `/var/www/html/parkir`
- [x] Server credentials verified

## Phase 1: Initial Server Setup

Execute on server as `moko` user:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Navigate to application directory
cd /var/www/html/parkir

# Clone the repository
git clone https://github.com/dwiatmokomoko/parkir.git .

# Make setup script executable
chmod +x server-setup.sh

# Run server setup (this will install all dependencies)
./server-setup.sh
```

**What this does:**
- Updates system packages
- Installs PHP 8.2, PostgreSQL, Redis, Nginx, Supervisor
- Installs Composer and Node.js
- Clones the repository
- Installs PHP and Node dependencies
- Builds frontend assets
- Creates `.env` file from `.env.example`
- Generates application key

**Estimated Time**: 15-20 minutes

## Phase 2: Database Configuration

After server setup completes, configure the database:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Create PostgreSQL user and database
sudo -u postgres psql << EOF
CREATE USER parking_user WITH PASSWORD 'your_secure_password_here';
CREATE DATABASE parking_payment_db OWNER parking_user;
\c parking_payment_db
CREATE EXTENSION IF NOT EXISTS uuid-ossp;
CREATE EXTENSION IF NOT EXISTS pg_trgm;
EOF

# Edit .env file with database credentials
nano /var/www/html/parkir/.env
```

**Update these values in .env:**
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=parking_payment_db
DB_USERNAME=parking_user
DB_PASSWORD=your_secure_password_here
```

## Phase 3: Midtrans Configuration

Configure payment gateway in `.env`:

```bash
# Edit .env file
nano /var/www/html/parkir/.env
```

**Update these values:**
```
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_MERCHANT_ID=your_merchant_id
MIDTRANS_IS_PRODUCTION=false  # Set to true for production
```

**Get these from**: https://dashboard.midtrans.com/

## Phase 4: Database Migrations

Run migrations to create tables:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

cd /var/www/html/parkir

# Run migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed
```

## Phase 5: Web Server Configuration

Setup Nginx:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Copy Nginx configuration
sudo cp /var/www/html/parkir/nginx.conf /etc/nginx/sites-available/parkir

# Enable the site
sudo ln -s /etc/nginx/sites-available/parkir /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

## Phase 6: SSL Certificate Setup

Setup HTTPS with Let's Encrypt:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Get SSL certificate (replace with your domain)
sudo certbot certonly --nginx -d parkir.dishub.go.id

# Update Nginx configuration with SSL paths
sudo nano /etc/nginx/sites-available/parkir

# Reload Nginx
sudo systemctl reload nginx

# Test SSL
curl -I https://parkir.dishub.go.id
```

## Phase 7: Queue Workers Setup

Configure Supervisor for background jobs:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Copy supervisor configuration
sudo cp /var/www/html/parkir/supervisor.conf /etc/supervisor/conf.d/parkir.conf

# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start parkir:*

# Check status
sudo supervisorctl status parkir:*
```

## Phase 8: Backup Configuration

Setup automated database backups:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Create backup directory
sudo mkdir -p /var/backups/parkir/database
sudo chown -R moko:moko /var/backups/parkir

# Copy backup script
sudo cp /var/www/html/parkir/backup-database.sh /usr/local/bin/
sudo chmod +x /usr/local/bin/backup-database.sh

# Add cron job for daily backups at 2:00 AM
crontab -e

# Add this line:
# 0 2 * * * /usr/local/bin/backup-database.sh
```

## Phase 9: Permissions and Ownership

Set correct permissions:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

cd /var/www/html/parkir

# Set storage and bootstrap permissions
chmod -R 775 storage bootstrap/cache

# Set ownership (if running as www-data)
sudo chown -R www-data:www-data /var/www/html/parkir
```

## Phase 10: Health Checks

Verify everything is working:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Check application health
curl https://parkir.dishub.go.id/api/health

# Check database connection
cd /var/www/html/parkir
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit

# Check queue workers
sudo supervisorctl status parkir:*

# Check Redis connection
redis-cli ping

# Check logs
tail -f storage/logs/laravel.log
```

## Phase 11: Performance Optimization

Optimize for production:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

cd /var/www/html/parkir

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --no-dev --optimize-autoloader
```

## Phase 12: Monitoring Setup

Setup monitoring and alerts:

```bash
# SSH into server
ssh -p 22 moko@100.119.174.120

# Check system resources
free -h
df -h
top

# Monitor logs
tail -f /var/www/html/parkir/storage/logs/laravel.log
```

## Deployment Checklist

### Pre-Deployment
- [ ] Code pushed to GitHub
- [ ] Server credentials verified
- [ ] Server folder created

### Phase 1: Server Setup
- [ ] SSH access confirmed
- [ ] server-setup.sh executed successfully
- [ ] All dependencies installed
- [ ] Frontend assets built

### Phase 2: Database
- [ ] PostgreSQL user created
- [ ] Database created
- [ ] Extensions enabled
- [ ] .env database credentials configured

### Phase 3: Midtrans
- [ ] Midtrans account created
- [ ] API keys obtained
- [ ] .env Midtrans credentials configured

### Phase 4: Migrations
- [ ] Database migrations completed
- [ ] Tables created successfully
- [ ] Initial data seeded (optional)

### Phase 5: Web Server
- [ ] Nginx configuration copied
- [ ] Nginx configuration tested
- [ ] Nginx reloaded

### Phase 6: SSL
- [ ] SSL certificate obtained
- [ ] Nginx SSL configuration updated
- [ ] HTTPS working

### Phase 7: Queue Workers
- [ ] Supervisor configuration copied
- [ ] Supervisor updated
- [ ] Workers running

### Phase 8: Backups
- [ ] Backup directory created
- [ ] Backup script copied
- [ ] Cron job configured

### Phase 9: Permissions
- [ ] Storage permissions set
- [ ] Bootstrap permissions set
- [ ] Ownership configured

### Phase 10: Health Checks
- [ ] API health check passed
- [ ] Database connection verified
- [ ] Queue workers running
- [ ] Redis connection working
- [ ] Logs accessible

### Phase 11: Optimization
- [ ] Configuration cached
- [ ] Routes cached
- [ ] Views cached
- [ ] Autoloader optimized

### Phase 12: Monitoring
- [ ] System resources monitored
- [ ] Logs monitored
- [ ] Alerts configured

## Troubleshooting

### Database Connection Error
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Test connection
psql -h 127.0.0.1 -U parking_user -d parking_payment_db
```

### Queue Workers Not Running
```bash
# Check supervisor status
sudo supervisorctl status parkir:*

# Restart workers
sudo supervisorctl restart parkir:*

# Check logs
tail -f /var/log/supervisor/parkir-*.log
```

### Nginx Not Starting
```bash
# Test configuration
sudo nginx -t

# Check error logs
sudo tail -f /var/log/nginx/error.log
```

### High Memory Usage
```bash
# Check processes
ps aux | grep php

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Support

For issues or questions:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Review DEPLOYMENT_GUIDE.md
3. Check API_ROUTES_DOCUMENTATION.md
4. Contact: support@dishub.go.id

## Next Steps After Deployment

1. **Test all features** in production environment
2. **Configure monitoring** and alerting
3. **Setup automated backups** verification
4. **Document any customizations** made
5. **Train users** on system usage
6. **Schedule regular maintenance** windows

---

**Last Updated**: 2026-03-11
**Status**: Ready for Deployment
