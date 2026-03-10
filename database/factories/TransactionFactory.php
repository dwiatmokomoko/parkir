<?php

namespace Database\Factories;

use App\Models\ParkingAttendant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicleType = fake()->randomElement(['motorcycle', 'car']);
        $amount = $vehicleType === 'motorcycle' ? 2000 : 5000;
        $paymentStatus = fake()->randomElement(['pending', 'success', 'failed', 'expired']);

        return [
            'transaction_id' => 'TRX-' . Str::uuid(),
            'parking_attendant_id' => ParkingAttendant::factory(),
            'street_section' => fake()->streetName(),
            'vehicle_type' => $vehicleType,
            'amount' => $amount,
            'payment_method' => $paymentStatus === 'success' ? fake()->randomElement(['qris', 'gopay', 'ovo', 'dana']) : null,
            'payment_status' => $paymentStatus,
            'qr_code_data' => json_encode([
                'transaction_id' => 'TRX-' . Str::uuid(),
                'amount' => $amount,
            ]),
            'qr_code_generated_at' => Carbon::now()->subMinutes(5),
            'qr_code_expires_at' => Carbon::now()->addMinutes(10),
            'paid_at' => $paymentStatus === 'success' ? Carbon::now() : null,
            'failure_reason' => $paymentStatus === 'failed' ? 'Payment declined' : null,
            'retry_count' => 0,
            'midtrans_transaction_id' => 'SNAP-' . Str::uuid(),
            'midtrans_response' => [
                'snap_token' => 'SNAP-' . Str::uuid(),
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/web/' . Str::uuid(),
            ],
        ];
    }

    /**
     * Indicate that the transaction should be successful.
     *
     * @return $this
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'success',
            'payment_method' => fake()->randomElement(['qris', 'gopay', 'ovo', 'dana']),
            'paid_at' => Carbon::now(),
            'failure_reason' => null,
        ]);
    }

    /**
     * Indicate that the transaction should be failed.
     *
     * @return $this
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
            'payment_method' => null,
            'paid_at' => null,
            'failure_reason' => 'Payment declined',
        ]);
    }

    /**
     * Indicate that the transaction should be pending.
     *
     * @return $this
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
            'payment_method' => null,
            'paid_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Indicate that the transaction should be for a motorcycle.
     *
     * @return $this
     */
    public function motorcycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
        ]);
    }

    /**
     * Indicate that the transaction should be for a car.
     *
     * @return $this
     */
    public function car(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'car',
            'amount' => 5000,
        ]);
    }
}
