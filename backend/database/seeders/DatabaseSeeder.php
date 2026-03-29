<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Utilisateur administrateur
        User::factory()->create([
            'name' => 'Administrateur',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin1234'),
            'role' => 'administrateur',
            'statut_admin' => 'Actif',
        ]);

        // Utilisateur test normal
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->call([
            UserSeeder::class,
            TreatmentCatalogSeeder::class,
            AllergyCatalogSeeder::class,
            ChronicDiseaseCatalogSeeder::class,
            // JournalSeeder::class,
        ]);
    }
}
