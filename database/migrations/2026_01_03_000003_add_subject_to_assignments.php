<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Subject the assignment belongs to (a faculty teaches many subjects).
            $table->string('subject')->nullable()->after('department_id');
            // Sequence number within a subject (Assignment 1, 2, 3 ...).
            $table->unsignedSmallInteger('assignment_no')->nullable()->after('subject');
            // Nature of the assignment.
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
