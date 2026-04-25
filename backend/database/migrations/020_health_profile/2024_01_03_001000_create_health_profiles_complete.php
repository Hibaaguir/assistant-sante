<?php
// Migration: creer la table des profils de sante
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
            $table->string('gender', 30)->nullable();
            $table->float('height')->nullable();
            $table->float('initial_weight')->nullable();
            $table->float('current_weight')->nullable();
            $table->string('blood_type')->nullable();

            // Goals & history
            $table->json('goals')->nullable();
            $table->json('allergies')->nullable();
            $table->json('chronic_diseases')->nullable();

            // Lifestyle
            $table->boolean('smoker')->default(false);
            $table->boolean('alcoholic')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profiles');
    }
};