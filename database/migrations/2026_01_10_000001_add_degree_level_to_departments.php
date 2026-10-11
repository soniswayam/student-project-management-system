<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('level', 20)->default('graduation')->after('code')->index();
            $table->unsignedTinyInteger('duration_years')->nullable()->after('level');
        });

        $this->backfill();
    }

    private function backfill(): void
    {
        foreach (DB::table('departments')->get() as $dept) {
            $key = strtoupper(trim((string) ($dept->code ?: $dept->name)));
            $isMasters = str_starts_with($key, 'M');

            $level = $isMasters ? 'post_graduation' : 'graduation';
            $years = $isMasters ? 2 : 3;

            DB::table('departments')->where('id', $dept->id)->update([
                'level' => $level,
                'duration_years' => $years,
                'total_semesters' => $dept->total_semesters ?? $years * 2,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['level', 'duration_years']);
        });
    }
};
