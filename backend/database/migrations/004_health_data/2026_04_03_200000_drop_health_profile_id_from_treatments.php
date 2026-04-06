<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['health_profile_id']);
            $table->dropIndex(['health_profile_id']);
            $table->dropColumn('health_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->unsignedBigInteger('health_profile_id')->nullable()->after('id');
        });
    }
};
