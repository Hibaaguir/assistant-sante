<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('analysis_type', 120);
            $table->decimal('value', 10, 2);
            $table->string('unit', 30)->nullable();
            $table->date('analysis_date')->index();
            $table->string('notes', 1000)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'analysis_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_lab_results');
    }
};
