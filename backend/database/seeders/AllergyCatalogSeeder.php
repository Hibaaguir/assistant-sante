<?php

namespace Database\Seeders;

use App\Models\AllergyCatalogItem;
use App\Models\ProfilSante;
use Illuminate\Database\Seeder;

class AllergyCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $defaultItems = [
            'Pollen',
            'Acariens',
            "Poils d'animaux",
            'Poussiere',
            'Arachides',
            'Fruits de mer',
            'Lait (lactose)',
            'Oeufs',
            'Gluten',
            'Penicilline',
            'Aspirine',
            "Piqures d'insectes",
            'Moisissures',
        ];

        foreach ($defaultItems as $name) {
            $normalizedName = $this->normalizeText($name);
            if ($normalizedName === null) {
                continue;
            }

            AllergyCatalogItem::query()->firstOrCreate([
                'name' => $normalizedName,
            ], [
                'created_by_id_utilisateur' => null,
            ]);
        }

        ProfilSante::query()->select('id', 'id_utilisateur', 'allergies')->chunkById(200, function ($profiles): void {
            foreach ($profiles as $profile) {
                $allergies = is_array($profile->allergies) ? $profile->allergies : [];

                foreach ($allergies as $item) {
                    $name = $this->normalizeText($item);
                    if ($name === null) {
                        continue;
                    }

                    AllergyCatalogItem::query()->firstOrCreate([
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
