<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;

class ReportController extends Controller
{
    public function index()
    {
        // Projects grouped by status.
        $byStatus = Project::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Projects grouped by type.
        $byType = Project::selectRaw('project_type, COUNT(*) as total')
            ->groupBy('project_type')
            ->pluck('total', 'project_type');

        // Department-wise breakdown.
        $byDepartment = Department::withCount(['projects', 'students'])
            ->orderBy('name')
            ->get();

        // Completed projects with marks.
        $completed = Project::with('leader.user', 'assignment.faculty.user')
            ->whereNotNull('marks')
            ->orderByDesc('marks')
            ->get();

        return view('admin.reports.index', compact('byStatus', 'byType', 'byDepartment', 'completed'));
    }
}
