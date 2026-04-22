<?php

namespace Database\Factories;

use App\Models\AnalysisResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalysisResult>
 */
class AnalysisResultFactory extends Factory
{
    protected $model = AnalysisResult::class;

    public function definition(): array
    {
        return [
            'health_data_id' => \App\Models\HealthData::factory(),
            'analysis_type' => $this->faker->randomElement(['Glucose', 'Lipid panel', 'Inflammation', 'Hematology']),
            'result_name' => $this->faker->randomElement([
                'Fasting blood glucose',
                'Total cholesterol',
                'C-reactive protein',
                'Hemoglobin',
            ]),
            'value' => $this->faker->randomFloat(2, 0.1, 260),
            'unit' => $this->faker->randomElement(['mmol/L', 'mg/dL', 'mg/L', 'g/dL']),
            'analysis_date' => Carbon::instance($this->faker->dateTimeBetween('-45 days', 'now'))->toDateString(),
        ];
    }
}
