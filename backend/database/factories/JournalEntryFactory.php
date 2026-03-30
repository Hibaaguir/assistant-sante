<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entry_date' => fake()->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'sleep' => fake()->numberBetween(4, 9),
            'stress' => fake()->numberBetween(1, 9),
            'energy' => fake()->numberBetween(2, 9),
            'sugar' => fake()->randomElement(['low', 'medium', 'high']),
            'caffeine' => fake()->numberBetween(0, 6),
            'hydration' => fake()->randomFloat(1, 0.6, 4.5),
            'meals' => [
                ['type' => 'breakfast', 'label' => 'Bol de flocons d avoine'],
                ['type' => 'lunch', 'label' => 'Poulet et legumes'],
            ],
            'calories' => fake()->numberBetween(1300, 2800),
            'activity_type' => fake()->randomElement(['Marche', 'Course', 'Velo', 'Yoga', 'Musculation', null]),
            'activity_duration' => fake()->numberBetween(0, 90),
            'intensity' => fake()->randomElement(['light', 'medium', 'high']),
            'tobacco' => fake()->boolean(18),
            'alcohol' => fake()->boolean(30),
            'tobacco_types' => ['cigarette' => false, 'vape' => false],
            'cigarettes_per_day' => null,
            'puffs_per_day' => null,
            'alcohol_drinks' => 0,
        ];
    }
}
