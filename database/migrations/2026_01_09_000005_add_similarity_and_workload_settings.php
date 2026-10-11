<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('college_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('duplicate_warn_percent')->default(60)->after('assignment_grace_hours');
            $table->unsignedTinyInteger('duplicate_block_percent')->default(85)->after('duplicate_warn_percent');
            $table->unsignedSmallInteger('reminder_lead_days')->default(3)->after('duplicate_block_percent');
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_projects')->nullable()->after('designation');
        });
    }

    public function down(): void
    {
        Schema::table('college_settings', function (Blueprint $table) {
            $table->dropColumn(['duplicate_warn_percent', 'duplicate_block_percent', 'reminder_lead_days']);
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn('max_projects');
        });
    }
};
