<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profils_sante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnDelete();

            // Informations physiques
            $table->enum('genre', ['homme', 'femme'])->nullable();
            $table->float('taille')->nullable();
            $table->float('poids')->nullable();
            $table->string('groupe_sanguin')->nullable();

            // Objectifs & antécédents
            $table->json('objectifs')->nullable();
            $table->json('allergies')->nullable();
            $table->json('maladies_chroniques')->nullable();

            // Style de vie
            $table->boolean('fumeur')->default(false);
            $table->boolean('alcoolique')->default(false);

            // Suivi médecin
            $table->boolean('consulte_medecin')->default(false);
            $table->boolean('medecin_peut_consulter')->default(false);
            $table->string('medecin_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profils_sante');
    }
};