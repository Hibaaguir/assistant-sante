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
        $alcohol = $this->faker->boolean(25);

        return [
            'user_id'        => User::factory(),
            'entry_date'     => $this->faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'sleep'          => $this->faker->numberBetween(4, 10),
            'stress'         => $this->faker->numberBetween(0, 10),
            'energy'         => $this->faker->numberBetween(0, 10),
            'caffeine'       => $this->faker->numberBetween(0, 8),
            'hydration'      => $this->faker->randomFloat(1, 0.8, 4.5),
            'sugar_intake'   => $this->faker->randomElement([
                'Consommation faible',
                'Consommation moderee',
                'Consommation elevee',
            ]),
            'alcohol'        => $alcohol,
            'alcohol_glasses'=> $alcohol ? $this->faker->numberBetween(1, 4) : null,
        ];
    }
}
