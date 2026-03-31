<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signes_vitaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnDelete();
            $table->dateTime('mesure_a');
            $table->unsignedSmallInteger('frequence_cardiaque')->nullable();
            $table->unsignedSmallInteger('pression_systolique')->nullable();
            $table->unsignedSmallInteger('pression_diastolique')->nullable();
            $table->unsignedSmallInteger('saturation_oxygene')->nullable();
            $table->timestamps();

            $table->index(['id_utilisateur', 'mesure_a']);
        });

        Schema::create('resultats_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnDelete();
            $table->string('type_analyse', 120);
            $table->string('resultat_analyse', 120)->nullable();
            $table->decimal('valeur', 10, 2);
            $table->string('unite', 30)->nullable();
            $table->date('date_analyse');
            $table->timestamps();

            $table->index(['id_utilisateur', 'date_analyse']);
        });

        Schema::create('suivi_traitement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traitement_id')->constrained('traitements')->cascadeOnDelete();
            $table->date('date_controle');
            $table->boolean('pris')->default(false);
            $table->dateTime('verifie_a')->nullable();
            $table->timestamps();

            $table->unique(['traitement_id', 'date_controle']);
            $table->index(['traitement_id', 'date_controle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivi_traitement');
        Schema::dropIfExists('resultats_analyses');
        Schema::dropIfExists('signes_vitaux');
    }
};