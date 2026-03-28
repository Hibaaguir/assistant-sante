<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('journal_entries', 'calories')) {
            return;
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('calories')->nullable()->after('meals');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('journal_entries', 'calories')) {
            return;
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn('calories');
        });
    }
};
