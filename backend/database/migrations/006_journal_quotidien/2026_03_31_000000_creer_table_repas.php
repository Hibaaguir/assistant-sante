<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_journal_quotidien')->constrained('journal_quotidien')->cascadeOnDelete();
            
            // Type de repas
            $table->enum('type_repas', ['petit_dejeuner', 'dejeuner', 'diner', 'collation'])->default('dejeuner');
            
            // Détails du repas
            $table->text('description')->nullable(); // ex: "2 œufs, pain complet"
            $table->unsignedSmallInteger('calories')->nullable();
            $table->text('apport_sucre')->nullable(); 
            
            // Métadonnées
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index('id_journal_quotidien');
            $table->index('type_repas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repas');
    }
};