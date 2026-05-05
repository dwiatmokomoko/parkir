<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin account
        User::updateOrCreate(
            ['email' => 'admin@dishub.go.id'],
            [
                'name' => 'Admin Dishub',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create additional admin accounts for testing
        User::updateOrCreate(
            ['email' => 'admin.test@dishub.go.id'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
