<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user', 'department', 'membership.project')
            ->orderBy('roll_no')
            ->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.students.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no'],
            'department_id' => ['required', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
            ]);

            Student::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'roll_no' => $data['roll_no'],
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student created.');
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $departments = Department::orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'departments'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $student->user_id],
            'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no,' . $student->id],
            'department_id' => ['required', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        DB::transaction(function () use ($data, $student) {
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $student->update([
                'department_id' => $data['department_id'],
                'roll_no' => $data['roll_no'],
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        // Deleting the user cascades to the student row.
        $student->user->delete();

        return back()->with('success', 'Student deleted.');
    }
}
