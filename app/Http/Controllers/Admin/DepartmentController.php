<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['students', 'faculties', 'projects'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'level' => ['required', Rule::in(Department::LEVELS)],
            'duration_years' => ['required', 'integer', 'min:1', 'max:7'],
            'total_semesters' => ['nullable', 'integer', 'min:1', 'max:20'],
            'submission_deadline' => ['nullable', 'date'],
        ]);

        Department::create($data);

        return back()->with('success', 'Department added.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code,'.$department->id],
            'level' => ['required', Rule::in(Department::LEVELS)],
            'duration_years' => ['required', 'integer', 'min:1', 'max:7'],
            'total_semesters' => ['nullable', 'integer', 'min:1', 'max:20'],
            'submission_deadline' => ['nullable', 'date'],
        ]);

        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->students()->exists() || $department->projects()->exists() || $department->assignments()->exists()) {
            return back()->with('error', 'This department still has students, projects or assignments and cannot be deleted.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
