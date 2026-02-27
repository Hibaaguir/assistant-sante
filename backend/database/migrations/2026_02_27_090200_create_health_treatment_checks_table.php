<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_treatment_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('check_date')->index();
            $table->string('medication_key', 120);
            $table->string('medication_name', 255);
            $table->string('dose', 120)->nullable();
            $table->boolean('taken')->default(false);
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'check_date', 'medication_key'], 'health_treatment_checks_unique');
            $table->index(['user_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_treatment_checks');
    }
};
