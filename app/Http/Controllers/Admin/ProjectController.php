<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\DegreeLevelSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\FacultyAssignmentHistory;
use App\Models\Notification;
use App\Models\Project;
use App\Support\SpreadsheetExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $this->filtered($request)
            ->with('department', 'leader', 'academicYear', 'viva', 'assignment.faculty.user', 'members.student.user')
            ->orderByDepartmentAndSemester()
            ->paginate(15)
            ->withQueryString();

        $statuses = $this->statuses();
        $departments = Department::orderBy('name')->get();
        $semesters = Department::semesterOptions();
        $years = AcademicYear::active()->orderByDesc('label')->get();

        $faculties = Faculty::with('user')
            ->withCount(['assignments as active_projects_count' => fn ($q) => $q->whereHas('project', fn ($p) => $p->inActiveYear())])
            ->get()
            ->sortBy(fn (Faculty $f) => $f->user?->name ?? '')
            ->values();

        return view('admin.projects.index', compact('projects', 'statuses', 'faculties', 'departments', 'years', 'semesters'));
    }

    public function show(Project $project)
    {
        $project->load(
            'department',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user',
            'academicYear',
            'similarities.matchedProject.academicYear',
            'similarities.matchedProject.members.student.user',
            'similarities.matchedProject.submission',
            'viva.panelists.user',
            'guideHistory.fromFaculty.user',
            'guideHistory.toFaculty.user',
            'guideHistory.changedBy'
        );

        $window = $project->submissionWindow();

        $faculties = Faculty::with('user')
            ->withCount(['assignments as active_projects_count' => fn ($q) => $q->whereHas('project', fn ($p) => $p->inActiveYear())])
            ->get()
            ->sortBy(fn (Faculty $f) => $f->user?->name ?? '')
            ->values();

        return view('admin.projects.show', compact('project', 'faculties', 'window'));
    }

    public function updateDeadline(Request $request, Project $project): RedirectResponse
    {
        abort_if($project->isArchived(), 403, 'This project belongs to an archived academic year and is read-only.');

        $data = $request->validate([
            'deadline_override' => ['nullable', 'date'],
            'deadline_override_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $project->update([
            'deadline_override' => $data['deadline_override'] ?? null,
            'deadline_override_reason' => $data['deadline_override'] ? ($data['deadline_override_reason'] ?? null) : null,
        ]);

        if ($project->deadline_override) {
            foreach ($project->members as $member) {
                if ($userId = $member->student?->user_id) {
                    Notification::notify(
                        $userId,
                        'Project deadline extended',
                        'Your submission deadline is now '.$project->deadline_override->format('d M Y, H:i').'.',
                        route('student.project.show')
                    );
                }
            }

            return back()->with('success', 'Deadline extension saved and the team has been notified.');
        }

        return back()->with('success', 'Extension removed — this project follows its department deadline again.');
    }

    public function export(Request $request)
    {
        $projects = $this->filtered($request)
            ->with('department', 'academicYear', 'members.student.user', 'assignment.faculty.user')
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
                $p->academicYear?->label ?? '—',
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
            ['Project', 'Type', 'Department', 'Year', 'Members Count', 'Group Members (Roll No)', 'Guide', 'Status', 'Marks'],
            $rows
        );
    }

    public function downloadPdf(Project $project)
    {
        $project->load('department', 'members.student.user', 'assignment.faculty.user', 'reviews');

        return Pdf::loadView('pdf.designs.formal', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('project-'.$project->id.'.pdf');
    }

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

    public function downloadSynopsis(Project $project)
    {
        $project->load('department', 'members.student.user', 'assignment.faculty.user');

        return Pdf::loadView('pdf.synopsis', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('synopsis-'.$project->id.'.pdf');
    }

    public function assignFaculty(Request $request, Project $project): RedirectResponse
    {
        abort_if($project->isArchived(), 403, 'This project belongs to an archived academic year and is read-only.');

        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newFaculty = Faculty::with('user')->findOrFail($data['faculty_id']);
        $previous = $project->assignment?->faculty;

        if ($previous && $previous->id === $newFaculty->id) {
            return back()->with('info', "{$newFaculty->user?->name} already guides this project.");
        }

        DB::transaction(function () use ($project, $newFaculty, $previous, $data) {
            FacultyAssignment::updateOrCreate(
                ['project_id' => $project->id],
                ['faculty_id' => $newFaculty->id, 'assigned_at' => now()]
            );

            FacultyAssignmentHistory::create([
                'project_id' => $project->id,
                'from_faculty_id' => $previous?->id,
                'to_faculty_id' => $newFaculty->id,
                'reason' => $data['reason'] ?? null,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        });

        $this->announceGuideChange($project, $newFaculty, $previous, $data['reason'] ?? null);

        if ($newFaculty->isOverCapacity()) {
            return back()->with('warning', "Assigned to {$newFaculty->user?->name}, but they are now over their stated capacity of {$newFaculty->max_projects} projects.");
        }

        return back()->with('success', $previous
            ? "Guide replaced — {$newFaculty->user?->name} now guides this project."
            : "Assigned to {$newFaculty->user?->name}.");
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $faculty = Faculty::with('user')->findOrFail($data['faculty_id']);

        $projects = Project::with('assignment.faculty')
            ->whereIn('id', $data['project_ids'])
            ->get();

        $assigned = 0;
        $skipped = 0;

        foreach ($projects as $project) {
            if ($project->isArchived()) {
                $skipped++;

                continue;
            }

            $previous = $project->assignment?->faculty;

            if ($previous && $previous->id === $faculty->id) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($project, $faculty, $previous, $data) {
                FacultyAssignment::updateOrCreate(
                    ['project_id' => $project->id],
                    ['faculty_id' => $faculty->id, 'assigned_at' => now()]
                );

                FacultyAssignmentHistory::create([
                    'project_id' => $project->id,
                    'from_faculty_id' => $previous?->id,
                    'to_faculty_id' => $faculty->id,
                    'reason' => $data['reason'] ?? null,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                ]);
            });

            $this->announceGuideChange($project, $faculty, $previous, $data['reason'] ?? null);
            $assigned++;
        }

        $message = "Assigned {$assigned} project(s) to {$faculty->user?->name}.";
        if ($skipped > 0) {
            $message .= " {$skipped} already had them as guide and were skipped.";
        }

        if ($faculty->fresh()->isOverCapacity()) {
            return back()->with('warning', $message." They are now over their stated capacity of {$faculty->max_projects}.");
        }

        return back()->with('success', $message);
    }

    private function announceGuideChange(Project $project, Faculty $newFaculty, ?Faculty $previous, ?string $reason): void
    {
        $because = $reason ? " Reason: {$reason}" : '';

        Notification::notify(
            $newFaculty->user_id,
            $previous ? 'Project reassigned to you' : 'New project assigned',
            "You have been assigned to guide the project \"{$project->name}\".".$because,
            route('faculty.projects.show', $project)
        );

        if ($previous && $previous->user_id) {
            Notification::notify(
                $previous->user_id,
                'Project reassigned',
                "\"{$project->name}\" has been reassigned to {$newFaculty->user?->name} and is no longer on your list.".$because,
                route('faculty.projects.index')
            );
        }

        if (! $previous) {
            return;
        }

        $project->loadMissing('members.student.user');

        foreach ($project->members as $member) {
            if ($userId = $member->student?->user_id) {
                Notification::notify(
                    $userId,
                    'Your project guide has changed',
                    "{$newFaculty->user?->name} is now the guide for \"{$project->name}\".",
                    route('student.project.show')
                );
            }
        }
    }

    private function filtered(Request $request): Builder
    {
        $query = Project::query()->inActiveYear();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $query->inSemester($request->query('semester'));

        if ($request->filled('level')) {
            $query->whereHas('department', fn (Builder $d) => $d->where('level', $request->level));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('faculty_id')) {
            $request->faculty_id === 'unassigned'
                ? $query->whereDoesntHave('assignment')
                : $query->whereHas('assignment', fn (Builder $a) => $a->where('faculty_id', $request->faculty_id));
        }

        if ($request->filled('flag')) {
            match ($request->flag) {
                'similar' => $query->whereHas('similarities', fn (Builder $s) => $s->where('score', '>=', CollegeSetting::warnPercent())),
                'late' => $query->whereHas('submission', fn (Builder $s) => $s->where('is_late', true)),
                'viva' => $query->whereHas('viva'),
                'no_viva' => $query->whereDoesntHave('viva'),
                'viva_missing' => $query->whereDoesntHave('viva')
                    ->whereHas('department', fn (Builder $d) => $d->whereIn(
                        'level',
                        DegreeLevelSetting::where('viva_required', true)->pluck('level')
                    )),
                'reupload' => $query->whereHas('revisionRequests', fn (Builder $r) => $r->whereNull('resolved_at')),
                default => null,
            };
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('members.student', fn (Builder $s) => $s->where('roll_no', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%")));
            });
        }

        return $query;
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
