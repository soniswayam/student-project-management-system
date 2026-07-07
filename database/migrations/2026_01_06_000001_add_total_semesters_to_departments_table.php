<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Total number of semesters in a course (e.g. BCA = 6, MCA = 4). Drives the
     * "final semester" cap when promoting students — nobody is promoted past it.
     * Nullable so existing courses stay uncapped until an admin sets a value.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedTinyInteger('total_semesters')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('total_semesters');
        });
    }
};
