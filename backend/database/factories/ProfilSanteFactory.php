<?php

namespace Database\Factories;

use App\Models\CatalogueTraitement;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfilSante>
 */
class ProfilSanteFactory extends Factory
{
    public function definition(): array
    {
        $sexe = fake()->randomElement(['homme', 'femme']);
        $taille = $sexe === 'homme'
            ? fake()->numberBetween(165, 195)
            : fake()->numberBetween(152, 180);

        $traitements = [];
        if (fake()->boolean(30)) {
            $rows = CatalogueTraitement::query()
                ->where('name', 'not like', 'Traitement %')
                ->inRandomOrder()
                ->limit(fake()->numberBetween(1, 3))
                ->get(['type', 'name']);

            foreach ($rows as $row) {
                $type = trim((string) ($row->type ?? ''));
                $name = trim((string) ($row->name ?? ''));
                if ($type === '' || $name === '') {
                    continue;
                }

                $frequencyUnit = fake()->randomElement(['jour', 'jour', 'semaine', 'mois']);
                $frequencyCount = match ($frequencyUnit) {
                    'jour' => fake()->numberBetween(1, 3),
                    'semaine' => fake()->numberBetween(1, 4),
                    default => fake()->numberBetween(1, 3),
                };

                $traitements[] = [
                    'type' => $type,
                    'name' => $name,
                    'dose' => fake()->randomElement(['5 mg', '10 mg', '20 mg', '250 mg', '500 mg', '1 comprime']),
                    'frequency_unit' => $frequencyUnit,
                    'frequency_count' => $frequencyCount,
                    'duration' => fake()->randomElement(['30 jours', '3 mois', '6 mois', 'traitement continu']),
                ];
            }
        }

        return [
            'id_utilisateur' => Utilisateur::factory(),
            'sexe' => $sexe,
            'taille' => $taille,
            'poids' => fake()->numberBetween(48, 110),
            'groupe_sanguin' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'objectifs' => ['Suivre ma sante regulierement'],
            'allergies' => [],
            'maladies_chroniques' => [],
            'traitements' => $traitements,
            'fumeur' => fake()->boolean(20),
            'alcool' => fake()->boolean(55),
            'consulte_medecin' => fake()->boolean(35),
            'medecin_peut_consulter' => false,
            'medecin_email' => null,
        ];
    }
}
