<?php

namespace Database\Seeders;

use App\Models\ParkingRate;
use Illuminate\Database\Seeder;

class ParkingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default parking rates
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => null, // Default rate for all locations
            'rate' => 2000.00,
            'effective_from' => now(),
            'created_by' => 1, // Admin Dishub
        ]);

        ParkingRate::create([
            'vehicle_type' => 'car',
            'street_section' => null, // Default rate for all locations
            'rate' => 5000.00,
            'effective_from' => now(),
            'created_by' => 1, // Admin Dishub
        ]);

        // Location-specific rates (optional examples)
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Sudirman',
            'rate' => 3000.00,
            'effective_from' => now(),
            'created_by' => 1,
        ]);

        ParkingRate::create([
            'vehicle_type' => 'car',
            'street_section' => 'Jl. Sudirman',
            'rate' => 7000.00,
            'effective_from' => now(),
            'created_by' => 1,
        ]);
    }
}
