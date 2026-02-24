<?php

// On importe les classes nécessaires pour créer une migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nouvelle migration pour créer la table des tokens API (Laravel Sanctum)
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute avec : php artisan migrate
     * Elle crée la table personal_access_tokens
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {

            // Clé primaire auto-incrémentée
            $table->id();

            // Colonnes polymorphiques (tokenable_id + tokenable_type)
            // Permet d’associer le token à un modèle (ex: User)
            $table->morphs('tokenable');

            // Nom du token (ex: auth_token, mobile_app...)
            $table->text('name');

            // Token unique (clé secrète utilisée pour l’authentification API)
            $table->string('token', 64)->unique();

            // Permissions du token (ex: read, write...) en JSON
            $table->text('abilities')->nullable();

            // Date de dernière utilisation du token
            $table->timestamp('last_used_at')->nullable();

            // Date d’expiration du token (si définie)
            $table->timestamp('expires_at')->nullable()->index();

            // created_at et updated_at
            $table->timestamps();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute avec : php artisan migrate:rollback
     * Elle supprime la table
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
