<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Support\SpreadsheetExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('leader.user', 'department', 'assignment.faculty.user', 'members.student.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $projects = $query->latest()->paginate(15)->withQueryString();
        $statuses = $this->statuses();

        return view('admin.projects.index', compact('projects', 'statuses'));
    }

    public function show(Project $project)
    {
        $project->load(
            'leader.user',
            'department',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user'
        );

        $faculties = Faculty::with('user')->orderBy('id')->get();

        return view('admin.projects.show', compact('project', 'faculties'));
    }

    /** Export all projects with their group members as an Excel (.xlsx) sheet. */
    public function export()
    {
        $projects = Project::with('department', 'members.student.user', 'assignment.faculty.user')
            ->latest()
            ->get();

        $rows = $projects->map(function ($p) {
            $members = $p->members
                ->map(fn ($m) => ($m->student?->user?->name ?? '—').' ('.($m->student?->roll_no ?? '—').')')
                ->implode(', ');

            return [
                $p->name,
                ucfirst($p->project_type),
                $p->department?->name ?? '—',
                $p->members->count(),
                $members,
                $p->assignment?->faculty?->user?->name ?? 'Unassigned',
                $p->status,
                $p->marks ?? '—',
            ];
        })->toArray();

        return SpreadsheetExporter::download(
            'projects-groups-'.now()->format('Y-m-d').'.xlsx',
            'Projects & Groups — '.config('college.name'),
            ['Project', 'Type', 'Department', 'Members Count', 'Group Members (Roll No)', 'Guide', 'Status', 'Marks'],
            $rows
        );
    }

    /** Download a full project report as PDF. */
    public function downloadPdf(Project $project)
    {
        $project->load('leader.user', 'department', 'members.student.user', 'assignment.faculty.user', 'reviews');

        return Pdf::loadView('pdf.designs.formal', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('project-'.$project->id.'.pdf');
    }

    /** Download a certificate (available once the final project is submitted). */
    public function certificate(Project $project)
    {
        abort_unless(
            $project->isSubmitted(),
            404,
            'Certificate is available after the final project is submitted.'
        );

        $project->load('members.student.user', 'assignment.faculty.user');

        return Pdf::loadView('pdf.certificate', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y'),
        ])->setPaper('a4', 'landscape')->download('certificate-'.$project->id.'.pdf');
    }

    /** Download a project's synopsis as a PDF. */
    public function downloadSynopsis(Project $project)
    {
        $project->load('department', 'members.student.user', 'assignment.faculty.user');

        return Pdf::loadView('pdf.synopsis', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('synopsis-'.$project->id.'.pdf');
    }

    /** Assign (or reassign) one faculty member to a project. */
    public function assignFaculty(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
        ]);

        FacultyAssignment::updateOrCreate(
            ['project_id' => $project->id],
            ['faculty_id' => $data['faculty_id'], 'assigned_at' => now()]
        );

        $faculty = Faculty::with('user')->find($data['faculty_id']);

        // Notify the faculty member.
        Notification::notify(
            $faculty->user_id,
            'New project assigned',
            "You have been assigned to review the project \"{$project->name}\".",
            route('faculty.projects.show', $project)
        );

        return back()->with('success', "Assigned to {$faculty->user->name}.");
    }

    private function statuses(): array
    {
        return [
            Project::STATUS_SYNOPSIS_PENDING,
            Project::STATUS_SYNOPSIS_REVIEW,
            Project::STATUS_SYNOPSIS_APPROVED,
            Project::STATUS_CORRECTION,
            Project::STATUS_REJECTED,
            Project::STATUS_FINAL_SUBMITTED,
            Project::STATUS_FINAL_REVIEWED,
            Project::STATUS_COMPLETED,
        ];
    }
}
