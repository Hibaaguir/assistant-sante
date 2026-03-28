<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnsToDrop = [];

        if (Schema::hasColumn('profils_sante', 'prend_medicament')) {
            $columnsToDrop[] = 'prend_medicament';
        }

        if (Schema::hasColumn('profils_sante', 'nom_medicament')) {
            $columnsToDrop[] = 'nom_medicament';
        }

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('profils_sante', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }

    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            if (! Schema::hasColumn('profils_sante', 'prend_medicament')) {
                $table->boolean('prend_medicament')->default(false);
            }

            if (! Schema::hasColumn('profils_sante', 'nom_medicament')) {
                $table->string('nom_medicament')->nullable();
            }
        });
    }
};
