<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            return;
        }

        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->dropUnique('doctor_invites_unique_pair');
            $table->dropConstrainedForeignId('doctor_user_id');
        });

        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->foreignId('doctor_user_id')->nullable()->after('patient_user_id')->constrained('users')->nullOnDelete();
            $table->unique(['patient_user_id', 'doctor_email'], 'doctor_invites_unique_patient_email');
            $table->index(['doctor_email', 'status']);
        });

        DB::table('doctor_invitations')->update([
            'doctor_email' => DB::raw('LOWER(doctor_email)'),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_invitations')) {
            return;
        }

        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->dropUnique('doctor_invites_unique_patient_email');
            $table->dropIndex(['doctor_email', 'status']);
            $table->dropConstrainedForeignId('doctor_user_id');
        });

        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete()->after('patient_user_id');
            $table->unique(['patient_user_id', 'doctor_user_id'], 'doctor_invites_unique_pair');
        });
    }
};
