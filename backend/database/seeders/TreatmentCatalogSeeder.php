<?php

namespace Database\Seeders;

use App\Models\ProfilSante;
use App\Models\CatalogueTraitement;
use Illuminate\Database\Seeder;

class TreatmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCatalog = [
            'Anti-inflammatoire' => ['Ibuprofene', 'Diclofenac', 'Ketoprofene', 'Naproxene'],
            'Antibiotique' => ['Amoxicilline', 'Azithromycine', 'Cefixime', 'Ciprofloxacine'],
            'Antidouleur' => ['Paracetamol', 'Tramadol', 'Codeine'],
            'Antihypertenseur' => ['Amlodipine', 'Ramipril', 'Losartan', 'Bisoprolol'],
            'Antidiabetique' => ['Metformine', 'Insuline', 'Gliclazide'],
            'Anticoagulant' => ['Heparine', 'Warfarine', 'Rivaroxaban'],
            'Antiallergique' => ['Cetirizine', 'Loratadine', 'Desloratadine'],
            'Antidepresseur' => ['Sertraline', 'Fluoxetine', 'Escitalopram'],
            'Corticoide' => ['Prednisone', 'Dexamethasone', 'Hydrocortisone'],
            'Traitement hormonal' => ['Levothyrox', 'Estradiol', 'Progesterone'],
            'Supplement vitaminique' => ['Vitamine D', 'Vitamine C', 'Fer'],
            'Inhalateur respiratoire' => ['Ventoline', 'Symbicort', 'Seretide'],
        ];

        foreach ($defaultCatalog as $type => $names) {
            $normalizedType = $this->normalizeText($type);
            if ($normalizedType === null) {
                continue;
            }

            foreach ($names as $name) {
                $normalizedName = $this->normalizeText($name) ?? '';
                TreatmentCatalogItem::query()->firstOrCreate([
                    'type' => $normalizedType,
                    'name' => $normalizedName,
                ], [
                    'created_by_id_utilisateur' => null,
                ]);
            }
        }

        ProfilSante::query()->select('id', 'id_utilisateur', 'traitements')->chunkById(200, function ($profiles): void {
            foreach ($profiles as $profile) {
                $treatments = is_array($profile->traitements) ? $profile->traitements : [];

                foreach ($treatments as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $type = $this->normalizeText($item['type'] ?? null);
                    $name = $this->normalizeText($item['name'] ?? null) ?? '';

                    if ($type === null) {
                        continue;
                    }

                    TreatmentCatalogItem::query()->firstOrCreate([
                        'type' => $type,
                        'name' => $name,
                    ], [
                        'created_by_id_utilisateur' => $profile->id_utilisateur,
                    ]);
                }
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
