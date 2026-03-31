<?php

namespace Database\Factories;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create or get the associated Compte
        $compte = Compte::factory()->create();

        return [
            'compte_id' => $compte->id,
            'nom' => fake()->name(),
            'date_naissance' => fake()->date(),
            'photo_profil' => null,
            'age' => fake()->numberBetween(18, 80),
            'role' => 'usager',
            'specialite' => null,
        ];
    }


}
