<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la table invitations_medecin
        if (Schema::hasTable('invitations_medecin')) {
            Schema::dropIfExists('invitations_medecin');
        }

        // Supprimer les colonnes médecin de la table profils_sante
        if (Schema::hasTable('profils_sante')) {
            Schema::table('profils_sante', function (Blueprint $table) {
                if (Schema::hasColumn('profils_sante', 'consulte_medecin')) {
                    $table->dropColumn('consulte_medecin');
                }
                if (Schema::hasColumn('profils_sante', 'medecin_email')) {
                    $table->dropColumn('medecin_email');
                }
                if (Schema::hasColumn('profils_sante', 'medecin_peut_consulter')) {
                    $table->dropColumn('medecin_peut_consulter');
                }
            });
        }
    }

    public function down(): void
    {
        // Recréer les colonnes médecin dans profils_sante
        if (Schema::hasTable('profils_sante')) {
            Schema::table('profils_sante', function (Blueprint $table) {
                $table->boolean('consulte_medecin')->default(false);
                $table->string('medecin_email')->nullable();
                $table->boolean('medecin_peut_consulter')->default(false);
            });
        }

        // Recréer la table invitations_medecin
        if (!Schema::hasTable('invitations_medecin')) {
            Schema::create('invitations_medecin', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('token')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('profil_sante_id')->nullable()->constrained('profils_sante')->onDelete('cascade');
                $table->timestamp('expires_at')->nullable();
                $table->boolean('accepted')->default(false);
                $table->timestamps();
            });
        }
    }
};
