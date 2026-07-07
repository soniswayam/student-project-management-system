<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('file_path');
            // Optional note the student adds when submitting.
            $table->text('remarks')->nullable();
            $table->timestamp('submitted_at');
            // submitted | checked
            $table->string('status')->default('submitted');
            // Optional feedback the faculty leaves when marking as checked.
            $table->text('feedback')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            // One submission per student per assignment (re-upload replaces it).
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
