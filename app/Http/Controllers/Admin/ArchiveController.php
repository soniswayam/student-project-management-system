<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Project;
use App\Support\SpreadsheetExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $years = AcademicYear::archived()->orderByDesc('label')->get();
        $departments = Department::orderBy('name')->get();

        $projects = $this->filtered($request)
            ->with('members.student.user', 'department', 'academicYear', 'assignment.faculty.user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.archive.index', compact('projects', 'years', 'departments'));
    }

    public function export(Request $request)
    {
        $projects = $this->filtered($request)
            ->with('members.student.user', 'department', 'academicYear', 'assignment.faculty.user')
            ->get();

        $rows = $projects->map(fn (Project $p) => [
            $p->academicYear?->label ?? '—',
            $p->name,
            $p->project_type === 'group' ? 'Group' : 'Single',
            $p->memberNames() ?: '—',
            $p->department?->name ?? '—',
            $p->assignment?->faculty?->user?->name ?? 'Unassigned',
            $p->status,
            $p->marks ?? '—',
            $p->created_at?->format('d M Y'),
        ])->toArray();

        return SpreadsheetExporter::download(
            'archived-projects-'.now()->format('Y-m-d').'.xlsx',
            'Archived Projects — '.config('college.name'),
            ['Year', 'Project', 'Type', 'Group', 'Department', 'Guide', 'Status', 'Marks', 'Created'],
            $rows
        );
    }

    public function show(Project $project)
    {
        abort_unless($project->isArchived(), 404);

        $project->load(
            'department',
            'academicYear',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user'
        );

        return view('admin.archive.show', compact('project'));
    }

    private function filtered(Request $request): Builder
    {
        $query = Project::query()->inArchivedYear();

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
}
