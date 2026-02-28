<?php

// On importe les classes nécessaires pour modifier une table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour modifier la table profils_sante
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute quand on lance : php artisan migrate
     * Ici on ajoute une nouvelle colonne dans une table existante
     */
    public function up(): void
    {
        if (!Schema::hasTable('profils_sante')) {
            return;
        }

        if (Schema::hasColumn('profils_sante', 'objectifs')) {
            return;
        }

        // On modifie la table profils_sante
        Schema::table('profils_sante', function (Blueprint $table) {

            // On ajoute une colonne "objectifs"
            // Type JSON → pour stocker plusieurs objectifs sous forme de liste
            // nullable() → peut être vide
            // after('objectif') → placée après la colonne "objectif"
            $table->json('objectifs')->nullable()->after('objectif');
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle permet d’annuler la modification
     */
    public function down(): void
    {
        if (!Schema::hasTable('profils_sante') || !Schema::hasColumn('profils_sante', 'objectifs')) {
            return;
        }

        // On supprime la colonne "objectifs"
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->dropColumn('objectifs');
        });
    }
};

