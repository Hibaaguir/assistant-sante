<?php
// Fabrique pour generer des entrees de journal fictives
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    public function definition(): array
    {
        $alcohol = fake()->boolean(25);

        return [
            'user_id' => 1,
            'entry_date' => fake()->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'sleep' => fake()->numberBetween(4, 10),
            'stress' => fake()->numberBetween(0, 10),
            'energy' => fake()->numberBetween(0, 10),
            'caffeine' => fake()->numberBetween(0, 8),
            'hydration' => fake()->randomFloat(1, 0.8, 4.5),
            'sugar_intake' => fake()->randomElement([
                'Low sugar intake',
                'Moderate sugar intake',
                'High sugar intake',
                'No added sugar today',
            ]),
            'alcohol' => $alcohol,
            'alcohol_glasses' => $alcohol ? fake()->numberBetween(1, 4) : null,
        ];
    }
}
