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
     * Elle s’exécute avec : php artisan migrate
     * Ici on ajoute deux nouvelles colonnes
     */
    public function up(): void
    {
        if (!Schema::hasTable('profils_sante')) {
            return;
        }

        // On modifie la table profils_sante
        Schema::table('profils_sante', function (Blueprint $table) {

            // Ajout d’une colonne consulte_medecin
            // Type boolean (true/false)
            // default(false) → valeur par défaut = false
            // after('alcool') → placée après la colonne alcool
            if (!Schema::hasColumn('profils_sante', 'consulte_medecin')) {
                $table->boolean('consulte_medecin')
                      ->default(false)
                      ->after('alcool');
            }

            // Ajout d’une colonne medecin_email
            // Type string
            // nullable() → peut être vide
            // placée après consulte_medecin
            if (!Schema::hasColumn('profils_sante', 'medecin_email')) {
                $table->string('medecin_email')
                      ->nullable()
                      ->after('consulte_medecin');
            }
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute avec : php artisan migrate:rollback
     * Elle annule les modifications
     */
    public function down(): void
    {
        if (!Schema::hasTable('profils_sante')) {
            return;
        }

        // On supprime les deux colonnes ajoutées
        Schema::table('profils_sante', function (Blueprint $table) {
            if (Schema::hasColumn('profils_sante', 'consulte_medecin')) {
                $table->dropColumn('consulte_medecin');
            }
            if (Schema::hasColumn('profils_sante', 'medecin_email')) {
                $table->dropColumn('medecin_email');
            }
        });
    }
};
