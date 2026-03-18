<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'is_doctor')) {
            DB::table('users')
                ->where('is_doctor', true)
                ->where(function ($query) {
                    $query->whereNull('role')->orWhere('role', 'user');
                })
                ->update(['role' => 'medecin']);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_doctor');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'is_doctor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_doctor')->default(false)->after('specialite');
            });

            DB::table('users')
                ->where('role', 'medecin')
                ->update(['is_doctor' => true]);
        }
    }
};
