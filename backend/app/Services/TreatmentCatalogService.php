<?php

namespace App\Services;

use App\Models\TreatmentCatalog;

class TreatmentCatalogService
{
    // Récupère et organise les traitements par type pour un affichage optimisé côté frontend.
    public function buildCatalog(): array
    {
        $items = TreatmentCatalog::query()
            ->orderBy('treatment_type')
            ->orderBy('treatment_name')
            ->get(['treatment_type', 'treatment_name']);

        $types       = [];
        $namesByType = [];

        foreach ($items as $item) {
            $type = $this->normalizeText($item->treatment_type);
            if ($type === null) {
                continue;
            }

            $this->appendUniqueValue($types, $type);

            $name = $this->normalizeText($item->treatment_name);
            if ($name === null) {
                continue;
            }

            if (!array_key_exists($type, $namesByType)) {
                $namesByType[$type] = [];
            }

            $this->appendUniqueValue($namesByType[$type], $name);
        }

        // Trier les types alphabétiquement (sans tenir compte de la casse)
        usort($types, function (string $a, string $b) {
            return strcasecmp($a, $b);
        });

        // Trier les noms de chaque type alphabétiquement
        foreach ($namesByType as &$names) {
            usort($names, function (string $a, string $b) {
                return strcasecmp($a, $b);
            });
        }
        unset($names);

        // Trier les clés du tableau par type alphabétiquement
        uksort($namesByType, function (string $a, string $b) {
            return strcasecmp($a, $b);
        });

        return [
            'types'         => $types,
            'names_by_type' => $namesByType,
        ];
    }

    // Enregistrer une entree dans le catalogue
    public function saveEntry(?string $type, ?string $name = null): void
    {
        $normalizedType = $this->normalizeText($type);
        if ($normalizedType === null) {
            return;
        }

        $normalizedName = $this->normalizeText($name) ?? '';
        $lowerType      = mb_strtolower($normalizedType, 'UTF-8');
        $lowerName      = mb_strtolower($normalizedName, 'UTF-8');

        // Vérifier si cette entrée existe déjà (comparaison insensible à la casse)
        $existing = TreatmentCatalog::query()
            ->whereRaw('LOWER(treatment_type) = ?', [$lowerType])
            ->whereRaw('LOWER(treatment_name) = ?', [$lowerName])
            ->first();

        if ($existing) {
            return;
        }

        TreatmentCatalog::query()->create([
            'treatment_type' => $normalizedType,
            'treatment_name' => $normalizedName,
        ]);
    }

    // Enregistrer les entrees depuis un tableau de traitements
    public function saveFromTreatments(array $treatments): void
    {
        foreach ($treatments as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;
            $name = $item['name'] ?? null;

            $this->saveEntry($type, null);
            $this->saveEntry($type, $name);
        }
    }

    // Nettoyer et normaliser un texte (supprimer espaces en trop, retourner null si vide)
    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');

        return $normalized !== '' ? $normalized : null;
    }

    // Ajouter une valeur dans un tableau seulement si elle n'existe pas déjà
    private function appendUniqueValue(array &$target, string $value): void
    {
        foreach ($target as $existing) {
            if (strcasecmp($existing, $value) === 0) {
                return;
            }
        }

        $target[] = $value;
    }
}
