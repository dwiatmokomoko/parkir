<?php

namespace Database\Seeders;

use App\Models\ParkingAttendant;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all parking attendants
        $attendants = ParkingAttendant::all();

        if ($attendants->isEmpty()) {
            $this->command->warn('No parking attendants found. Please run ParkingAttendantSeeder first.');
            return;
        }

        // Generate 100 sample transactions across the last 30 days
        for ($i = 0; $i < 100; $i++) {
            $attendant = $attendants->random();
            $vehicleType = fake()->randomElement(['motorcycle', 'car']);
            $amount = $vehicleType === 'motorcycle' ? 2000 : 5000;
            $paymentStatus = fake()->randomElement(['success', 'success', 'success', 'failed', 'pending']);
            $createdAt = Carbon::now()->subDays(fake()->numberBetween(0, 30));

            Transaction::create([
                'transaction_id' => 'TRX-' . Str::uuid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $vehicleType,
                'amount' => $amount,
                'payment_method' => $paymentStatus === 'success' ? fake()->randomElement(['qris', 'gopay', 'ovo', 'dana']) : null,
                'payment_status' => $paymentStatus,
                'qr_code_data' => json_encode([
                    'transaction_id' => 'TRX-' . Str::uuid(),
                    'amount' => $amount,
                    'attendant_id' => $attendant->id,
                ]),
                'qr_code_generated_at' => $createdAt,
                'qr_code_expires_at' => $createdAt->copy()->addMinutes(15),
                'paid_at' => $paymentStatus === 'success' ? $createdAt->copy()->addMinutes(2) : null,
                'failure_reason' => $paymentStatus === 'failed' ? 'Payment declined' : null,
                'retry_count' => $paymentStatus === 'failed' ? fake()->numberBetween(1, 3) : 0,
                'midtrans_transaction_id' => 'SNAP-' . Str::uuid(),
                'midtrans_response' => [
                    'snap_token' => 'SNAP-' . Str::uuid(),
                    'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/web/' . Str::uuid(),
                    'status_code' => $paymentStatus === 'success' ? '200' : '400',
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('100 sample transactions created successfully.');
    }
}
