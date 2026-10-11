<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->string('venue')->nullable();
            $table->string('mode', 20)->default('offline');
            $table->string('meeting_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('scheduled')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('viva_panelists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viva_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['viva_id', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viva_panelists');
        Schema::dropIfExists('vivas');
    }
};
