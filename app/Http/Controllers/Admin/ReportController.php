<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', $this->data());
    }

    public function export()
    {
        $data = $this->data() + [
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ];

        return Pdf::loadView('pdf.report', $data)
            ->setPaper('a4')
            ->download('projects-report-'.now()->format('Y-m-d').'.pdf');
    }

    private function data(): array
    {
        $byStatus = Project::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byType = Project::selectRaw('project_type, COUNT(*) as total')
            ->groupBy('project_type')
            ->pluck('total', 'project_type');

        $byDepartment = Department::withCount(['projects', 'students'])
            ->orderBy('name')
            ->get();

        $completed = Project::with('members.student.user', 'assignment.faculty.user', 'department')
            ->whereNotNull('marks')
            ->get()
            ->filter(fn (Project $p) => $p->isLevelMarked())
            ->sortByDesc('marks')
            ->values();

        return compact('byStatus', 'byType', 'byDepartment', 'completed');
    }
}
