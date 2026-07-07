<?php

/*
|--------------------------------------------------------------------------
| Admin-area permission catalog
|--------------------------------------------------------------------------
| The full list of capabilities a staff role can be granted. Grouped by
| feature for the "Manage Roles" checkbox UI. The key is what routes and
| @can() checks reference; the value is the human label.
|
| A role whose permissions array contains "*" is treated as all-access
| (super admin).
*/

return [
    'Dashboard' => [
        'dashboard.view' => 'View dashboard & analytics',
    ],
    'Students' => [
        'students.view' => 'View students',
        'students.create' => 'Add students',
        'students.edit' => 'Edit students',
        'students.delete' => 'Delete students',
    ],
    'Faculty' => [
        'faculty.view' => 'View faculty',
        'faculty.manage' => 'Add / edit / delete faculty',
    ],
    'Departments' => [
        'departments.view' => 'View departments',
        'departments.manage' => 'Add / edit / delete departments',
    ],
    'Projects' => [
        'projects.view' => 'View projects & reports export',
        'projects.assign' => 'Assign faculty to projects',
        'projects.export' => 'Export project/group data',
    ],
    'Assignments' => [
        'assignments.view' => 'View assignments & submissions',
        'assignments.manage' => 'Create / edit / delete assignments',
    ],
    'Reports' => [
        'reports.view' => 'View & export reports',
    ],
    'Administration' => [
        'admins.manage' => 'Manage admin/staff accounts',
        'roles.manage' => 'Manage roles & permissions',
        'settings.manage' => 'Manage college settings',
    ],
];
