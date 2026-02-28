<?php

// On importe les classes nécessaires pour créer des migrations
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// On crée une nouvelle migration
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute quand on lance : php artisan migrate
     * Elle sert à créer les tables dans la base de données
     */
    public function up(): void
    {
        // Création de la table users
        Schema::create('users', function (Blueprint $table) {

            // id auto-incrémenté (clé primaire)
            $table->id();

            // Nom de l’utilisateur
            $table->string('name');

            // Email unique (ne peut pas être répété)
            $table->string('email')->unique();

            // Date de vérification de l’email (peut être vide au début)
            $table->timestamp('email_verified_at')->nullable();

            // Mot de passe
            $table->string('password');

            // Token utilisé pour "remember me"
            $table->rememberToken();

            // created_at et updated_at (gérés automatiquement par Laravel)
            $table->timestamps();
        });

        // Table pour réinitialiser les mots de passe
        Schema::create('password_reset_tokens', function (Blueprint $table) {

            // Email comme clé primaire
            $table->string('email')->primary();

            // Token envoyé par email pour changer le mot de passe
            $table->string('token');

            // Date de création du token
            $table->timestamp('created_at')->nullable();
        });

        // Table pour gérer les sessions des utilisateurs
        Schema::create('sessions', function (Blueprint $table) {

            // ID unique de la session
            $table->string('id')->primary();

            // ID de l’utilisateur (peut être vide si visiteur non connecté)
            $table->foreignId('user_id')->nullable()->index();

            // Adresse IP de l’utilisateur
            $table->string('ip_address', 45)->nullable();

            // Informations sur le navigateur (Chrome, Firefox, etc.)
            $table->text('user_agent')->nullable();

            // Données de la session
            $table->longText('payload');

            // Dernière activité de la session
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle sert à supprimer les tables
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
