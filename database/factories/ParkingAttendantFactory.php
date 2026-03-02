<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingAttendant>
 */
class ParkingAttendantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => 'ATT' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->name(),
            'street_section' => fake()->streetName(),
            'location_side' => fake()->randomElement(['Utara', 'Selatan', 'Timur', 'Barat']),
            'bank_account_number' => fake()->numerify('##########'),
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'pin' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the attendant should be inactive.
     *
     * @return $this
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
