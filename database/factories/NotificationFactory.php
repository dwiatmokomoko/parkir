<?php

namespace Database\Factories;

use App\Models\ParkingAttendant;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parking_attendant_id' => ParkingAttendant::factory(),
            'transaction_id' => Transaction::factory(),
            'type' => fake()->randomElement(['payment_success', 'payment_failed', 'qr_expired']),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'data' => json_encode([
                'amount' => fake()->numberBetween(1000, 50000),
                'vehicle_type' => fake()->randomElement(['motorcycle', 'car']),
            ]),
            'is_read' => false,
        ];
    }

    /**
     * Indicate that the notification should be read.
     *
     * @return $this
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification should be for payment success.
     *
     * @return $this
     */
    public function paymentSuccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment_success',
            'title' => 'Pembayaran Berhasil',
            'message' => 'Pembayaran parkir telah berhasil diproses',
        ]);
    }
}
