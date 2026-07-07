<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add the terminal "Rejected" status to the projects.status enum. */
    public function up(): void
    {
        // ENUM alteration only applies to MySQL/MariaDB. SQLite (used in tests)
        // stores enum columns as plain strings, so no schema change is needed.
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
        // Move any rejected projects back under review before dropping the value.
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
