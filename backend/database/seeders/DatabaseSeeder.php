<?php
// Semoir principal pour initialiser les donnees de la base de donnees
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminAccountSeeder::class,
            TreatmentCatalogSeeder::class,
            RealisticMedicalDatasetSeeder::class,
        ]);
    }
}
