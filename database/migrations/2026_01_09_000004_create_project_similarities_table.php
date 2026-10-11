<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_similarities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('stage', 20)->index();
            $table->decimal('score', 5, 2)->index();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'matched_project_id', 'stage'], 'project_similarity_pair_unique');
        });

        Schema::table('project_submissions', function (Blueprint $table) {
            $table->longText('report_text')->nullable()->after('report_path');
        });
    }

    public function down(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            $table->dropColumn('report_text');
        });

        Schema::dropIfExists('project_similarities');
    }
};
