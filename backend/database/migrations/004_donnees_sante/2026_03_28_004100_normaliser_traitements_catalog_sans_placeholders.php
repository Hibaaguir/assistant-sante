<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_catalog_items')) {
            return;
        }

        $catalogRows = DB::table('treatment_catalog_items')->select('type', 'name')->get();

        $namesByType = [];
        $typeByName = [];

        foreach ($catalogRows as $row) {
            $type = $this->normalizeText($row->type ?? null);
            $name = $this->normalizeText($row->name ?? null);
            if ($type === null || $name === null || $this->isPlaceholder($name)) {
                continue;
            }

            $namesByType[$type] ??= [];
            if (! in_array($name, $namesByType[$type], true)) {
                $namesByType[$type][] = $name;
            }

            if (! isset($typeByName[$name])) {
                $typeByName[$name] = $type;
            }
        }

        if ($namesByType === [] || $typeByName === []) {
            return;
        }

        $defaultName = array_key_first($typeByName);

        if (Schema::hasTable('profils_sante')) {
            DB::table('profils_sante')
                ->select('id', 'traitements')
                ->orderBy('id')
                ->chunkById(200, function ($profiles) use ($namesByType, $typeByName): void {
                    foreach ($profiles as $profile) {
                        $items = json_decode((string) ($profile->traitements ?? '[]'), true);
                        if (! is_array($items)) {
                            continue;
                        }

                        $normalized = [];

                        foreach ($items as $item) {
                            if (! is_array($item)) {
                                continue;
                            }

                            $type = $this->normalizeText($item['type'] ?? null);
                            $name = $this->normalizeText($item['name'] ?? null);

                            if ($type !== null && isset($namesByType[$type])) {
                                if ($name === null || $this->isPlaceholder($name) || ! in_array($name, $namesByType[$type], true)) {
                                    $name = $namesByType[$type][0] ?? null;
                                }
                            } elseif ($name !== null && isset($typeByName[$name])) {
                                $type = $typeByName[$name];
                            } else {
                                continue;
                            }

                            if ($type === null || $name === null) {
                                continue;
                            }

                            $item['type'] = $type;
                            $item['name'] = $name;
                            $normalized[] = $item;
                        }

                        if ($normalized !== $items) {
                            DB::table('profils_sante')
                                ->where('id', $profile->id)
                                ->update([
                                    'traitements' => json_encode($normalized),
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                });
        }

        if (! Schema::hasTable('health_treatment_checks')) {
            return;
        }

        $validNames = array_keys($typeByName);

        DB::table('health_treatment_checks')
            ->select('id', 'medication_key', 'medication_name')
            ->orderBy('id')
            ->chunkById(500, function ($checks) use ($validNames, $defaultName): void {
                foreach ($checks as $check) {
                    $name = $this->normalizeText($check->medication_name ?? null);
                    if ($name !== null && in_array($name, $validNames, true) && ! $this->isPlaceholder($name)) {
                        continue;
                    }

                    if ($defaultName === null) {
                        continue;
                    }

                    $suffix = '1';
                    if (preg_match('/-(\d+(?:-\d+)?)$/', (string) $check->medication_key, $matches) === 1) {
                        $suffix = $matches[1];
                    }

                    $keyBase = Str::slug($defaultName);
                    if ($keyBase === '') {
                        $keyBase = 'traitement';
                    }

                    DB::table('health_treatment_checks')
                        ->where('id', $check->id)
                        ->update([
                            'medication_name' => $defaultName,
                            'medication_key' => $keyBase . '-' . $suffix,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No-op.
    }

    private function normalizeText(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return $normalized === '' ? null : $normalized;
    }

    private function isPlaceholder(string $name): bool
    {
        return preg_match('/^Traitement\s+/i', $name) === 1;
    }
};
