<?php

namespace Database\Factories;

use App\Models\TreatmentCheck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TreatmentCheck>
 */
class TreatmentCheckFactory extends Factory
{
    protected $model = TreatmentCheck::class;

    public function definition(): array
    {
        $checkDate = Carbon::instance($this->faker->dateTimeBetween('-45 days', 'now'))->toDateString();
        $taken = $this->faker->boolean(80);

        return [
            'treatment_id'   => 1,
            'health_data_id' => 1,
            'check_date' => $checkDate,
            'medication_key' => '1__dose_1',
            'taken' => $taken,
            'checked_at' => $taken ? Carbon::parse($checkDate)->setTime($this->faker->numberBetween(6, 22), $this->faker->numberBetween(0, 59)) : null,
        ];
    }
}
