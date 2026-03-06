<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_doctor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_doctor')->default(false)->after('date_of_birth');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_doctor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_doctor');
            });
        }
    }
};
