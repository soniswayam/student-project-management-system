<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('department_id')
                ->constrained('academic_years')->nullOnDelete();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('department_id')
                ->constrained('academic_years')->nullOnDelete();
        });

        $yearId = $this->seedCurrentYear();

        DB::table('projects')->whereNull('academic_year_id')->update(['academic_year_id' => $yearId]);
        DB::table('assignments')->whereNull('academic_year_id')->update(['academic_year_id' => $yearId]);
    }

    private function seedCurrentYear(): int
    {
        $now = now();
        $startYear = $now->month >= 6 ? $now->year : $now->year - 1;
        $label = $startYear.'-'.substr((string) ($startYear + 1), -2);

        $existing = DB::table('academic_years')->where('label', $label)->first();

        if ($existing) {
            return $existing->id;
        }

        return DB::table('academic_years')->insertGetId([
            'label' => $label,
            'starts_on' => $startYear.'-06-01',
            'ends_on' => ($startYear + 1).'-05-31',
            'is_current' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_year_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
