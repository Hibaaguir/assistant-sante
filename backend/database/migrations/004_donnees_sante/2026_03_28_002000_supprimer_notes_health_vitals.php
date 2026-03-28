<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('health_vitals', 'notes')) {
            return;
        }

        Schema::table('health_vitals', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('health_vitals', 'notes')) {
            return;
        }

        Schema::table('health_vitals', function (Blueprint $table) {
            $table->string('notes', 1000)->nullable();
        });
    }
};
