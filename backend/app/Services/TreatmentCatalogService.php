<?php

namespace App\Services;

use App\Models\TreatmentCatalogItem;

class TreatmentCatalogService
{
    public function buildCatalog(): array
    {
        $items = TreatmentCatalogItem::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get(['type', 'name']);

        $types = [];
        $namesByType = [];

        foreach ($items as $item) {
            $type = $this->normalizeText($item->type);
            if ($type === null) {
                continue;
            }

            $this->appendUniqueValue($types, $type);

            $name = $this->normalizeText($item->name);
            if ($name === null) {
                continue;
            }

            if (! array_key_exists($type, $namesByType)) {
                $namesByType[$type] = [];
            }

            $this->appendUniqueValue($namesByType[$type], $name);
        }

        usort($types, fn (string $a, string $b) => strcasecmp($a, $b));
        foreach ($namesByType as &$names) {
            usort($names, fn (string $a, string $b) => strcasecmp($a, $b));
        }
        unset($names);

        uksort($namesByType, fn (string $a, string $b) => strcasecmp($a, $b));

        return [
            'types' => $types,
            'names_by_type' => $namesByType,
        ];
    }

    public function saveEntry(?string $type, ?string $name = null, ?int $createdByUserId = null): void
    {
        $normalizedType = $this->normalizeText($type);
        if ($normalizedType === null) {
            return;
        }

        $normalizedName = $this->normalizeText($name) ?? '';
        $lowerType = mb_strtolower($normalizedType, 'UTF-8');
        $lowerName = mb_strtolower($normalizedName, 'UTF-8');

        $existing = TreatmentCatalogItem::query()
            ->whereRaw('LOWER(type) = ?', [$lowerType])
            ->whereRaw('LOWER(name) = ?', [$lowerName])
            ->first();

        if ($existing) {
            if ($existing->created_by_user_id === null && $createdByUserId !== null) {
                $existing->update(['created_by_user_id' => $createdByUserId]);
            }
            return;
        }

        TreatmentCatalogItem::query()->create([
            'type' => $normalizedType,
            'name' => $normalizedName,
            'created_by_user_id' => $createdByUserId,
        ]);
    }

    public function saveFromTreatments(array $treatments, ?int $createdByUserId = null): void
    {
        foreach ($treatments as $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;
            $name = $item['name'] ?? null;

            $this->saveEntry($type, null, $createdByUserId);
            $this->saveEntry($type, $name, $createdByUserId);
        }
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return $normalized !== '' ? $normalized : null;
    }

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
