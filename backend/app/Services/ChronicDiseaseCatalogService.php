<?php

namespace App\Services;

use App\Models\ChronicDiseaseCatalogItem;

class ChronicDiseaseCatalogService
{
    public function buildCatalog(): array
    {
        $items = ChronicDiseaseCatalogItem::query()
            ->orderBy('name')
            ->get(['name']);

        $catalogItems = [];

        foreach ($items as $item) {
            $name = $this->normalizeText($item->name);
            if ($name === null) {
                continue;
            }

            $this->appendUniqueValue($catalogItems, $name);
        }

        usort($catalogItems, fn (string $a, string $b) => strcasecmp($a, $b));

        return [
            'items' => $catalogItems,
        ];
    }

    public function saveEntry(mixed $name, ?int $createdByUserId = null): void
    {
        $normalizedName = $this->normalizeText($name);
        if ($normalizedName === null) {
            return;
        }

        $lowerName = mb_strtolower($normalizedName, 'UTF-8');

        $existing = ChronicDiseaseCatalogItem::query()
            ->whereRaw('LOWER(name) = ?', [$lowerName])
            ->first();

        if ($existing) {
            if ($existing->created_by_user_id === null && $createdByUserId !== null) {
                $existing->update(['created_by_user_id' => $createdByUserId]);
            }
            return;
        }

        ChronicDiseaseCatalogItem::query()->create([
            'name' => $normalizedName,
            'created_by_user_id' => $createdByUserId,
        ]);
    }

    public function saveFromList(array $items, ?int $createdByUserId = null): void
    {
        foreach ($items as $item) {
            $this->saveEntry($item, $createdByUserId);
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
