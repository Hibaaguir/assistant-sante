<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('entry_date');

            // General state
            $table->unsignedTinyInteger('sleep')->nullable();
            $table->unsignedTinyInteger('stress')->nullable();
            $table->unsignedTinyInteger('energy')->nullable();
            $table->unsignedTinyInteger('caffeine')->default(0);
            $table->decimal('hydration', 4, 1)->default(0.0);
            $table->string('sugar_intake')->nullable();

            // Alcohol
            $table->boolean('alcohol')->default(false);
            $table->unsignedSmallInteger('alcohol_glasses')->nullable();

            $table->timestamps();
            $table->unique(['user_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};