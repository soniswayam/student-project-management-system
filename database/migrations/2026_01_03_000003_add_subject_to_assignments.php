<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('department_id');
            $table->unsignedSmallInteger('assignment_no')->nullable()->after('subject');
            $table->string('type')->default('Theory')->after('assignment_no');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['subject', 'assignment_no', 'type']);
        });
    }
};
