<?php

// On importe les classes nécessaires pour créer des migrations
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour créer les tables du cache
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute avec : php artisan migrate
     * Ici on crée les tables cache et cache_locks
     */
    public function up(): void
    {
        // Création de la table "cache"
        Schema::create('cache', function (Blueprint $table) {

            // Clé unique du cache (identifiant principal)
            $table->string('key')->primary();

            // Valeur stockée dans le cache
            // mediumText permet de stocker beaucoup de texte
            $table->mediumText('value');

            // Date d’expiration du cache (timestamp)
            // index() permet de rechercher plus rapidement
            $table->integer('expiration')->index();
        });

        // Création de la table "cache_locks"
        Schema::create('cache_locks', function (Blueprint $table) {

            // Clé unique du verrou
            $table->string('key')->primary();

            // Identifie qui possède le verrou
            $table->string('owner');

            // Date d’expiration du verrou
            $table->integer('expiration')->index();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute avec : php artisan migrate:rollback
     * Elle supprime les tables créées
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};

