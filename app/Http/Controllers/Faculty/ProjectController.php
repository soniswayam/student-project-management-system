<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Project;
use App\Support\ClassPicker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $project_ids = $this->assignedProjectIds();

        if (! $request->hasAny(['department_id', 'semester', 'flag', 'all', 'page'])) {
            $mine = Project::query()
                ->inActiveYear()
                ->with('department', 'leader')
                ->whereIn('id', $project_ids)
                ->get();

            if ($mine->isNotEmpty()) {
                $picker = ClassPicker::build(
                    $mine,
                    'project',
                    fn (Project $p) => $p->leader?->semester
                        ? [(string) $p->leader->semester, 'Semester '.$p->leader->semester]
                        : ['none', 'Semester not set'],
                    fn ($rows) => [
                        $rows->count().' '.Str::plural('project', $rows->count()),
                        $rows->where('status', Project::STATUS_SYNOPSIS_REVIEW)->count().' to review',
                        $rows->where('status', Project::STATUS_FINAL_SUBMITTED)->count().' submitted',
                    ]
                );

                return view('faculty.projects.index', compact('picker'));
            }
        }

        $projects = Project::query()
            ->inActiveYear()
            ->with('department', 'leader', 'members.student.user', 'submission', 'similarities.matchedProject.members.student.user')
            ->whereIn('id', $project_ids)
            ->when($request->query('flag') === 'similar', fn ($q) => $q->whereHas(
                'similarities',
                fn ($s) => $s->flagged()
            ))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->query('department_id')))
            ->inSemester($request->query('semester'))
            ->orderByDepartmentAndSemester()
            ->paginate(15)
            ->withQueryString();

        $departments = Department::whereIn('id', Project::whereIn('id', $project_ids)->select('department_id'))
            ->orderBy('name')
            ->get();
        $semesters = Department::semesterOptions();

        return view('faculty.projects.index', compact('projects', 'departments', 'semesters'));
    }

    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(
            'department',
            'members.student.user',
            'submission',
            'reviews.faculty.user',
            'similarities.matchedProject.academicYear',
            'similarities.matchedProject.members.student.user',
            'similarities.matchedProject.submission',
            'viva.panelists.user',
            'revisionRequests'
        );

        $panelChoices = Faculty::with('user')
            ->where('id', '!=', auth()->user()->faculty?->id)
            ->where(function ($q) use ($project) {
                $q->where('department_id', $project->department_id)
                    ->orWhereHas('departments', fn ($d) => $d->where('departments.id', $project->department_id));
            })
            ->get()
            ->sortBy(fn (Faculty $f) => $f->user?->name ?? '')
            ->values();

        return view('faculty.projects.show', compact('project', 'panelChoices'));
    }

    private function assignedProjectIds()
    {
        $faculty = auth()->user()->faculty;

        return $faculty ? $faculty->assignments()->pluck('project_id') : collect();
    }

    private function authorizeProject(Project $project): void
    {
        $faculty = auth()->user()->faculty;
        $owns = $faculty && $project->assignment && $project->assignment->faculty_id === $faculty->id;

        abort_unless($owns, 403, 'This project is not assigned to you.');
    }
}
