#!/bin/bash

echo "=========================================="
echo "Final Fix - Parking Payment System"
echo "=========================================="

cd /var/www/html/parkir

# Step 1: Fix ownership
echo "[1/8] Fixing ownership..."
sudo chown -R moko:moko /var/www/html/parkir

# Step 2: Pull latest code
echo "[2/8] Pulling latest code..."
git reset --hard origin/main
git pull origin main

# Step 3: Update auth config manually if pull failed
echo "[3/8] Updating auth config..."
cat > /tmp/auth_guards.txt << 'EOF'
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'attendant' => [
            'driver' => 'session',
            'provider' => 'attendants',
        ],
    ],
EOF

cat > /tmp/auth_providers.txt << 'EOF'
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        
        'attendants' => [
            'driver' => 'eloquent',
            'model' => App\Models\ParkingAttendant::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],
EOF

# Backup original auth config
cp config/auth.php config/auth.php.backup

# Update auth config using sed
sed -i "/    'guards' => \[/,/    \],/{
    /    'guards' => \[/r /tmp/auth_guards.txt
    d
}" config/auth.php 2>/dev/null || echo "Auth guards already updated or manual edit needed"

# Step 4: Set correct ownership
echo "[4/8] Setting correct ownership..."
sudo chown -R www-data:www-data /var/www/html/parkir
sudo chown -R moko:moko /var/www/html/parkir/.git
sudo chmod -R 755 /var/www/html/parkir
sudo chmod -R 775 /var/www/html/parkir/storage
sudo chmod -R 775 /var/www/html/parkir/bootstrap/cache

# Step 5: Clear all caches
echo "[5/8] Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Step 6: Optimize for production
echo "[6/8] Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 7: Restart services
echo "[7/8] Restarting services..."
sudo systemctl restart php8.3-fpm
sudo systemctl restart apache2

# Step 8: Test application
echo "[8/8] Testing application..."
sleep 2
curl -I https://parkir.lemahteles.fun 2>&1 | head -1

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Login Credentials:"
echo "------------------"
echo "Admin:"
echo "  URL: https://parkir.lemahteles.fun/login"
echo "  Email: admin@dishub.go.id"
echo "  Password: password123"
echo ""
echo "Attendant:"
echo "  URL: https://parkir.lemahteles.fun/attendant/login"
echo "  Registration: JP001, JP002, JP003, JP004, JP005"
echo "  PIN: 1234"
echo ""
echo "=========================================="
