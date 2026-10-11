<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('passed_out_year_id')->nullable()->after('semester')
                ->constrained('academic_years')->nullOnDelete();
            $table->timestamp('passed_out_at')->nullable()->after('passed_out_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('passed_out_year_id');
            $table->dropColumn('passed_out_at');
        });
    }
};
