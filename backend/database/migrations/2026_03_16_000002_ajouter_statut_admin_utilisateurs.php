<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'statut_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('statut_admin', 20)->default('Actif')->after('specialite');
        });

        DB::table('users')->whereNull('statut_admin')->update(['statut_admin' => 'Actif']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'statut_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('statut_admin');
        });
    }
};
