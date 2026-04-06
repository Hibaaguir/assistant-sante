<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tobacco')) {
            Schema::create('tobacco', function (Blueprint $table) {
                $table->id();
                $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
                
                // Type of tobacco
                $table->enum('tobacco_type', ['cigarette', 'vape']);
                
                // Specific data
                $table->unsignedSmallInteger('cigarettes_per_day')->nullable(); // number of cigarettes per day
                $table->unsignedSmallInteger('puffs_per_day')->nullable(); // number of puffs per day
                
                // Metadata
                $table->timestamps();
                
                // Indexes for frequent queries
                $table->index('journal_entry_id');
                $table->index('tobacco_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tobacco');
    }
};