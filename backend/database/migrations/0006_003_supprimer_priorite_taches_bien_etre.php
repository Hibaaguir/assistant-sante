<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('taches_bien_etre') && Schema::hasColumn('taches_bien_etre', 'priorite')) {
            Schema::table('taches_bien_etre', function (Blueprint $table): void {
                $table->dropColumn('priorite');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('taches_bien_etre') && ! Schema::hasColumn('taches_bien_etre', 'priorite')) {
            Schema::table('taches_bien_etre', function (Blueprint $table): void {
                $table->enum('priorite', ['faible', 'moyen', 'eleve'])->default('moyen')->after('categorie');
            });
        }
    }
};
