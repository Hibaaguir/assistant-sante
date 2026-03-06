<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            Schema::create('doctor_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('doctor_email');
                $table->enum('status', ['pending', 'accepted', 'rejected', 'revoked'])->default('pending');
                $table->string('token', 64)->unique();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->unique(['patient_user_id', 'doctor_user_id'], 'doctor_invites_unique_pair');
                $table->index(['doctor_user_id', 'status']);
                $table->index(['patient_user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_invitations');
    }
};
