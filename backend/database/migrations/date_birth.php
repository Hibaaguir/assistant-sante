<?php

// On importe les classes nécessaires pour modifier une table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour modifier la table users
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute quand on lance : php artisan migrate
     * Ici on modifie une table existante
     */
    public function up(): void
    {
        // On modifie la table users
        Schema::table('users', function (Blueprint $table) {

            // On ajoute une nouvelle colonne date_of_birth
            // Type : date
            // nullable() → peut être vide
            // after('email') → sera placée après la colonne email
            $table->date('date_of_birth')->nullable()->after('email');
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle annule les modifications
     */
    public function down(): void
    {
        // On supprime la colonne date_of_birth
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }
};

