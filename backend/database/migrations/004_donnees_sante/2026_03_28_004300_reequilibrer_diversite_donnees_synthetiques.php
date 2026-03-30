<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $treatmentNamesByType = $this->loadTreatmentNamesByType();
        $allTreatmentPairs = $this->buildTreatmentPairs($treatmentNamesByType);

        if (Schema::hasTable('health_treatment_checks') && $allTreatmentPairs !== []) {
            $this->rebalanceTreatmentChecks($treatmentNamesByType, $allTreatmentPairs);
        }

        if (Schema::hasTable('profils_sante')) {
            $this->rebalanceProfiles(
                $treatmentNamesByType,
                $this->loadNamePool('allergy_catalog_items'),
                $this->loadNamePool('chronic_disease_catalog_items'),
            );
        }
    }

    public function down(): void
    {
        // No-op: diversification rebalance intentionally irreversible.
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function loadTreatmentNamesByType(): array
    {
        if (! Schema::hasTable('treatment_catalog_items')) {
            return [];
        }

        $rows = DB::table('treatment_catalog_items')
            ->where('name', 'not like', 'Traitement %')
            ->orderBy('type')
            ->orderBy('name')
            ->get(['type', 'name']);

        $result = [];

        foreach ($rows as $row) {
            $type = $this->normalizeText($row->type ?? null);
            $name = $this->normalizeText($row->name ?? null);

            if ($type === null || $name === null) {
                continue;
            }

            $result[$type] ??= [];
            if (! in_array($name, $result[$type], true)) {
                $result[$type][] = $name;
            }
        }

        return array_filter($result, static fn (array $names) => $names !== []);
    }

    /**
     * @param array<string, array<int, string>> $namesByType
     * @return array<int, array{type:string,name:string}>
     */
    private function buildTreatmentPairs(array $namesByType): array
    {
        $pairs = [];

        foreach ($namesByType as $type => $names) {
            foreach ($names as $name) {
                $pairs[] = ['type' => $type, 'name' => $name];
            }
        }

        return $pairs;
    }

    /**
     * @param array<string, array<int, string>> $namesByType
     * @param array<int, array{type:string,name:string}> $allPairs
     */
    private function rebalanceTreatmentChecks(array $namesByType, array $allPairs): void
    {
        $typeOffsets = [];

        foreach ($namesByType as $type => $names) {
            DB::table('health_treatment_checks')
                ->select('id', 'medication_key', 'treatment_name')
                ->where('treatment_type', $type)
                ->orderBy('id')
                ->chunkById(1000, function ($rows) use (&$typeOffsets, $type, $names): void {
                    foreach ($rows as $row) {
                        $offset = $typeOffsets[$type] ?? 0;
                        $name = $names[$offset % count($names)];
                        $typeOffsets[$type] = $offset + 1;

                        $suffix = $this->extractSuffix((string) $row->medication_key);
                        $base = Str::slug($name);
                        if ($base === '') {
                            $base = 'traitement';
                        }

                        DB::table('health_treatment_checks')
                            ->where('id', $row->id)
                            ->update([
                                'treatment_name' => $name,
                                'medication_key' => $base . '-' . $suffix,
                                'updated_at' => now(),
                            ]);
                    }
                });
        }

        $pairOffset = 0;

        DB::table('health_treatment_checks')
            ->select('id', 'medication_key', 'treatment_type', 'treatment_name')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use (&$pairOffset, $allPairs, $namesByType): void {
                foreach ($rows as $row) {
                    $type = $this->normalizeText($row->treatment_type ?? null);
                    $name = $this->normalizeText($row->treatment_name ?? null);

                    if ($type !== null && isset($namesByType[$type]) && $name !== null && in_array($name, $namesByType[$type], true)) {
                        continue;
                    }

                    $pair = $allPairs[$pairOffset % count($allPairs)];
                    $pairOffset++;

                    $suffix = $this->extractSuffix((string) $row->medication_key);
                    $base = Str::slug($pair['name']);
                    if ($base === '') {
                        $base = 'traitement';
                    }

                    DB::table('health_treatment_checks')
                        ->where('id', $row->id)
                        ->update([
                            'treatment_type' => $pair['type'],
                            'treatment_name' => $pair['name'],
                            'medication_key' => $base . '-' . $suffix,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * @param array<string, array<int, string>> $treatmentNamesByType
     * @param array<int, string> $allergyPool
     * @param array<int, string> $diseasePool
     */
    private function rebalanceProfiles(array $treatmentNamesByType, array $allergyPool, array $diseasePool): void
    {
        $allergyOffset = 0;
        $diseaseOffset = 0;
        $treatmentOffsets = [];

        DB::table('profils_sante')
            ->select('id', 'traitements', 'allergies', 'maladies_chroniques')
            ->orderBy('id')
            ->chunkById(300, function ($profiles) use ($treatmentNamesByType, $allergyPool, $diseasePool, &$allergyOffset, &$diseaseOffset, &$treatmentOffsets): void {
                foreach ($profiles as $profile) {
                    $traitements = json_decode((string) ($profile->traitements ?? '[]'), true);
                    $allergies = json_decode((string) ($profile->allergies ?? '[]'), true);
                    $maladies = json_decode((string) ($profile->maladies_chroniques ?? '[]'), true);

                    if (! is_array($traitements)) {
                        $traitements = [];
                    }
                    if (! is_array($allergies)) {
                        $allergies = [];
                    }
                    if (! is_array($maladies)) {
                        $maladies = [];
                    }

                    $newTraitements = [];
                    foreach ($traitements as $item) {
                        if (! is_array($item)) {
                            continue;
                        }

                        $type = $this->normalizeText($item['type'] ?? null);
                        if ($type === null || ! isset($treatmentNamesByType[$type])) {
                            continue;
                        }

                        $names = $treatmentNamesByType[$type];
                        $offset = $treatmentOffsets[$type] ?? 0;
                        $name = $names[$offset % count($names)];
                        $treatmentOffsets[$type] = $offset + 1;

                        $item['type'] = $type;
                        $item['name'] = $name;
                        $newTraitements[] = $item;
                    }

                    $allergiesCount = count(array_filter($allergies, fn ($v) => $this->normalizeText($v) !== null));
                    $maladiesCount = count(array_filter($maladies, fn ($v) => $this->normalizeText($v) !== null));

                    $newAllergies = $allergiesCount > 0
                        ? $this->takeRotatingUnique($allergyPool, $allergyOffset, min($allergiesCount, max(1, min(3, count($allergyPool)))))
                        : [];

                    $newMaladies = $maladiesCount > 0
                        ? $this->takeRotatingUnique($diseasePool, $diseaseOffset, min($maladiesCount, max(1, min(3, count($diseasePool)))))
                        : [];

                    DB::table('profils_sante')
                        ->where('id', $profile->id)
                        ->update([
                            'traitements' => json_encode($newTraitements),
                            'allergies' => json_encode($newAllergies),
                            'maladies_chroniques' => json_encode($newMaladies),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * @return array<int, string>
     */
    private function loadNamePool(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($v) => $this->normalizeText($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $pool
     * @return array<int, string>
     */
    private function takeRotatingUnique(array $pool, int &$offset, int $count): array
    {
        if ($pool === [] || $count <= 0) {
            return [];
        }

        $count = min($count, count($pool));
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $pool[($offset + $i) % count($pool)];
        }

        $offset += $count;

        return array_values(array_unique($result));
    }

    private function extractSuffix(string $medicationKey): string
    {
        if (preg_match('/-(\d+(?:-\d+)?)$/', $medicationKey, $matches) === 1) {
            return $matches[1];
        }

        return '1';
    }

    private function normalizeText(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return $normalized === '' ? null : $normalized;
    }
};
