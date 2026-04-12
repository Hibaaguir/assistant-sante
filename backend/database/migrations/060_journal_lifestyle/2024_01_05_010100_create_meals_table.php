<?php
// Migration: creer la table des repas
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();

            // Type of meal
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack'])->default('lunch');

            // Meal details
            $table->text('description')->nullable(); // ex: "2 eggs, whole wheat bread"
            $table->unsignedSmallInteger('calories')->nullable();

            // Metadata
            $table->timestamps();

            // Indexes for frequent queries
            $table->index('journal_entry_id');
            $table->index('meal_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};