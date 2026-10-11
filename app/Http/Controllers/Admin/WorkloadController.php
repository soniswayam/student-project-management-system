<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Project;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkloadController extends Controller
{
    public function index(Request $request)
    {
        $faculties = $this->rows($request);
        $departments = Department::orderBy('name')->get();

        return view('admin.workload.index', compact('faculties', 'departments'));
    }

    public function export(Request $request)
    {
        $rows = $this->rows($request)->map(fn (Faculty $f) => [
            $f->user?->name ?? '—',
            $f->designation ?? '—',
            $f->department?->name ?? '—',
            $f->active_projects_count,
            $f->pending_synopsis_count,
            $f->pending_final_count,
            $f->given_assignments_count,
            $f->unchecked_submissions_count,
            $f->max_projects ?? 'Uncapped',
            $f->isOverCapacity() ? 'Over capacity' : 'OK',
        ])->toArray();

        return SpreadsheetExporter::download(
            'faculty-workload-'.now()->format('Y-m-d').'.xlsx',
            'Faculty Workload — '.config('college.name'),
            ['Faculty', 'Designation', 'Department', 'Projects', 'Synopsis Pending', 'Final Pending',
                'Assignments Set', 'Unchecked', 'Capacity', 'Status'],
            $rows
        );
    }

    public function updateCapacity(Request $request, Faculty $faculty): RedirectResponse
    {
        $data = $request->validate([
            'max_projects' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $faculty->update(['max_projects' => $data['max_projects'] ?? null]);

        return back()->with('success', 'Capacity updated for '.($faculty->user?->name ?? 'faculty').'.');
    }

    private function rows(Request $request)
    {
        $activeProjectIds = Project::inActiveYear()->select('id');

        $query = Faculty::query()
            ->with('user', 'department')
            ->withCount([
                'assignments as active_projects_count' => fn ($q) => $q->whereIn('project_id', $activeProjectIds),

                'assignments as pending_synopsis_count' => fn ($q) => $q->whereHas(
                    'project',
                    fn ($p) => $p->inActiveYear()->where('status', Project::STATUS_SYNOPSIS_REVIEW)
                ),

                'assignments as pending_final_count' => fn ($q) => $q->whereHas(
                    'project',
                    fn ($p) => $p->inActiveYear()->where('status', Project::STATUS_FINAL_SUBMITTED)
                ),

                'givenAssignments as given_assignments_count' => fn ($q) => $q->inActiveYear(),

                'givenSubmissions as unchecked_submissions_count' => fn ($q) => $q
                    ->where('assignment_submissions.status', 'submitted')
                    ->where(fn ($y) => $y->whereNull('assignments.academic_year_id')
                        ->orWhereIn('assignments.academic_year_id', AcademicYear::active()->select('id'))),
            ]);

        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function ($q) use ($deptId) {
                $q->where('department_id', $deptId)
                    ->orWhereHas('departments', fn ($d) => $d->where('departments.id', $deptId));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $faculties = $query->get();

        return $faculties->sortByDesc(fn (Faculty $f) => $f->loadScore())->values();
    }
}
