<?php

namespace Database\Seeders;

use App\Models\ParkingAttendant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParkingAttendantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendants = [
            [
                'registration_number' => 'JP001',
                'name' => 'Budi Santoso',
                'street_section' => 'Jl. Sudirman',
                'location_side' => 'Depan Gedung A',
                'bank_account_number' => '1234567890',
                'bank_name' => 'BCA',
                'pin' => Hash::make('1234'),
                'is_active' => true,
            ],
            [
                'registration_number' => 'JP002',
                'name' => 'Siti Aminah',
                'street_section' => 'Jl. Thamrin',
                'location_side' => 'Depan Mall',
                'bank_account_number' => '0987654321',
                'bank_name' => 'Mandiri',
                'pin' => Hash::make('1234'),
                'is_active' => true,
            ],
            [
                'registration_number' => 'JP003',
                'name' => 'Ahmad Yani',
                'street_section' => 'Jl. Gatot Subroto',
                'location_side' => 'Depan Kantor',
                'bank_account_number' => '1122334455',
                'bank_name' => 'BNI',
                'pin' => Hash::make('1234'),
                'is_active' => true,
            ],
            [
                'registration_number' => 'JP004',
                'name' => 'Dewi Lestari',
                'street_section' => 'Jl. Sudirman',
                'location_side' => 'Depan Gedung B',
                'bank_account_number' => '5566778899',
                'bank_name' => 'BRI',
                'pin' => Hash::make('1234'),
                'is_active' => true,
            ],
            [
                'registration_number' => 'JP005',
                'name' => 'Eko Prasetyo',
                'street_section' => 'Jl. Thamrin',
                'location_side' => 'Depan Hotel',
                'bank_account_number' => '9988776655',
                'bank_name' => 'BCA',
                'pin' => Hash::make('1234'),
                'is_active' => true,
            ],
        ];

        foreach ($attendants as $attendant) {
            ParkingAttendant::create($attendant);
        }
    }
}
