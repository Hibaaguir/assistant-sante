<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthLabResult>
 */
class HealthLabResultFactory extends Factory
{
    public function definition(): array
    {
        $catalog = [
            'Biologie sanguine' => [
                ['label' => 'Glycémie', 'unit' => 'mmol/L', 'min' => 3.5, 'max' => 8.9],
                ['label' => 'HbA1c', 'unit' => '%', 'min' => 4.0, 'max' => 10.2],
                ['label' => 'CRP', 'unit' => 'mg/L', 'min' => 0.2, 'max' => 40.0],
                ['label' => 'Ferritine', 'unit' => 'ng/mL', 'min' => 8.0, 'max' => 420.0],
                ['label' => 'Créatinine', 'unit' => 'mg/L', 'min' => 4.0, 'max' => 24.0],
            ],
            'Hématologie' => [
                ['label' => 'Hémoglobine', 'unit' => 'g/dL', 'min' => 8.5, 'max' => 18.5],
            ],
            'Bilan lipidique' => [
                ['label' => 'Cholestérol total', 'unit' => 'mmol/L', 'min' => 2.5, 'max' => 9.0],
                ['label' => 'HDL', 'unit' => 'mmol/L', 'min' => 0.4, 'max' => 2.2],
                ['label' => 'LDL', 'unit' => 'mmol/L', 'min' => 0.7, 'max' => 6.5],
                ['label' => 'Triglycérides', 'unit' => 'mmol/L', 'min' => 0.4, 'max' => 5.2],
            ],
        ];

        $analysisType = (string) fake()->randomElement(array_keys($catalog));
        $option = fake()->randomElement($catalog[$analysisType]);

        return [
            'user_id' => User::factory(),
            'analysis_type' => $analysisType,
            'analysis_result' => $option['label'],
            'value' => fake()->randomFloat(2, $option['min'], $option['max']),
            'unit' => $option['unit'],
            'analysis_date' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m-d'),
        ];
    }
}
