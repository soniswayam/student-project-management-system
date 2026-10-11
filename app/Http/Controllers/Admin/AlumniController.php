<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use App\Support\SpreadsheetExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $alumni = $this->filtered($request)
            ->with('user', 'department', 'passedOutYear', 'membership.project')
            ->orderByDesc('passed_out_at')
            ->orderBy('roll_no')
            ->paginate(20)
            ->withQueryString();

        return view('admin.alumni.index', [
            'alumni' => $alumni,
            'departments' => Department::orderBy('name')->get(),
            'batches' => AcademicYear::whereIn(
                'id',
                Student::whereNotNull('passed_out_year_id')->select('passed_out_year_id')
            )->orderByDesc('label')->get(),
            'totalAlumni' => Student::whereHas('user', fn ($u) => $u->where('status', User::STATUS_GRADUATED))->count(),
        ]);
    }

    public function export(Request $request)
    {
        $alumni = $this->filtered($request)
            ->with('user', 'department', 'passedOutYear', 'membership.project')
            ->orderBy('roll_no')
            ->get();

        $rows = $alumni->map(function (Student $s) {
            $project = $s->membership?->project;

            return [
                $s->roll_no,
                $s->user?->name ?? '—',
                $s->user?->email ?? '—',
                $s->department?->name ?? '—',
                $s->department?->levelLabel() ?? '—',
                $s->batchLabel() ?? '—',
                $s->passed_out_at?->format('d M Y') ?? '—',
                $project?->name ?? '—',
                $project?->status ?? '—',
                $project?->marksLabel() ?? '—',
            ];
        })->toArray();

        return SpreadsheetExporter::download(
            'alumni-'.now()->format('Y-m-d').'.xlsx',
            'Alumni Register — '.config('college.name'),
            ['Roll No', 'Name', 'Email', 'Course', 'Level', 'Batch', 'Passed Out', 'Project', 'Project Status', 'Marks'],
            $rows
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'passed_out_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
        ]);

        $batchId = $data['passed_out_year_id'] ?? AcademicYear::currentId();

        $students = Student::with('user', 'department')
            ->whereIn('id', $data['student_ids'])
            ->get();

        $passed = 0;
        $skipped = 0;

        foreach ($students as $student) {
            if ($student->isAlumni() || ! $student->user?->isActive()) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($student, $batchId) {
                $student->update([
                    'passed_out_year_id' => $batchId,
                    'passed_out_at' => now(),
                ]);

                $student->user?->update(['status' => User::STATUS_GRADUATED]);
            });

            if ($userId = $student->user_id) {
                Notification::notify(
                    $userId,
                    'You have passed out',
                    'Your course is complete. You can still sign in to view your project and '
                        .'download your completion certificate.',
                    route('student.project.show')
                );
            }

            $passed++;
        }

        $message = "Moved {$passed} student(s) to Alumni.";
        if ($skipped > 0) {
            $message .= " {$skipped} were already alumni and were skipped.";
        }

        return back()->with('success', $message);
    }

    public function restore(Student $student): RedirectResponse
    {
        if (! $student->isAlumni()) {
            return back()->with('info', 'That student is not an alumnus.');
        }

        DB::transaction(function () use ($student) {
            $student->update(['passed_out_year_id' => null, 'passed_out_at' => null]);
            $student->user?->update(['status' => User::STATUS_ACTIVE]);
        });

        return back()->with('success', ($student->user?->name ?? 'Student').' restored to studying.');
    }

    private function filtered(Request $request): Builder
    {
        $query = Student::query()
            ->whereHas('user', fn (Builder $u) => $u->where('status', User::STATUS_GRADUATED));

        if ($request->filled('batch')) {
            $query->where('passed_out_year_id', $request->batch);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('level')) {
            $query->whereHas('department', fn (Builder $d) => $d->where('level', $request->level));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('roll_no', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return $query;
    }
}
