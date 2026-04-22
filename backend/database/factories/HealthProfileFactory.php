<?php

namespace Database\Factories;

use App\Models\HealthProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthProfile>
 */
class HealthProfileFactory extends Factory
{
    protected $model = HealthProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'height' => $this->faker->randomFloat(1, 145, 195),
            'initial_weight' => $this->faker->randomFloat(1, 45, 115),
            'current_weight' => $this->faker->randomFloat(1, 45, 115),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'goals' => $this->randomSubset([
                'Weight management',
                'Improve sleep quality',
                'Lower blood pressure',
                'Better glucose control',
                'Increase daily activity',
            ], 1, 3),
            'allergies' => $this->faker->boolean(35)
                ? $this->randomSubset(['Pollen', 'Peanuts', 'Dust mites', 'Seafood', 'Penicillin'], 1, 2)
                : [],
            'chronic_diseases' => $this->faker->boolean(45)
                ? $this->randomSubset(['Hypertension', 'Type 2 Diabetes', 'Asthma', 'Hypothyroidism'], 1, 2)
                : [],
            'smoker' => $this->faker->boolean(22),
            'alcoholic' => $this->faker->boolean(18),
            'doctor_invited' => false,
            'doctor_email' => null,
        ];
    }

    /**
     * @param array<int, string> $source
     * @return array<int, string>
     */
    private function randomSubset(array $source, int $min, int $max): array
    {
        shuffle($source);
        return array_slice($source, 0, random_int($min, $max));
    }
}
