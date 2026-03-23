<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            if (! Schema::hasColumn('profils_sante', 'frequence_activite_physique')) {
                $table->string('frequence_activite_physique', 30)->nullable()->after('activites_physiques');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            if (Schema::hasColumn('profils_sante', 'frequence_activite_physique')) {
                $table->dropColumn('frequence_activite_physique');
            }
        });
    }
};
