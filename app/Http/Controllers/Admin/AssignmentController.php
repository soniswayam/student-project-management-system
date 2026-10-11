<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Notification;
use App\Support\SpreadsheetExporter;
use App\Support\StoredFiles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $this->query($request)
            ->reorder()
            ->orderByDepartmentAndSemester()
            ->paginate(15)
            ->withQueryString();
        $departments = Department::orderBy('name')->get();
        $semesters = Department::semesterOptions();

        return view('admin.assignments.index', compact('assignments', 'departments', 'semesters'));
    }

    public function show(Assignment $assignment)
    {
        $assignment->load([
            'faculty.user',
            'department',
            'submissions.student.user',
        ]);

        $totalStudents = $assignment->targetStudents()->count();

        return view('admin.assignments.show', compact('assignment', 'totalStudents'));
    }

    public function create()
    {
        return view('admin.assignments.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $payload = $this->payload($data);

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $payload['academic_year_id'] = AcademicYear::currentId();

        $assignment = Assignment::create($payload);
        $this->notifyDepartmentStudents($assignment);

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment created and students notified.');
    }

    public function edit(Assignment $assignment)
    {
        return view('admin.assignments.edit', $this->formData() + ['assignment' => $assignment]);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $data = $this->validateData($request);
        $payload = $this->payload($data);

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment_path) {
                Storage::disk('public')->delete($assignment->attachment_path);
            }
            $payload['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment->update($payload);

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $files = $assignment->submissions()->pluck('file_path');

        $assignment->delete();
        StoredFiles::delete($files);

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment deleted.');
    }

    private function formData(): array
    {
        return [
            'faculties' => Faculty::with('user')->get()
                ->sortBy(fn (Faculty $f) => $f->user?->name)->values(),
            'departments' => Department::orderBy('name')->get(),
            'subjects' => Assignment::whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject'),
            'semesters' => Department::semesterOptions(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'semester' => ['nullable', 'integer', Rule::in(Department::semesterOptions())],
            'subject' => ['required', 'string', 'max:120'],
            'assignment_no' => ['nullable', 'integer', 'min:1', 'max:99'],
            'type' => ['required', 'in:'.implode(',', Assignment::TYPES)],
            'max_marks' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:20480'],
            'due_date' => ['nullable', 'date'],
        ]);
    }

    private function payload(array $data): array
    {
        return [
            'faculty_id' => $data['faculty_id'],
            'department_id' => $data['department_id'],
            'semester' => $data['semester'] ?? null,
            'subject' => $data['subject'],
            'assignment_no' => $data['assignment_no'] ?? null,
            'type' => $data['type'],
            'max_marks' => $data['max_marks'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ];
    }

    private function notifyDepartmentStudents(Assignment $assignment): void
    {
        $students = $assignment->targetStudents()->with('user')->get();

        foreach ($students as $student) {
            if ($student->user) {
                Notification::notify(
                    $student->user_id,
                    'New assignment: '.$assignment->title,
                    "A new assignment \"{$assignment->title}\" has been posted for your department.",
                    route('student.assignments.index')
                );
            }
        }
    }

    public function export(Request $request)
    {
        $assignments = $this->query($request)
            ->with(['submissions.student.user', 'department'])
            ->get();

        $rows = [];
        foreach ($assignments as $a) {
            if ($a->submissions->isEmpty()) {
                $rows[] = [
                    $a->title,
                    $a->department?->name ?? '—',
                    $a->faculty?->user?->name ?? '—',
                    $a->due_date?->format('d M Y, H:i') ?? '—',
                    '— no submissions —', '—', '—', '—', 'Pending', '—',
                ];

                continue;
            }

            foreach ($a->submissions as $s) {
                $rows[] = [
                    $a->title,
                    $a->department?->name ?? '—',
                    $a->faculty?->user?->name ?? '—',
                    $a->due_date?->format('d M Y, H:i') ?? '—',
                    $s->student?->user?->name ?? '—',
                    $s->student?->roll_no ?? '—',
                    $s->submitted_at?->format('d M Y, H:i') ?? '—',
                    $s->isLate() ? 'Yes' : 'No',
                    ucfirst($s->status),
                    $s->feedback ?: '—',
                ];
            }
        }

        return SpreadsheetExporter::download(
            'assignment-submissions-'.now()->format('Y-m-d').'.xlsx',
            'Assignment Submissions — '.config('college.name'),
            ['Assignment', 'Department', 'Faculty', 'Due Date', 'Student Name', 'Roll No', 'Submitted On', 'Late', 'Status', 'Feedback'],
            $rows
        );
    }

    public function exportPdf(Request $request)
    {
        $assignments = $this->query($request)
            ->with(['submissions.student.user', 'department'])
            ->get();

        return Pdf::loadView('pdf.assignments', [
            'assignments' => $assignments,
            'studentCounts' => Assignment::targetCounts($assignments),
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')
            ->download('assignment-submissions-'.now()->format('Y-m-d').'.pdf');
    }

    public function distribution(Request $request)
    {
        [$byFaculty, $studentCounts, $totals] = $this->distributionData($request);
        $departments = Department::orderBy('name')->get();

        return view('admin.assignments.distribution', compact('byFaculty', 'studentCounts', 'totals', 'departments'));
    }

    public function distributionExcel(Request $request)
    {
        [$byFaculty, $studentCounts] = $this->distributionData($request);

        $rows = [];
        foreach ($byFaculty as $facultyName => $assignments) {
            foreach ($assignments as $a) {
                if ($a->submissions->isEmpty()) {
                    $rows[] = [
                        $facultyName, $a->subject ?? '—', $a->assignment_no ?? '—', $a->title, $a->type,
                        $a->department?->name ?? '—', $a->due_date?->format('d M Y') ?? '—',
                        '— no submissions —', '—', '—', 'Pending',
                    ];

                    continue;
                }

                foreach ($a->submissions as $s) {
                    $rows[] = [
                        $facultyName,
                        $a->subject ?? '—',
                        $a->assignment_no ?? '—',
                        $a->title,
                        $a->type,
                        $a->department?->name ?? '—',
                        $a->due_date?->format('d M Y') ?? '—',
                        $s->student?->user?->name ?? '—',
                        $s->student?->roll_no ?? '—',
                        $s->submitted_at?->format('d M Y, H:i') ?? '—',
                        ucfirst($s->status),
                    ];
                }
            }
        }

        return SpreadsheetExporter::download(
            'assignment-distribution-'.now()->format('Y-m-d').'.xlsx',
            'Assignment Distribution — '.config('college.name'),
            ['Faculty', 'Subject', 'Asg No', 'Title', 'Type', 'Department', 'Due Date', 'Student Name', 'Roll No', 'Submitted On', 'Status'],
            $rows
        );
    }

    public function distributionPdf(Request $request)
    {
        [$byFaculty, $studentCounts, $totals] = $this->distributionData($request);

        return Pdf::loadView('pdf.assignment-distribution', [
            'byFaculty' => $byFaculty,
            'studentCounts' => $studentCounts,
            'totals' => $totals,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')
            ->download('assignment-distribution-'.now()->format('Y-m-d').'.pdf');
    }

    private function distributionData(Request $request): array
    {
        $assignments = $this->query($request)
            ->with(['submissions.student.user'])
            ->reorder()
            ->orderBy('subject')
            ->orderBy('assignment_no')
            ->get();

        $byFaculty = $assignments
            ->groupBy(fn (Assignment $a) => $a->faculty?->user?->name ?? 'Unassigned')
            ->sortKeys();

        $studentCounts = Assignment::targetCounts($assignments);

        $totals = [
            'faculty' => $byFaculty->count(),
            'subjects' => $assignments->pluck('subject')->filter()->unique()->count(),
            'assignments' => $assignments->count(),
            'submissions' => $assignments->sum('submissions_count'),
            'checked' => $assignments->sum('checked_count'),
        ];

        return [$byFaculty, $studentCounts, $totals];
    }

    private function query(Request $request)
    {
        $query = Assignment::query()
            ->inActiveYear()
            ->with(['faculty.user', 'department'])
            ->withCount([
                'submissions',
                'submissions as checked_count' => fn ($q) => $q->where('status', 'checked'),
            ])
            ->latest();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->query('semester'));
        }

        return $query;
    }
}
