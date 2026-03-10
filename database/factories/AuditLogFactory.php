<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_type' => 'admin',
            'action' => fake()->randomElement(['create', 'update', 'delete', 'login', 'logout']),
            'entity_type' => fake()->randomElement(['transaction', 'attendant', 'rate', 'user']),
            'entity_id' => fake()->numberBetween(1, 1000),
            'old_values' => json_encode(['field' => 'old_value']),
            'new_values' => json_encode(['field' => 'new_value']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the audit log should be for a login action.
     *
     * @return $this
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'entity_type' => 'user',
        ]);
    }

    /**
     * Indicate that the audit log should be for a transaction.
     *
     * @return $this
     */
    public function transaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => 'transaction',
        ]);
    }
}
