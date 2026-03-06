<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_lab_results') && Schema::hasColumn('health_lab_results', 'notes')) {
            Schema::table('health_lab_results', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (Schema::hasTable('health_vitals') && Schema::hasColumn('health_vitals', 'notes')) {
            Schema::table('health_vitals', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('health_lab_results') && ! Schema::hasColumn('health_lab_results', 'notes')) {
            Schema::table('health_lab_results', function (Blueprint $table) {
                $table->string('notes', 1000)->nullable();
            });
        }

        if (Schema::hasTable('health_vitals') && ! Schema::hasColumn('health_vitals', 'notes')) {
            Schema::table('health_vitals', function (Blueprint $table) {
                $table->string('notes', 1000)->nullable();
            });
        }
    }
};
