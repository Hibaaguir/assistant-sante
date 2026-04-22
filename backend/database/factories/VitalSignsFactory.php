<?php

namespace Database\Factories;

use App\Models\VitalSigns;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VitalSigns>
 */
class VitalSignsFactory extends Factory
{
    protected $model = VitalSigns::class;

    public function definition(): array
    {
        return [
            'health_data_id' => \App\Models\HealthData::factory(),
            'measured_at' => $this->faker->dateTimeBetween('-45 days', 'now'),
            'heart_rate' => $this->faker->numberBetween(55, 100),
            'systolic_pressure' => $this->faker->numberBetween(100, 150),
            'diastolic_pressure' => $this->faker->numberBetween(60, 95),
            'oxygen_saturation' => $this->faker->numberBetween(92, 100),
        ];
    }
}
