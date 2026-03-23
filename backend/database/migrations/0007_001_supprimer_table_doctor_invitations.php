<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la table doctor_invitations si elle existe
        if (Schema::hasTable('doctor_invitations')) {
            Schema::dropIfExists('doctor_invitations');
        }
    }

    public function down(): void
    {
        // Recréer la table doctor_invitations en cas de rollback
        if (!Schema::hasTable('doctor_invitations')) {
            Schema::create('doctor_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('doctor_user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('doctor_email');
                $table->string('token')->unique();
                $table->enum('status', ['pending', 'accepted', 'rejected', 'revoked'])->default('pending');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }
    }
};
