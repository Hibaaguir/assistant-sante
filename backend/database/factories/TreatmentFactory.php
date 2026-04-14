<?php

namespace Database\Factories;

use App\Models\Treatment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Treatment>
 */
class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-120 days', '-5 days'));
        $hasEndDate = $this->faker->boolean(30);

        return [
            'health_data_id' => 1,
            'treatment_catalog_id' => 1,
            'dose' => $this->faker->randomElement(['1 tablet', '2 tablets', '250 mg', '500 mg', '10 ml']),
            'frequency' => $this->faker->randomElement(['day', 'week', 'month']),
            'daily_doses' => $this->faker->numberBetween(1, 3),
            'start_date' => $startDate->toDateString(),
            'end_date' => $hasEndDate ? $startDate->copy()->addDays($this->faker->numberBetween(14, 160))->toDateString() : null,
        ];
    }
}
