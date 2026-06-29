<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            // One final submission per project (students can re-upload to replace it).
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('report_path')->nullable();
            $table->string('source_zip_path')->nullable();
            $table->string('ppt_path')->nullable();
            // Stored as a JSON array of screenshot paths.
            $table->json('screenshots')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_submissions');
    }
};
