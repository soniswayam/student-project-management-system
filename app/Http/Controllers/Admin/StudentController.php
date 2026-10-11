<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Support\PasswordRules;
use App\Support\SpreadsheetExporter;
use App\Support\SpreadsheetImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('user', 'department', 'membership.project');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('roll_no', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('level')) {
            $query->whereHas('department', fn ($d) => $d->where('level', $request->level));
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('status')) {
            $query->whereHas('user', fn ($u) => $u->where('status', $request->status));
        } else {
            $query->whereHas('user', fn ($u) => $u->where('status', '!=', User::STATUS_GRADUATED));
        }

        $students = $query->with('user', 'department', 'membership.project')
            ->orderBy('roll_no')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        $currentYear = AcademicYear::current();

        $semesters = Student::whereNotNull('semester')
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester');

        $pendingCount = User::where('role', 'student')
            ->where('status', User::STATUS_PENDING)
            ->count();

        $overSemesterCount = $this->studentsPastFinalSemester()->count();

        $alumniCount = Student::whereHas('user', fn ($u) => $u->where('status', User::STATUS_GRADUATED))->count();

        return view('admin.students.index', compact(
            'students', 'departments', 'semesters', 'pendingCount',
            'overSemesterCount', 'currentYear', 'alumniCount'
        ));
    }

    public function export()
    {
        $students = Student::with('user', 'department')
            ->orderBy('roll_no')
            ->get();

        $rows = $students->map(fn ($s) => [
            $s->user?->name,
            $s->user?->email,
            $s->roll_no,
            $s->department?->name ?? '—',
            $s->department?->levelLabel() ?? '—',
            $s->semester ?? '—',
            $s->department?->finalSemester() ?? '—',
            $s->phone ?? '—',
            ucfirst($s->user?->status ?? 'active'),
        ])->toArray();

        return SpreadsheetExporter::download(
            'students-'.now()->format('Y-m-d').'.xlsx',
            'Student List — '.config('college.name'),
            ['Name', 'Email', 'Roll No', 'Department', 'Level', 'Semester', 'Final Sem', 'Phone', 'Status'],
            $rows
        );
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.students.create', compact('departments'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
                'status' => $data['status'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'roll_no' => $data['roll_no'],
                'semester' => $data['semester'],
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student created.');
    }

    public function show(Student $student)
    {
        $student->load('user', 'department', 'membership.project');

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $departments = Department::orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'departments'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $student) {
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $student->user->isGraduated() ? User::STATUS_GRADUATED : $data['status'],
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $student->update([
                'department_id' => $data['department_id'],
                'roll_no' => $data['roll_no'],
                'semester' => $data['semester'],
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function updateStatus(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_PENDING,
                User::STATUS_BLOCKED,
            ])],
        ]);

        if ($student->user->isGraduated()) {
            return back()->with('error', 'This student is an alumnus. Use "Restore" on the Alumni page to make them a current student again.');
        }

        $student->user->update(['status' => $data['status']]);

        $labels = [
            User::STATUS_ACTIVE => 'activated',
            User::STATUS_PENDING => 'set to pending',
            User::STATUS_BLOCKED => 'blocked',
        ];

        return back()->with('success', 'Student '.($labels[$data['status']] ?? 'updated').'.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->membership()->exists() || $student->ledProject()->exists()) {
            return back()->with('error', 'This student is linked to a project and cannot be deleted.');
        }

        $student->user->delete();

        return back()->with('success', 'Student deleted.');
    }

    public function promoteSemester(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $students = Student::with('department')->whereIn('id', $data['student_ids'])->get();

        $promoted = 0;
        $skipped = 0;

        DB::transaction(function () use ($students, &$promoted, &$skipped) {
            foreach ($students as $student) {
                if (! is_numeric($student->semester)) {
                    $skipped++;

                    continue;
                }

                $current = (int) $student->semester;
                $max = $student->department?->finalSemester();

                if ($max !== null && $current >= $max) {
                    $skipped++;

                    continue;
                }

                $student->update(['semester' => (string) ($current + 1)]);
                $promoted++;
            }
        });

        $message = "Promoted {$promoted} student(s) to the next semester.";
        if ($skipped > 0) {
            $message .= " {$skipped} in their final semester were skipped.";
        }

        return back()->with('success', $message);
    }

    public function semesterReview()
    {
        return view('admin.students.semester-review', [
            'students' => $this->studentsPastFinalSemester(),
        ]);
    }

    public function fixSemesters(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $fixed = 0;

        DB::transaction(function () use ($data, &$fixed) {
            $students = Student::with('department')->whereIn('id', $data['student_ids'])->get();

            foreach ($students as $student) {
                $final = $student->department?->finalSemester();

                if ($final === null || ! is_numeric($student->semester) || (int) $student->semester <= $final) {
                    continue;
                }

                $student->update(['semester' => (string) $final]);
                $fixed++;
            }
        });

        $skipped = count($data['student_ids']) - $fixed;
        $message = "Moved {$fixed} student(s) back to their final semester.";
        if ($skipped > 0) {
            $message .= " {$skipped} no longer needed fixing and were left alone.";
        }

        return back()->with('success', $message);
    }

    private function studentsPastFinalSemester()
    {
        return Student::with('user', 'department')
            ->whereNotNull('semester')
            ->whereHas('department')
            ->get()
            ->filter(function (Student $student) {
                $final = $student->department?->finalSemester();

                return $final !== null
                    && is_numeric($student->semester)
                    && (int) $student->semester > $final;
            })
            ->sortBy([
                fn (Student $a, Student $b) => ($a->department?->name ?? '') <=> ($b->department?->name ?? ''),
                fn (Student $a, Student $b) => $a->roll_no <=> $b->roll_no,
            ])
            ->values();
    }

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('file');

        try {
            $rows = SpreadsheetImporter::rows($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Please upload a valid CSV or Excel file.');
        }

        if (empty($rows)) {
            return back()->with('error', 'The file has no data rows.');
        }

        $departments = Department::all();
        $deptLookup = [];
        foreach ($departments as $dept) {
            $deptLookup[strtolower($dept->name)] = $dept->id;
            if ($dept->code) {
                $deptLookup[strtolower($dept->code)] = $dept->id;
            }
        }

        $created = 0;
        $failures = [];

        foreach ($rows as $i => $row) {
            $lineNo = $i + 2;

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no'],
                'semester' => ['required', 'string', 'max:20'],
                'phone' => ['nullable', 'string', 'max:20'],
                'password' => ['required', 'string', PasswordRules::password()],
            ]);

            $departmentId = $deptLookup[strtolower($row['department'] ?? '')] ?? null;
            if (! $departmentId) {
                $validator->after(fn ($v) => $v->errors()->add('department', 'Department "'.($row['department'] ?? '').'" does not exist.'));
            }

            if ($validator->fails()) {
                $failures[] = [
                    'line' => $lineNo,
                    'roll_no' => $row['roll_no'] ?? '',
                    'email' => $row['email'] ?? '',
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            DB::transaction(function () use ($row, $departmentId, &$created) {
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => $row['password'],
                    'role' => 'student',
                    'status' => User::STATUS_ACTIVE,
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'department_id' => $departmentId,
                    'roll_no' => $row['roll_no'],
                    'semester' => $row['semester'],
                    'phone' => $row['phone'] ?? null,
                ]);

                $created++;
            });
        }

        return redirect()
            ->route('admin.students.import.form')
            ->with('success', "Imported {$created} student(s).")
            ->with('import_failures', $failures)
            ->with('import_total', count($rows));
    }
}
