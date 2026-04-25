<?php
// Migration: creer la table des invitations de medecin
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('doctor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('doctor_email');
            $table->boolean('doctor_invited')->default(true);
            $table->string('status', 30)->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamp('accepted_at')->nullable(); 
            $table->timestamp('rejected_at')->nullable(); 
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_user_id', 'doctor_email'], 'invitation_unique_patient_email');
            $table->index(['doctor_user_id', 'status']);
            $table->index(['patient_user_id', 'status']);
            $table->index(['doctor_email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_invitations');
    }
};