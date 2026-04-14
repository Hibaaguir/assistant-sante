<?php
// Migration: creer la table du catalogue de traitements
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_type', 120)->nullable();
            $table->string('treatment_name', 255);

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index('treatment_type');
            $table->index('treatment_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_catalogs');
    }
};
