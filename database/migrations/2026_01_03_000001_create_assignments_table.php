<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            // The subject faculty who created the assignment.
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            // The department whose students must submit this assignment.
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            // Optional reference file attached by the faculty (question paper, etc.).
            $table->string('attachment_path')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
