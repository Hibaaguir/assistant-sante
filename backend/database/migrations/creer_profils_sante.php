<?php

// On importe les classes nécessaires pour créer une migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Création d’une nouvelle migration
return new class extends Migration
{
    /**
     * Méthode up()
     * Elle s’exécute quand on lance : php artisan migrate
     * Elle sert à créer la table profils_sante
     */
    public function up(): void
    {
        // Création de la table profils_sante
        Schema::create('profils_sante', function (Blueprint $table) {

            // Clé primaire auto-incrémentée
            $table->id();

            // Clé étrangère liée à la table users
            // Chaque profil appartient à un utilisateur
            $table->foreignId('user_id')
                  ->constrained() // référence automatiquement la table users
                  ->onDelete('cascade'); 
                  // Si on supprime l’utilisateur, son profil est supprimé automatiquement


            // Âge de l’utilisateur
            $table->integer('age');

            // Sexe (valeur limitée à homme ou femme)
            $table->enum('sexe', ['homme', 'femme']);

            // Taille (ex: 170 cm)
            $table->float('taille');

            // Poids (ex: 65 kg)
            $table->float('poids');

            // Groupe sanguin (A+, O-, etc.)
            $table->string('groupe_sanguin');

            // Objectif (ex: perdre du poids, prise de masse…)
            $table->string('objectif');

            // -------- Étape 2 : Informations médicales --------

            // Liste des allergies (stockée en format JSON)
            $table->json('allergies')->nullable();

            // Liste des maladies chroniques (JSON)
            $table->json('maladies_chroniques')->nullable();

            // Liste des traitements (JSON)
            $table->json('traitements')->nullable();

            // Indique si la personne prend un médicament
            $table->boolean('prend_medicament')->default(false);

            // Nom du médicament (facultatif)
            $table->string('nom_medicament')->nullable();

            // Indique si la personne est fumeur
            $table->boolean('fumeur')->default(false);

            // Indique si la personne consomme de l’alcool
            $table->boolean('alcool')->default(false);

            // created_at et updated_at (gérés automatiquement par Laravel)
            $table->timestamps();
        });
    }

    /**
     * Méthode down()
     * Elle s’exécute quand on lance : php artisan migrate:rollback
     * Elle sert à supprimer la table
     */
    public function down(): void
    {
        Schema::dropIfExists('profils_sante');
    }
};
