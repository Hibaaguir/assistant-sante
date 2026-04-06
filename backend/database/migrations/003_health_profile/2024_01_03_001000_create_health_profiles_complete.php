<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Physical information
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->string('blood_type')->nullable();

            // Goals & history
            $table->json('goals')->nullable();
            $table->json('allergies')->nullable();
            $table->json('chronic_diseases')->nullable();

            // Lifestyle
            $table->boolean('smoker')->default(false);
            $table->boolean('alcoholic')->default(false);

            // Doctor consultation
            $table->boolean('consults_doctor')->default(false);
            $table->boolean('doctor_can_consult')->default(false);
            $table->string('doctor_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profiles');
    }
};