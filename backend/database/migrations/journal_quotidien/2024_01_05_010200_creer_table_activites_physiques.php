<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activites_physiques')) {
            Schema::create('activites_physiques', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_journal_quotidien')->constrained('journal_quotidien')->cascadeOnDelete();
                
                // Données d'activité physique
                $table->string('type_activite', 120)->nullable(); // ex: "course", "natation", "musculation"
                $table->unsignedSmallInteger('duree_activite')->nullable(); // durée en minutes
                $table->enum('intensite', ['faible', 'moyenne', 'elevee'])->default('moyenne'); // intensité
                
                // Métadonnées
                $table->timestamps();
                
                // Index pour les requêtes fréquentes
                $table->index('id_journal_quotidien');
                $table->index('type_activite');
                $table->index('intensite');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activites_physiques');
    }
};