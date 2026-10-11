<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFacultyRequest;
use App\Http\Requests\Admin\UpdateFacultyRequest;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\ProjectReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $query = Faculty::with('user', 'departments')->withCount('assignments');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', fn ($d) => $d->where('departments.id', $request->department_id));
        }

        $faculties = $query->orderBy('id')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.faculties.index', compact('faculties', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.faculties.create', compact('departments'));
    }

    public function store(StoreFacultyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'faculty',
            ]);

            $faculty = Faculty::create([
                'user_id' => $user->id,
                'department_id' => $data['department_ids'][0],
                'designation' => $data['designation'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            $faculty->departments()->sync($data['department_ids']);
        });

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty created.');
    }

    public function edit(Faculty $faculty)
    {
        $faculty->load('user', 'departments');
        $departments = Department::orderBy('name')->get();
        $selectedDepartments = $faculty->departments->pluck('id')->all();

        return view('admin.faculties.edit', compact('faculty', 'departments', 'selectedDepartments'));
    }

    public function update(UpdateFacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $faculty) {
            $faculty->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $faculty->update([
                'department_id' => $data['department_ids'][0],
                'designation' => $data['designation'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            $faculty->departments()->sync($data['department_ids']);
        });

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty updated.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        if ($faculty->assignments()->exists()
            || $faculty->givenAssignments()->exists()
            || ProjectReview::where('faculty_id', $faculty->id)->exists()) {
            return back()->with('error', 'This faculty has projects, assignments or reviews on record and cannot be deleted. Block their account instead.');
        }

        $faculty->user->delete();

        return back()->with('success', 'Faculty deleted.');
    }
}
