<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            // Make treatment_id nullable since we track by medication_key
            $table->foreignId('treatment_id')->nullable()->change();
            
            // Add the fields that the controller expects
            $table->string('medication_name', 255)->nullable();
            $table->string('dose', 120)->nullable();
            $table->boolean('taken')->default(false);
            $table->dateTime('checked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('treatment_checks', function (Blueprint $table) {
            // Reverse changes: make treatment_id required again
            $table->foreignId('treatment_id')->nullable(false)->change();
            
            // Drop added columns
            $table->dropColumn(['medication_name', 'dose', 'taken', 'checked_at']);
        });
    }
};
