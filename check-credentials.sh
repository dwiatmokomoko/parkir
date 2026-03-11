#!/bin/bash

echo "==================================="
echo "Checking Database Credentials"
echo "==================================="

cd /var/www/html/parkir

echo ""
echo "Admin Users:"
echo "------------"
php artisan tinker --execute="
\$users = App\Models\User::where('role', 'admin')->get(['id', 'name', 'email', 'is_active']);
foreach (\$users as \$user) {
    echo 'ID: ' . \$user->id . ' | Email: ' . \$user->email . ' | Name: ' . \$user->name . ' | Active: ' . (\$user->is_active ? 'Yes' : 'No') . PHP_EOL;
}
"

echo ""
echo "Parking Attendants:"
echo "-------------------"
php artisan tinker --execute="
\$attendants = App\Models\ParkingAttendant::all(['id', 'registration_number', 'name', 'is_active']);
foreach (\$attendants as \$att) {
    echo 'ID: ' . \$att->id . ' | Reg: ' . \$att->registration_number . ' | Name: ' . \$att->name . ' | Active: ' . (\$att->is_active ? 'Yes' : 'No') . PHP_EOL;
}
"

echo ""
echo "==================================="
echo "Default Passwords:"
echo "==================================="
echo "Admin: password123"
echo "Attendant PIN: 1234"
echo "==================================="
