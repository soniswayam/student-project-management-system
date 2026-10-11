<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;

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

        $pendingCount = User::pendingCount();

        $recentProjects = Project::with('members.student.user', 'assignment.faculty.user')
            ->latest()
            ->take(5)
            ->get();

        $levelStats = $this->levelStats();

        return view('admin.dashboard', compact('stats', 'statusCounts', 'unassigned', 'pendingCount', 'recentProjects', 'levelStats'));
    }

    private function levelStats(): array
    {
        $students = Student::query()
            ->join('departments', 'students.department_id', '=', 'departments.id')
            ->selectRaw('departments.level, COUNT(*) as total')
            ->groupBy('departments.level')
            ->pluck('total', 'level');

        $projects = Project::query()
            ->join('departments', 'projects.department_id', '=', 'departments.id')
            ->selectRaw('departments.level, COUNT(*) as total')
            ->groupBy('departments.level')
            ->pluck('total', 'level');

        return collect(Department::LEVELS)->map(fn (string $level) => [
            'level' => $level,
            'label' => $level === Department::LEVEL_POST_GRADUATION ? 'Post-Graduation' : 'Graduation',
            'short' => $level === Department::LEVEL_POST_GRADUATION ? 'PG' : 'UG',
            'color' => $level === Department::LEVEL_POST_GRADUATION ? 'primary' : 'info',
            'students' => (int) ($students[$level] ?? 0),
            'projects' => (int) ($projects[$level] ?? 0),
            'courses' => Department::where('level', $level)->count(),
        ])->all();
    }
}
