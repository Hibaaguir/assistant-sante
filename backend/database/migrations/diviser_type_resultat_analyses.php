<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_lab_results', function (Blueprint $table) {
            if (! Schema::hasColumn('health_lab_results', 'analysis_result')) {
                $table->string('analysis_result', 120)->nullable()->after('analysis_type');
            }
        });

        $rows = DB::table('health_lab_results')
            ->select('id', 'analysis_type')
            ->get();

        foreach ($rows as $row) {
            $raw = trim((string) ($row->analysis_type ?? ''));
            if ($raw === '') {
                continue;
            }

            $parts = preg_split('/\s*-\s*/u', $raw, 2);
            $type = trim((string) ($parts[0] ?? $raw));
            $result = trim((string) ($parts[1] ?? $raw));

            if ($type === '') {
                $type = $raw;
            }
            if ($result === '') {
                $result = $raw;
            }

            DB::table('health_lab_results')
                ->where('id', $row->id)
                ->update([
                    'analysis_type' => $type,
                    'analysis_result' => $result,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('health_lab_results', 'analysis_result')) {
            $rows = DB::table('health_lab_results')
                ->select('id', 'analysis_type', 'analysis_result')
                ->get();

            foreach ($rows as $row) {
                $type = trim((string) ($row->analysis_type ?? ''));
                $result = trim((string) ($row->analysis_result ?? ''));
                $combined = $result !== '' ? "{$type} - {$result}" : $type;

                DB::table('health_lab_results')
                    ->where('id', $row->id)
                    ->update(['analysis_type' => $combined]);
            }
        }

        Schema::table('health_lab_results', function (Blueprint $table) {
            if (Schema::hasColumn('health_lab_results', 'analysis_result')) {
                $table->dropColumn('analysis_result');
            }
        });
    }
};
