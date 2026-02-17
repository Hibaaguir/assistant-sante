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
            $table->boolean('consulte_medecin')->default(false)->after('alcool');
            $table->string('medecin_email')->nullable()->after('consulte_medecin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            $table->dropColumn(['consulte_medecin', 'medecin_email']);
        });
    }
};
