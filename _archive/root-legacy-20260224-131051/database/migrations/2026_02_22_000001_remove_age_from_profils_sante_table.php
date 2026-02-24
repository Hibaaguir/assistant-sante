<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            if (Schema::hasColumn('profils_sante', 'age')) {
                $table->dropColumn('age');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profils_sante', function (Blueprint $table) {
            if (!Schema::hasColumn('profils_sante', 'age')) {
                $table->unsignedSmallInteger('age')->nullable()->after('user_id');
            }
        });
    }
};
