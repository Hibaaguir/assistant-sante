<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();

            // Physical activity data
            $table->string('activity_type', 120)->nullable(); // ex: "running", "swimming", "weight training"
            $table->unsignedSmallInteger('duration_minutes')->nullable(); // duration in minutes
            $table->enum('intensity', ['low', 'medium', 'high'])->default('medium'); // intensity

            // Metadata
            $table->timestamps();

            // Indexes for frequent queries
            $table->index('journal_entry_id');
            $table->index('activity_type');
            $table->index('intensity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_activities');
    }
};