<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::with('user', 'department')
            ->withCount('assignments')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.faculties.index', compact('faculties'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.faculties.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'faculty',
            ]);

            Faculty::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'designation' => $data['designation'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty created.');
    }

    public function edit(Faculty $faculty)
    {
        $faculty->load('user');
        $departments = Department::orderBy('name')->get();

        return view('admin.faculties.edit', compact('faculty', 'departments'));
    }

    public function update(Request $request, Faculty $faculty): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $faculty->user_id],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        DB::transaction(function () use ($data, $faculty) {
            $faculty->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $faculty->update([
                'department_id' => $data['department_id'],
                'designation' => $data['designation'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty updated.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        $faculty->user->delete();

        return back()->with('success', 'Faculty deleted.');
    }
}
