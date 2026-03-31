<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabacs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_journal_quotidien')->constrained('journal_quotidien')->cascadeOnDelete();
            
            // Type de tabac
            $table->enum('type_tabac', ['cigarette', 'vape']);
            
            // Données spécifiques
            $table->unsignedSmallInteger('cigarettes_par_jour')->nullable(); // nombre de cigarettes par jour
            $table->unsignedSmallInteger('bouffees_par_jour')->nullable(); // nombre de bouffées par jour
            
            // Métadonnées
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index('id_journal_quotidien');
            $table->index('type_tabac');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabacs');
    }
};