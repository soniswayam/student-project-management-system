<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'topic_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropConstrainedForeignId('topic_id');
            });
        }

        Schema::dropIfExists('project_topics');
    }

    public function down(): void
    {
        if (! Schema::hasTable('project_topics')) {
            Schema::create('project_topics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('suggested_tech')->nullable();
                $table->unsignedSmallInteger('max_teams')->nullable();
                $table->boolean('is_open')->default(true);
                $table->timestamps();

                $table->index(['department_id', 'is_open']);
            });
        }

        if (! Schema::hasColumn('projects', 'topic_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('topic_id')->nullable()->after('department_id')
                    ->constrained('project_topics')->nullOnDelete();
            });
        }
    }
};
