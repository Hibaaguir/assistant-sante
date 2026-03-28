<?php

namespace Database\Seeders;

use App\Models\ChronicDiseaseCatalogItem;
use App\Models\ProfilSante;
use Illuminate\Database\Seeder;

class ChronicDiseaseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $defaultItems = [
            'Diabete',
            'Hypertension arterielle',
            'Asthme',
            'Maladie cardiaque',
            'Maladie renale chronique',
            'Maladie thyroidienne',
            'Arthrite',
            'Epilepsie',
            'Migraine chronique',
            'Maladie pulmonaire chronique',
            'Cholesterol eleve',
            'Depression',
            'Anemie',
        ];

        foreach ($defaultItems as $name) {
            $normalizedName = $this->normalizeText($name);
            if ($normalizedName === null) {
                continue;
            }

            ChronicDiseaseCatalogItem::query()->firstOrCreate([
                'name' => $normalizedName,
            ], [
                'created_by_user_id' => null,
            ]);
        }

        ProfilSante::query()->select('id', 'user_id', 'maladies_chroniques')->chunkById(200, function ($profiles): void {
            foreach ($profiles as $profile) {
                $diseases = is_array($profile->maladies_chroniques) ? $profile->maladies_chroniques : [];

                foreach ($diseases as $item) {
                    $name = $this->normalizeText($item);
                    if ($name === null) {
                        continue;
                    }

                    ChronicDiseaseCatalogItem::query()->firstOrCreate([
                        'name' => $name,
                    ], [
                        'created_by_user_id' => $profile->user_id,
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
