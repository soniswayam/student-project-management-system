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

    /** Download the analytics report as a PDF. */
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

    /** Shared analytics dataset used by both the HTML view and the PDF. */
    private function data(): array
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

        return compact('byStatus', 'byType', 'byDepartment', 'completed');
    }
}
