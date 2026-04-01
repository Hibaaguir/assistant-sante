<?php

namespace Database\Seeders;

use App\Models\Compte;
use App\Models\Utilisateur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur administrateur
        $compteAdmin = Compte::create([
            'email' => 'admin@gmail.com',
            'motdepasse' => Hash::make('admin1234'),
            'statut_compte' => 'actif',
        ]);

        Utilisateur::create([
            'compte_id' => $compteAdmin->id,
            'nom' => 'Administrateur',
            'date_naissance' => null,
            'photo_profil' => null,
            'age' => null,
            'role' => 'administrateur',
            'specialite' => null,
        ]);

        // Utilisateur test normal
        $compteTest = Compte::create([
            'email' => 'test@example.com',
            'motdepasse' => Hash::make('password'),
            'statut_compte' => 'actif',
        ]);

        Utilisateur::create([
            'compte_id' => $compteTest->id,
            'nom' => 'Test User',
            'date_naissance' => null,
            'photo_profil' => null,
            'age' => null,
            'role' => 'usager',
            'specialite' => null,
        ]);

        $this->call([
            // UserSeeder::class,
            // TreatmentCatalogSeeder::class,
            // AllergyCatalogSeeder::class,
            // ChronicDiseaseCatalogSeeder::class,
        ]);
    }
}
