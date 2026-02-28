<?php

// On importe les classes nécessaires pour modifier une table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour modifier une colonne existante
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute quand on lance : php artisan migrate
     * Ici on modifie une colonne existante
     */
    public function up(): void
    {
        // On modifie la table profils_sante
        Schema::table('profils_sante', function (Blueprint $table) {

            // On rend la colonne "objectif" nullable
            // Cela signifie que cette colonne peut être vide (NULL)
            $table->string('objectif')->nullable()->change();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle annule la modification
     */
    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {

            // On remet la colonne "objectif" comme obligatoire (NOT NULL)
            $table->string('objectif')->nullable(false)->change();
        });
    }
};

