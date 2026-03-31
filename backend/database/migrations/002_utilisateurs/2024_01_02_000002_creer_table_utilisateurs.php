<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compte_id');
            $table->string('nom', 255)->nullable();
            $table->date('date_naissance')->nullable();
            $table->longText('photo_profil')->nullable();
            $table->integer('age')->nullable();
            $table->string('role', 50)->default('usager');
            $table->string('specialite', 255)->nullable();
            $table->string('statut_admin', 20)->default('Actif');
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('compte_id')
                ->references('id')
                ->on('comptes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
