<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->json('objectifs')->nullable()->after('objectif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->dropColumn('objectifs');
        });
    }
};
