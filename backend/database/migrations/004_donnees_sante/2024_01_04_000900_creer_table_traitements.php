<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traitements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_sante_id')->constrained('profils_sante')->cascadeOnDelete();
            $table->foreignId('catalogue_traitements_id')->nullable()->constrained('catalogue_traitements')->nullOnDelete();
            
            // Détails du traitement
            $table->string('dose', 120)->nullable();
            $table->string('frequence', 120)->nullable(); 
            $table->unsignedTinyInteger('nombre_prises')->nullable();
            
            // Période du traitement
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            
            // Métadonnées
            $table->timestamps();
            
            // Index
            $table->index('profil_sante_id');
            $table->index('catalogue_traitements_id');
            $table->index('date_debut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traitements');
    }
};
