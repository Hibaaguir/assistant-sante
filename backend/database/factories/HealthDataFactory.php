<?php

namespace Database\Factories;

use App\Models\HealthData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthData>
 */
class HealthDataFactory extends Factory
{
    protected $model = HealthData::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => Carbon::instance($this->faker->dateTimeBetween('-45 days', 'now'))->toDateString(),
            'doctor_observation' => $this->faker->boolean(35)
                ? $this->faker->randomElement([
                    'Stable clinical status.',
                    'Monitor hydration and blood pressure.',
                    'Encourage medication adherence.',
                    'Continue current treatment plan.',
                ])
                : null,
        ];
    }
}
