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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Informations physiques
            $table->enum('sexe', ['homme', 'femme']);
            $table->float('taille');
            $table->float('poids');
            $table->string('groupe_sanguin');

            // Objectifs & antécédents
            $table->string('objectif')->nullable();
            $table->json('objectifs')->nullable();
            $table->json('allergies')->nullable();
            $table->json('maladies_chroniques')->nullable();
            $table->json('traitements')->nullable();

            // Médicaments
            $table->boolean('prend_medicament')->default(false);
            $table->string('nom_medicament')->nullable();

            // Style de vie
            $table->boolean('fumeur')->default(false);
            $table->boolean('alcool')->default(false);

            // Suivi médecin
            $table->boolean('consulte_medecin')->default(false);
            $table->string('medecin_email')->nullable();
            $table->boolean('medecin_peut_consulter')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profils_sante');
    }
};