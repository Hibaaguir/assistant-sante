<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('profils_sante', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // Étape 1
        $table->integer('age');
        $table->enum('sexe', ['homme', 'femme']);
        $table->float('taille');
        $table->float('poids');
        $table->string('groupe_sanguin');
        $table->string('objectif');

        // Étape 2
        $table->json('allergies')->nullable();
        $table->json('maladies_chroniques')->nullable();
        $table->json('traitements')->nullable();

        $table->boolean('prend_medicament')->default(false);
        $table->string('nom_medicament')->nullable();

        $table->boolean('fumeur')->default(false);
        $table->boolean('alcool')->default(false);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils_sante');
    }
};
