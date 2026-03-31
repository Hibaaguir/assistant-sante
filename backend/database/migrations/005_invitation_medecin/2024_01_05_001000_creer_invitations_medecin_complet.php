<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations_medecin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur_patient')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('id_utilisateur_medecin')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('email_medecin');
            $table->enum('statut', ['en_attente', 'accepte', 'refuse', 'revoque'])->default('en_attente');
            $table->string('jeton', 64)->unique();
            $table->timestamp('accepted_at')->nullable(); 
            $table->timestamp('rejected_at')->nullable(); 
            $table->timestamp('revoked_at')->nullable();
            $table->text('observation_generale')->nullable();
            $table->timestamp('observation_generale_mis_a_jour')->nullable();
            $table->timestamps();

            $table->unique(['id_utilisateur_patient', 'email_medecin'], 'invitation_unique_patient_email');
            $table->index(['id_utilisateur_medecin', 'statut']);
            $table->index(['id_utilisateur_patient', 'statut']);
            $table->index(['email_medecin', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations_medecin');
    }
};