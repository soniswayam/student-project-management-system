<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_submission_id')->nullable()
                ->constrained('assignment_submissions')->cascadeOnDelete();
            $table->string('stage', 20)->index();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->text('reason');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_requests');
    }
};
