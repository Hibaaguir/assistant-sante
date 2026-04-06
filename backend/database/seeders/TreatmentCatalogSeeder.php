<?php

namespace Database\Seeders;

use App\Models\HealthProfile;
use App\Models\TreatmentCatalog;
use Illuminate\Database\Seeder;

class TreatmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing catalog entries to allow fresh seed
        TreatmentCatalog::query()->delete();

        $defaultCatalog = [
            'Anti-inflammatoire' => ['Ibuprofène', 'Diclofénac', 'Kétoprofène', 'Naproxène'],
            'Antibiotique' => ['Amoxicilline', 'Azithromycine', 'Céfixime', 'Ciprofloxacine'],
            'Antidouleur' => ['Paracétamol', 'Tramadol', 'Codéine'],
            'Antihypertenseur' => ['Amlodipine', 'Ramipril', 'Losartan', 'Bisoprolol'],
            'Antidiabétique' => ['Metformine', 'Insuline', 'Gliclazide'],
            'Anticoagulant' => ['Héparine', 'Warfarine', 'Rivaroxaban'],
            'Antihistaminique' => ['Cétirizine', 'Loratadine', 'Desloratadine'],
            'Antidépresseur' => ['Sertraline', 'Fluoxétine', 'Escitalopram'],
            'Corticostéroïde' => ['Prednisone', 'Dexaméthasone', 'Hydrocortisone'],
            'Thérapie hormonale' => ['Lévothyroxine', 'Estradiol', 'Progestérone'],
            'Supplément vitaminé' => ['Vitamine D', 'Vitamine C', 'Fer'],
            'Inhalateur respiratoire' => ['Albutérol', 'Budésonide/Formotérol', 'Fluticasone/Salmétérol'],
        ];

        foreach ($defaultCatalog as $type => $names) {
            $normalizedType = $this->normalizeText($type);
            if ($normalizedType === null) {
                continue;
            }

            foreach ($names as $name) {
                $normalizedName = $this->normalizeText($name) ?? '';
                TreatmentCatalog::query()->firstOrCreate([
                    'medication_type' => $normalizedType,
                    'medication_name' => $normalizedName,
                ], [
                    'created_by_user_id' => null,
                ]);
            }
        }

        HealthProfile::query()->select('id', 'user_id')->chunkById(200, function ($profiles): void {
            foreach ($profiles as $profile) {
                // Treatment data is managed through the treatments() relationship
                // Catalog seeding is primarily for default medications
            }
        });
    }

    private function normalizeText(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return $normalized !== '' ? $normalized : null;
    }
}
