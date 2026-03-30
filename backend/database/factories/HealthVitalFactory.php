<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthVital>
 */
class HealthVitalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'measured_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'heart_rate' => fake()->numberBetween(55, 115),
            'systolic_pressure' => fake()->numberBetween(100, 155),
            'diastolic_pressure' => fake()->numberBetween(60, 100),
            'oxygen_saturation' => fake()->randomFloat(1, 93, 100),
        ];
    }
}
