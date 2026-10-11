<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->timestamp('submission_deadline')->nullable()->after('total_semesters');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('deadline_override')->nullable()->after('revision');
            $table->string('deadline_override_reason')->nullable()->after('deadline_override');
        });

        Schema::table('college_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('project_grace_hours')->default(12)->after('logo_path');
            $table->unsignedSmallInteger('assignment_grace_hours')->default(6)->after('project_grace_hours');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('submission_deadline');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['deadline_override', 'deadline_override_reason']);
        });

        Schema::table('college_settings', function (Blueprint $table) {
            $table->dropColumn(['project_grace_hours', 'assignment_grace_hours']);
        });
    }
};
