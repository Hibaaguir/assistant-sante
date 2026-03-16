<?php

// Import des classes nécessaires pour créer et gérer une migration Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Déclaration d'une migration anonyme pour créer la table journal_entries
return new class extends Migration
{
    // Méthode exécutée lors de la migration pour créer la table
    public function up(): void
    {
        // Vérifie si la table n'existe pas déjà avant de la créer
        if (! Schema::hasTable('journal_entries')) {
            
            // Création de la table journal_entries avec ses colonnes
            Schema::create('journal_entries', function (Blueprint $table) {
                
                // Clé primaire auto-incrémentée de la table
                $table->id();

                // Clé étrangère vers la table users ; si l'utilisateur est supprimé, ses entrées le seront aussi
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // Date de l'entrée avec index pour améliorer la vitesse de recherche
                $table->date('entry_date')->index();

                // Informations liées à l'état général de l'utilisateur
                $table->unsignedTinyInteger('sleep')->nullable();
                $table->unsignedTinyInteger('stress')->nullable();
                $table->unsignedTinyInteger('energy')->nullable();
                $table->enum('sugar', ['low', 'medium', 'high'])->default('low');
                $table->unsignedTinyInteger('caffeine')->default(0);
                $table->decimal('hydration', 4, 1)->default(0.0);

                // Informations liées à l'alimentation et à l'activité physique
                $table->json('meals')->nullable();
                $table->string('activity_type', 120)->nullable();
                $table->unsignedSmallInteger('activity_duration')->nullable();
                $table->enum('intensity', ['light', 'medium', 'high'])->default('medium');

                // Informations liées au tabac et à l'alcool
                $table->boolean('tobacco')->default(false);
                $table->boolean('alcohol')->default(false);
                $table->json('tobacco_types')->nullable();
                $table->unsignedSmallInteger('cigarettes_per_day')->nullable();
                $table->string('vape_frequency', 50)->nullable();
                $table->unsignedSmallInteger('vape_liquid_ml')->nullable();
                $table->unsignedSmallInteger('alcohol_drinks')->nullable();

                // Colonnes created_at et updated_at ajoutées automatiquement par Laravel
                $table->timestamps();

                // Empêche un même utilisateur d'avoir deux entrées pour la même date
                $table->unique(['user_id', 'entry_date']);
            });
        }
    }

    // Méthode exécutée lors du rollback pour supprimer la table
    public function down(): void
    {
        // Supprime la table si elle existe
        Schema::dropIfExists('journal_entries');
    }
};