<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            return;
        }

        Schema::table('doctor_invitations', function (Blueprint $table) {
            if (! Schema::hasColumn('doctor_invitations', 'general_observation')) {
                $table->text('general_observation')->nullable()->after('revoked_at');
            }

            if (! Schema::hasColumn('doctor_invitations', 'general_observation_updated_at')) {
                $table->timestamp('general_observation_updated_at')->nullable()->after('general_observation');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            return;
        }

        Schema::table('doctor_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_invitations', 'general_observation_updated_at')) {
                $table->dropColumn('general_observation_updated_at');
            }

            if (Schema::hasColumn('doctor_invitations', 'general_observation')) {
                $table->dropColumn('general_observation');
            }
        });
    }
};
