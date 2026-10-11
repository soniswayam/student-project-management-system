<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE `projects` MODIFY `status` ENUM(
            'Synopsis Pending',
            'Synopsis Under Review',
            'Synopsis Approved',
            'Correction Required',
            'Rejected',
            'Final Submitted',
            'Final Reviewed',
            'Completed'
        ) NOT NULL DEFAULT 'Synopsis Under Review'");
    }

    public function down(): void
    {
        DB::table('projects')->where('status', 'Rejected')->update(['status' => 'Synopsis Under Review']);

        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE `projects` MODIFY `status` ENUM(
            'Synopsis Pending',
            'Synopsis Under Review',
            'Synopsis Approved',
            'Correction Required',
            'Final Submitted',
            'Final Reviewed',
            'Completed'
        ) NOT NULL DEFAULT 'Synopsis Under Review'");
    }
};
