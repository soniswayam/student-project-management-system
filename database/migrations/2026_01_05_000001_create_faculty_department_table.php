<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faculty ↔ Department is many-to-many: one professor teaches across
     * several courses/departments (e.g. BCA, MSc IT, MSc CA).
     * faculties.department_id is kept as the "primary" department.
     */
    public function up(): void
    {
        Schema::create('faculty_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unique(['faculty_id', 'department_id']);
        });

        // Backfill: every existing faculty's current department becomes a pivot row.
        foreach (DB::table('faculties')->whereNotNull('department_id')->get() as $faculty) {
            DB::table('faculty_department')->insertOrIgnore([
                'faculty_id' => $faculty->id,
                'department_id' => $faculty->department_id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_department');
    }
};
