<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('degree_level_settings', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->unique();
            $table->unsignedTinyInteger('max_group_size')->default(2);
            $table->timestamp('submission_deadline')->nullable();
            $table->unsignedSmallInteger('max_marks')->nullable();
            $table->boolean('viva_required')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table('degree_level_settings')->insert([
            [
                'level' => 'graduation',
                'max_group_size' => 2,
                'max_marks' => 100,
                'viva_required' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'level' => 'post_graduation',
                'max_group_size' => 2,
                'max_marks' => 100,
                'viva_required' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('degree_level_settings');
    }
};
