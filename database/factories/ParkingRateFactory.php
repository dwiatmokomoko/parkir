<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingRate>
 */
class ParkingRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_type' => fake()->randomElement(['motorcycle', 'car']),
            'street_section' => fake()->optional(0.3)->streetName(),
            'rate' => fake()->randomElement([2000, 5000, 3000, 4000]),
            'effective_from' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the rate should be for motorcycles.
     *
     * @return $this
     */
    public function motorcycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'motorcycle',
            'rate' => 2000,
        ]);
    }

    /**
     * Indicate that the rate should be for cars.
     *
     * @return $this
     */
    public function car(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'car',
            'rate' => 5000,
        ]);
    }

    /**
     * Indicate that the rate should be location-specific.
     *
     * @return $this
     */
    public function locationSpecific(): static
    {
        return $this->state(fn (array $attributes) => [
            'street_section' => fake()->streetName(),
        ]);
    }
}
