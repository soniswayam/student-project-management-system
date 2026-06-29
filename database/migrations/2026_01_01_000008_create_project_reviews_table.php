<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            // Which phase the review belongs to.
            $table->enum('stage', ['synopsis', 'final']);
            // Faculty decision for this review entry.
            $table->enum('action', ['approved', 'rejected', 'correction', 'reviewed']);
            $table->text('comments')->nullable();
            $table->unsignedSmallInteger('marks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_reviews');
    }
};
