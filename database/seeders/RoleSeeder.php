<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'super_admin'], [
            'label' => 'Super Admin',
            'permissions' => ['*'],
            'is_staff' => true,
            'is_system' => true,
        ]);

        Role::updateOrCreate(['name' => 'admin'], [
            'label' => 'Admin',
            'permissions' => [
                'dashboard.view',
                'students.view', 'students.create', 'students.edit',
                'alumni.view', 'alumni.manage',
                'faculty.view',
                'departments.view',
                'projects.view', 'projects.assign', 'projects.export',
                'assignments.view', 'assignments.manage',
                'reports.view', 'similarity.view', 'workload.view',
                'archive.view',
                'approvals.manage',
            ],
            'is_staff' => true,
            'is_system' => true,
        ]);

        Role::updateOrCreate(['name' => 'faculty'], [
            'label' => 'Faculty',
            'permissions' => [],
            'is_staff' => false,
            'is_system' => true,
        ]);

        Role::updateOrCreate(['name' => 'student'], [
            'label' => 'Student',
            'permissions' => [],
            'is_staff' => false,
            'is_system' => true,
        ]);
    }
}
