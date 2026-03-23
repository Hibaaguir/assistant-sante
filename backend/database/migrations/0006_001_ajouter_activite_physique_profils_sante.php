<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profils_sante', function (Blueprint $table): void {
            if (! Schema::hasColumn('profils_sante', 'activite_physique')) {
                $table->boolean('activite_physique')->default(false)->after('alcool');
            }

            if (! Schema::hasColumn('profils_sante', 'activites_physiques')) {
                $table->json('activites_physiques')->nullable()->after('activite_physique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table): void {
            if (Schema::hasColumn('profils_sante', 'activites_physiques')) {
                $table->dropColumn('activites_physiques');
            }

            if (Schema::hasColumn('profils_sante', 'activite_physique')) {
                $table->dropColumn('activite_physique');
            }
        });
    }
};
