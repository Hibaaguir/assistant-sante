<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_entries')) {
            return;
        }

        if (! Schema::hasColumn('journal_entries', 'puffs_per_day')) {
            Schema::table('journal_entries', function (Blueprint $table): void {
                $table->unsignedSmallInteger('puffs_per_day')->nullable()->after('cigarettes_per_day');
            });
        }

        if (Schema::hasColumn('journal_entries', 'vape_liquid_ml')) {
            DB::table('journal_entries')
                ->whereNotNull('vape_liquid_ml')
                ->where(function ($query): void {
                    $query->whereNull('puffs_per_day')->orWhere('puffs_per_day', 0);
                })
                ->update([
                    'puffs_per_day' => DB::raw('vape_liquid_ml'),
                ]);
        }

        $hasVapeFrequency = Schema::hasColumn('journal_entries', 'vape_frequency');
        $hasVapeLiquid = Schema::hasColumn('journal_entries', 'vape_liquid_ml');

        if ($hasVapeFrequency || $hasVapeLiquid) {
            Schema::table('journal_entries', function (Blueprint $table) use ($hasVapeFrequency, $hasVapeLiquid): void {
                $toDrop = [];
                if ($hasVapeFrequency) {
                    $toDrop[] = 'vape_frequency';
                }
                if ($hasVapeLiquid) {
                    $toDrop[] = 'vape_liquid_ml';
                }

                $table->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('journal_entries')) {
            return;
        }

        if (! Schema::hasColumn('journal_entries', 'vape_frequency')) {
            Schema::table('journal_entries', function (Blueprint $table): void {
                $table->string('vape_frequency', 50)->nullable()->after('cigarettes_per_day');
            });
        }

        if (! Schema::hasColumn('journal_entries', 'vape_liquid_ml')) {
            Schema::table('journal_entries', function (Blueprint $table): void {
                $table->unsignedSmallInteger('vape_liquid_ml')->nullable()->after('vape_frequency');
            });
        }

        if (Schema::hasColumn('journal_entries', 'puffs_per_day')) {
            DB::table('journal_entries')
                ->whereNotNull('puffs_per_day')
                ->whereNull('vape_liquid_ml')
                ->update([
                    'vape_liquid_ml' => DB::raw('puffs_per_day'),
                ]);

            Schema::table('journal_entries', function (Blueprint $table): void {
                $table->dropColumn('puffs_per_day');
            });
        }
    }
};
