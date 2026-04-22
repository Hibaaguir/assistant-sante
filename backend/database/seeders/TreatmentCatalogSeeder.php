<?php

namespace Database\Seeders;

use App\Models\TreatmentCatalog;
use Illuminate\Database\Seeder;

/** Initialise le catalogue des médicaments disponibles dans l'application. */
class TreatmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
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
                    'treatment_type' => $normalizedType,
                    'treatment_name' => $normalizedName,
                ]);
            }
        }

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
