<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorInvitation>
 */
class DoctorInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_user_id' => User::factory(),
            'doctor_user_id' => null,
            'doctor_email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'revoked']),
            'token' => hash('sha256', Str::uuid()->toString()),
            'accepted_at' => null,
            'rejected_at' => null,
            'revoked_at' => null,
            'doctor_observation' => null,
            'doctor_observation_updated_at' => null,
        ];
    }
}
