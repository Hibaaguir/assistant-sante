<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('health_treatment_checks')) {
            return;
        }

        if (! Schema::hasColumn('health_treatment_checks', 'treatment_name')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->string('treatment_name', 255)->nullable()->after('medication_key');
            });
        }

        if (Schema::hasColumn('health_treatment_checks', 'medication_name')) {
            DB::table('health_treatment_checks')
                ->where(function ($query): void {
                    $query->whereNull('treatment_name')->orWhere('treatment_name', '');
                })
                ->update([
                    'treatment_name' => DB::raw('medication_name'),
                ]);
        }

        if (! Schema::hasColumn('health_treatment_checks', 'treatment_type')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->string('treatment_type', 120)->nullable()->after('check_date');
            });
        }

        if (! Schema::hasTable('treatment_catalog_items')) {
            return;
        }

        $catalogRows = DB::table('treatment_catalog_items')
            ->where('name', 'not like', 'Traitement %')
            ->select('type', 'name')
            ->get();

        $typeByName = [];
        $defaultType = null;
        $defaultName = null;

        foreach ($catalogRows as $row) {
            $type = $this->normalizeText($row->type ?? null);
            $name = $this->normalizeText($row->name ?? null);
            if ($type === null || $name === null) {
                continue;
            }

            $typeByName[$name] = $type;
            if ($defaultType === null) {
                $defaultType = $type;
                $defaultName = $name;
            }
        }

        DB::table('health_treatment_checks')
            ->select('id', 'treatment_name', 'treatment_type')
            ->orderBy('id')
            ->chunkById(500, function ($checks) use ($typeByName, $defaultType, $defaultName): void {
                foreach ($checks as $check) {
                    $name = $this->normalizeText($check->treatment_name ?? null);
                    $type = $this->normalizeText($check->treatment_type ?? null);

                    $nextName = $name;
                    $nextType = $type;

                    if ($name !== null && isset($typeByName[$name])) {
                        $nextType = $typeByName[$name];
                    } elseif ($defaultName !== null && $defaultType !== null) {
                        $nextName = $defaultName;
                        $nextType = $defaultType;
                    }

                    if ($nextName === null || $nextType === null) {
                        continue;
                    }

                    if ($nextName !== $name || $nextType !== $type) {
                        DB::table('health_treatment_checks')
                            ->where('id', $check->id)
                            ->update([
                                'treatment_name' => $nextName,
                                'treatment_type' => $nextType,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        if (Schema::hasColumn('health_treatment_checks', 'medication_name')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->dropColumn('medication_name');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('health_treatment_checks')) {
            return;
        }

        if (! Schema::hasColumn('health_treatment_checks', 'medication_name')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->string('medication_name', 255)->nullable()->after('medication_key');
            });
        }

        if (Schema::hasColumn('health_treatment_checks', 'treatment_name')) {
            DB::table('health_treatment_checks')
                ->where(function ($query): void {
                    $query->whereNull('medication_name')->orWhere('medication_name', '');
                })
                ->update([
                    'medication_name' => DB::raw('treatment_name'),
                ]);
        }

        if (Schema::hasColumn('health_treatment_checks', 'treatment_type')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->dropColumn('treatment_type');
            });
        }

        if (Schema::hasColumn('health_treatment_checks', 'treatment_name')) {
            Schema::table('health_treatment_checks', function (Blueprint $table): void {
                $table->dropColumn('treatment_name');
            });
        }
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
