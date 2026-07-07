<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the student's current semester. Nullable so existing rows are
     * unaffected; the admin forms enforce it as required going forward.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('semester', 20)->nullable()->after('roll_no');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
