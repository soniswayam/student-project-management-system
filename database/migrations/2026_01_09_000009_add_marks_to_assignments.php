<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_marks')->nullable()->after('type');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('marks')->nullable()->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('max_marks');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn('marks');
        });
    }
};
