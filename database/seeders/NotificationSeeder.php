<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\ParkingAttendant;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendants = ParkingAttendant::all();
        $transactions = Transaction::where('payment_status', 'success')->get();

        if ($attendants->isEmpty() || $transactions->isEmpty()) {
            $this->command->warn('No attendants or successful transactions found. Please run other seeders first.');
            return;
        }

        // Generate sample notifications for successful transactions
        foreach ($transactions->take(50) as $transaction) {
            $createdAt = $transaction->created_at->copy()->addMinutes(2);

            Notification::create([
                'parking_attendant_id' => $transaction->parking_attendant_id,
                'transaction_id' => $transaction->id,
                'type' => 'payment_success',
                'title' => 'Pembayaran Berhasil',
                'message' => "Pembayaran parkir sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " telah berhasil diproses.",
                'data' => json_encode([
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
                    'street_section' => $transaction->street_section,
                    'payment_method' => $transaction->payment_method,
                ]),
                'is_read' => fake()->boolean(70), // 70% chance of being read
                'read_at' => fake()->boolean(70) ? $createdAt->copy()->addMinutes(fake()->numberBetween(1, 60)) : null,
                'created_at' => $createdAt,
            ]);
        }

        // Generate some failed payment notifications
        $failedTransactions = Transaction::where('payment_status', 'failed')->get();
        foreach ($failedTransactions->take(20) as $transaction) {
            $createdAt = $transaction->created_at->copy()->addMinutes(2);

            Notification::create([
                'parking_attendant_id' => $transaction->parking_attendant_id,
                'transaction_id' => $transaction->id,
                'type' => 'payment_failed',
                'title' => 'Pembayaran Gagal',
                'message' => "Pembayaran parkir gagal. Silakan coba lagi atau hubungi customer service.",
                'data' => json_encode([
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
                    'failure_reason' => $transaction->failure_reason,
                ]),
                'is_read' => fake()->boolean(50),
                'read_at' => fake()->boolean(50) ? $createdAt->copy()->addMinutes(fake()->numberBetween(1, 120)) : null,
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('Sample notifications created successfully.');
    }
}
