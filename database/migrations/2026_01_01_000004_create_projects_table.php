<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->enum('project_type', ['single', 'group']);
            $table->string('name');
            // Leader of the project (a students.id). Each student leads at most one project.
            $table->foreignId('leader_student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('frontend_tech');
            $table->string('backend_tech');
            $table->text('abstract');
            $table->enum('status', [
                'Synopsis Pending',
                'Synopsis Under Review',
                'Synopsis Approved',
                'Correction Required',
                'Final Submitted',
                'Final Reviewed',
                'Completed',
            ])->default('Synopsis Under Review');
            $table->unsignedSmallInteger('marks')->nullable();
            $table->text('final_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
