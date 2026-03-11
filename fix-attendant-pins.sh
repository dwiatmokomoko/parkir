#!/bin/bash

echo "==================================="
echo "Fixing Attendant PINs in Database"
echo "==================================="

cd /var/www/html/parkir

echo "Updating all attendant PINs to '1234' (hashed)..."

php artisan tinker --execute="
\$attendants = App\Models\ParkingAttendant::all();
foreach (\$attendants as \$att) {
    // Update PIN directly in database with hashed value
    \$hashedPin = Hash::make('1234');
    \DB::table('parking_attendants')
        ->where('id', \$att->id)
        ->update(['pin' => \$hashedPin]);
    echo 'Updated PIN for: ' . \$att->registration_number . ' - ' . \$att->name . PHP_EOL;
}
echo PHP_EOL . 'All PINs updated successfully!' . PHP_EOL;
"

echo ""
echo "==================================="
echo "Testing login for JP001..."
echo "==================================="

curl -X POST https://parkir.lemahteles.fun/api/attendant/auth/login \
  -H "Content-Type: application/json" \
  -d '{"registration_number":"JP001","pin":"1234"}' \
  -s | python3 -m json.tool

echo ""
echo "==================================="
echo "Done!"
echo "==================================="
