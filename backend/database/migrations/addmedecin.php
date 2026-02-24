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
     * Ici on ajoute une nouvelle colonne
     */
    public function up(): void
    {
        if (!Schema::hasTable('profils_sante') || Schema::hasColumn('profils_sante', 'medecin_peut_consulter')) {
            return;
        }

        // On modifie la table profils_sante
        Schema::table('profils_sante', function (Blueprint $table) {

            // Ajout d’une colonne medecin_peut_consulter
            // Type boolean (true/false)
            // default(false) → valeur par défaut = false
            // after('medecin_email') → placée après la colonne medecin_email
            $table->boolean('medecin_peut_consulter')
                  ->default(false)
                  ->after('medecin_email');
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle permet d’annuler la modification
     */
    public function down(): void
    {
        if (!Schema::hasTable('profils_sante') || !Schema::hasColumn('profils_sante', 'medecin_peut_consulter')) {
            return;
        }

        // On supprime la colonne ajoutée
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->dropColumn('medecin_peut_consulter');
        });
    }
};

