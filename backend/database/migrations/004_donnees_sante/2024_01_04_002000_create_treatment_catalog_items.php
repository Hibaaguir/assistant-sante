<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogue_traitements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 120)->nullable();
            $table->string('nom', 255);
            $table->foreignId('created_by_id_utilisateur')->nullable()->constrained('utilisateurs')->nullOnDelete();
            
            // Métadonnées
            $table->timestamps();
            
            // Index
            $table->index('type');
            $table->index('nom');
            $table->index('created_by_id_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogue_traitements');
    }
};
