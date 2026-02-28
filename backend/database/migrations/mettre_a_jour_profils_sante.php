<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profils_sante')) {
            return;
        }

        Schema::table('profils_sante', function (Blueprint $table) {
            if (! Schema::hasColumn('profils_sante', 'objectifs')) {
                $table->json('objectifs')->nullable();
            }

            if (! Schema::hasColumn('profils_sante', 'consulte_medecin')) {
                $table->boolean('consulte_medecin')->default(false);
            }

            if (! Schema::hasColumn('profils_sante', 'medecin_email')) {
                $table->string('medecin_email')->nullable();
            }

            if (! Schema::hasColumn('profils_sante', 'medecin_peut_consulter')) {
                $table->boolean('medecin_peut_consulter')->default(false);
            }
        });

        if (Schema::hasColumn('profils_sante', 'age')) {
            Schema::table('profils_sante', function (Blueprint $table) {
                $table->dropColumn('age');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('profils_sante')) {
            return;
        }

        Schema::table('profils_sante', function (Blueprint $table) {
            if (Schema::hasColumn('profils_sante', 'medecin_peut_consulter')) {
                $table->dropColumn('medecin_peut_consulter');
            }
            if (Schema::hasColumn('profils_sante', 'medecin_email')) {
                $table->dropColumn('medecin_email');
            }
            if (Schema::hasColumn('profils_sante', 'consulte_medecin')) {
                $table->dropColumn('consulte_medecin');
            }
            if (Schema::hasColumn('profils_sante', 'objectifs')) {
                $table->dropColumn('objectifs');
            }
        });
    }
};

