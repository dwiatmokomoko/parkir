# Server Setup Commands

## Langkah-langkah Setup di Server Production

### 1. Update .env di Server

```bash
cd /var/www/html/parkir

# Edit .env file
nano .env
```

Pastikan konfigurasi database sesuai server:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=3424
DB_DATABASE=parkir
DB_USERNAME=postgres
DB_PASSWORD=postgres
DB_SSLMODE=prefer

# Cache & Session (gunakan file, bukan redis)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 2. Fix Permissions

```bash
cd /var/www/html/parkir

# Fix ownership
sudo chown -R www-data:www-data .
sudo chown -R moko:moko .git

# Fix permissions
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/logs

# Add user to www-data group
sudo usermod -a -G www-data moko
```

### 3. Install Dependencies

```bash
cd /var/www/html/parkir

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 4. Run Migrations

```bash
cd /var/www/html/parkir

# Check migration status
php artisan migrate:status

# Run migrations (fresh install)
php artisan migrate:fresh --seed

# Or just migrate (without dropping tables)
php artisan migrate --seed
```

### 5. Optimize for Production

```bash
cd /var/www/html/parkir

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 6. Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart Apache
sudo systemctl restart apache2

# Or restart Nginx (if using nginx)
sudo systemctl restart nginx
```

### 7. Set Correct Permissions (Final)

```bash
cd /var/www/html/parkir

# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
```

### 8. Verify Installation

```bash
# Check Laravel version
php artisan --version

# Check database connection
php artisan migrate:status

# Check application
curl -I http://localhost
```

## Troubleshooting

### Permission Denied Errors

```bash
sudo chmod -R 777 storage/logs
sudo chown -R www-data:www-data storage
```

### Database Connection Errors

```bash
# Test PostgreSQL connection
psql -h 127.0.0.1 -p 3424 -U postgres -d parkir

# Check PostgreSQL status
sudo systemctl status postgresql
```

### Redis Connection Errors

Pastikan di `.env`:
```
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### Git Permission Errors

```bash
sudo chown -R moko:moko /var/www/html/parkir/.git
```

## Quick Fix Script

Jalankan script yang sudah disediakan:

```bash
cd /var/www/html/parkir

# Fix permissions
chmod +x fix-all-issues.sh
sudo ./fix-all-issues.sh

# Switch to file cache
chmod +x switch-to-file-cache.sh
./switch-to-file-cache.sh

# Fix database and logs
chmod +x fix-db-and-logs.sh
sudo ./fix-db-and-logs.sh
```

## Default Login Credentials

Setelah seeding, gunakan kredensial default:

- **Admin**: Check `database/seeders/DatabaseSeeder.php`
- **Attendant**: Check `database/seeders/DatabaseSeeder.php`

Segera ubah password setelah login pertama kali!
