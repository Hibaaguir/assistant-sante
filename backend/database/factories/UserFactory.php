<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $dob = Carbon::instance($this->faker->dateTimeBetween('-80 years', '-18 years'));

        return [
            'account_id' => 1,
            'name' => $this->faker->name(),
            'date_of_birth' => $dob->toDateString(),
            'profile_photo' => null,
            'age' => $dob->age,
            'role' => 'user',
            'specialty' => null,
        ];
    }

    public function patient(): self
    {
        return $this->state(fn () => [
            'role' => 'user',
            'specialty' => null,
        ]);
    }

    public function doctor(): self
    {
        return $this->state(fn () => [
            'role' => 'doctor',
            'specialty' => $this->faker->randomElement([
                'General Practitioner',
                'Cardiology',
                'Endocrinology',
                'Pulmonology',
                'Internal Medicine',
            ]),
        ]);
    }
}
