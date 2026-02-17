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
            $table->boolean('medecin_peut_consulter')->default(false)->after('medecin_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->dropColumn('medecin_peut_consulter');
        });
    }
};
