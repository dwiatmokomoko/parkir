<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $actions = ['create', 'update', 'delete', 'login', 'logout', 'activate', 'deactivate'];
        $entityTypes = ['transaction', 'attendant', 'rate', 'user', 'notification'];

        // Generate sample audit logs for the last 30 days
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $action = fake()->randomElement($actions);
            $entityType = fake()->randomElement($entityTypes);
            $createdAt = Carbon::now()->subDays(fake()->numberBetween(0, 30));

            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => 'admin',
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => fake()->numberBetween(1, 1000),
                'old_values' => json_encode([
                    'field1' => 'old_value_' . fake()->word(),
                    'field2' => fake()->numberBetween(1000, 9999),
                ]),
                'new_values' => json_encode([
                    'field1' => 'new_value_' . fake()->word(),
                    'field2' => fake()->numberBetween(1000, 9999),
                ]),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('50 sample audit logs created successfully.');
    }
}
