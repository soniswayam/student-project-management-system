<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Project;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'faculties' => Faculty::count(),
            'departments' => Department::count(),
            'projects' => Project::count(),
        ];

        $statusCounts = Project::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $unassigned = Project::doesntHave('assignment')->count();

        $recentProjects = Project::with('leader.user', 'assignment.faculty.user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'statusCounts', 'unassigned', 'recentProjects'));
    }
}
