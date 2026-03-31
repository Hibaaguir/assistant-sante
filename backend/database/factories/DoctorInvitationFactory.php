<?php

namespace Database\Factories;

use App\Models\Utilisateur;
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
            'id_patient_utilisateur' => Utilisateur::factory(),
            'id_medecin_utilisateur' => null,
            'doctor_email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'revoked']),
            'token' => hash('sha256', Str::uuid()->toString()),
            'accepted_at' => null,
            'rejected_at' => null,
            'revoked_at' => null,
            'general_observation' => null,
            'general_observation_updated_at' => null,
        ];
    }
}
