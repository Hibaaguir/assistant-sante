<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('medication_type', 120)->nullable();
            $table->string('medication_name', 255);
            
            // Metadata
            $table->timestamps();
            
            // Indexes
            $table->index('medication_type');
            $table->index('medication_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_catalogs');
    }
};
