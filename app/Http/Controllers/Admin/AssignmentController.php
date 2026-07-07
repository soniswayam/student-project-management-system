<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Notification;
use App\Support\SpreadsheetExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** List every assignment across the college, with optional department filter. */
    public function index(Request $request)
    {
        $assignments = $this->query($request)->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.assignments.index', compact('assignments', 'departments'));
    }

    /** Read-only detail: one assignment and all its submissions. */
    public function show(Assignment $assignment)
    {
        $assignment->load([
            'faculty.user',
            'department',
            'submissions.student.user',
        ]);

        $totalStudents = $assignment->department->students()->count();

        return view('admin.assignments.show', compact('assignment', 'totalStudents'));
    }

    /*
    |------------------------------------------------------------------
    | Create / edit / delete (assignments.manage)
    |------------------------------------------------------------------
    */

    /** Show the "create assignment" form. */
    public function create()
    {
        return view('admin.assignments.create', $this->formData());
    }

    /** Store a new assignment and notify the target department's students. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $payload = $this->payload($data);

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment = Assignment::create($payload);
        $this->notifyDepartmentStudents($assignment);

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment created and students notified.');
    }

    /** Show the edit form. */
    public function edit(Assignment $assignment)
    {
        return view('admin.assignments.edit', $this->formData() + ['assignment' => $assignment]);
    }

    /** Update an existing assignment. */
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

    /** Delete an assignment (submissions cascade). */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment deleted.');
    }

    /** Dropdown / datalist data shared by the create & edit forms. */
    private function formData(): array
    {
        return [
            'faculties' => Faculty::with('user')->get()
                ->sortBy(fn (Faculty $f) => $f->user?->name)->values(),
            'departments' => Department::orderBy('name')->get(),
            'subjects' => Assignment::whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject'),
        ];
    }

    /** Validation rules shared by store & update. */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'subject' => ['required', 'string', 'max:120'],
            'assignment_no' => ['nullable', 'integer', 'min:1', 'max:99'],
            'type' => ['required', 'in:'.implode(',', Assignment::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:20480'],
            'due_date' => ['nullable', 'date'],
        ]);
    }

    /** Build the persisted column payload from validated data. */
    private function payload(array $data): array
    {
        return [
            'faculty_id' => $data['faculty_id'],
            'department_id' => $data['department_id'],
            'subject' => $data['subject'],
            'assignment_no' => $data['assignment_no'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ];
    }

    /** Notify every student in the assignment's target department. */
    private function notifyDepartmentStudents(Assignment $assignment): void
    {
        $students = $assignment->department->students()->with('user')->get();

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

    /** Export a student-wise submission breakdown as an Excel (.xlsx) sheet. */
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

    /** Download a student-wise submission report as a PDF. */
    public function exportPdf(Request $request)
    {
        $assignments = $this->query($request)
            ->with(['submissions.student.user', 'department'])
            ->get();

        return Pdf::loadView('pdf.assignments', [
            'assignments' => $assignments,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')
            ->download('assignment-submissions-'.now()->format('Y-m-d').'.pdf');
    }

    /*
    |------------------------------------------------------------------
    | Assignment Distribution report (Faculty › Subject › Assignment)
    |------------------------------------------------------------------
    */

    /** On-screen distribution report with Excel / PDF / Print buttons. */
    public function distribution(Request $request)
    {
        [$byFaculty, $studentCounts, $totals] = $this->distributionData($request);
        $departments = Department::orderBy('name')->get();

        return view('admin.assignments.distribution', compact('byFaculty', 'studentCounts', 'totals', 'departments'));
    }

    /** Distribution as a flat, detailed Excel sheet (one row per assignment). */
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

    /** Distribution as a grouped PDF (Faculty › Subject). */
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

    /**
     * Build the distribution dataset: assignments grouped by faculty name,
     * a department => student-count map, and headline totals.
     */
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

        $studentCounts = Department::withCount('students')->pluck('students_count', 'id');

        $totals = [
            'faculty' => $byFaculty->count(),
            'subjects' => $assignments->pluck('subject')->filter()->unique()->count(),
            'assignments' => $assignments->count(),
            'submissions' => $assignments->sum('submissions_count'),
            'checked' => $assignments->sum('checked_count'),
        ];

        return [$byFaculty, $studentCounts, $totals];
    }

    /** Shared, filterable base query with submission counts. */
    private function query(Request $request)
    {
        $query = Assignment::with(['faculty.user', 'department'])
            ->withCount([
                'submissions',
                'submissions as checked_count' => fn ($q) => $q->where('status', 'checked'),
            ])
            ->latest();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        return $query;
    }
}
