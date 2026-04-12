<?php
// Fabrique pour generer des entrees de journal fictives
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
            'caffeine' => fake()->numberBetween(0, 6),
            'hydration' => fake()->randomFloat(1, 0.6, 4.5),
            'alcohol' => fake()->boolean(30),
            'alcohol_glasses' => fake()->numberBetween(0, 4),
        ];
    }
}
