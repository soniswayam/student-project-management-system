<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Department;
use App\Models\Notification;
use App\Support\ClassPicker;
use App\Support\StoredFiles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $faculty = auth()->user()->faculty ?? abort(403, 'No faculty profile is linked to this account.');

        if (! $request->hasAny(['department_id', 'semester', 'all'])) {
            $mine = $faculty->givenAssignments()
                ->inActiveYear()
                ->with('department')
                ->withCount([
                    'submissions',
                    'submissions as unchecked_count' => fn ($q) => $q->where('status', '!=', 'checked'),
                ])
                ->get();

            if ($mine->isNotEmpty()) {
                $picker = ClassPicker::build(
                    $mine,
                    'assignment',
                    fn (Assignment $a) => $a->semester !== null
                        ? [(string) $a->semester, 'Semester '.$a->semester]
                        : ['common', 'Common to all semesters'],
                    fn ($rows) => [
                        $rows->count().' '.Str::plural('assignment', $rows->count()),
                        $rows->sum('submissions_count').' submitted',
                        $rows->sum('unchecked_count').' to check',
                    ]
                );

                return view('faculty.assignments.index', compact('picker'));
            }
        }

        $semester = $request->query('semester');

        $assignments = $faculty->givenAssignments()
            ->inActiveYear()
            ->with('department')
            ->withCount('submissions')
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->query('department_id')))
            ->when($semester === 'common', fn ($q) => $q->whereNull('semester'))
            ->when($semester !== null && $semester !== '' && $semester !== 'common', fn ($q) => $q->where('semester', $semester))
            ->orderByDepartmentAndSemester()
            ->get();

        $departments = $faculty->departments()->orderBy('name')->get();
        $semesters = Department::semesterOptions();

        return view('faculty.assignments.index', compact('assignments', 'departments', 'semesters'));
    }

    public function create()
    {
        $faculty = auth()->user()->faculty ?? abort(403, 'No faculty profile is linked to this account.');
        $departments = $faculty->departments()->orderBy('name')->get();

        $subjects = $faculty->givenAssignments()
            ->whereNotNull('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');

        $semesters = Department::semesterOptions();

        return view('faculty.assignments.create', compact('departments', 'faculty', 'subjects', 'semesters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $faculty = auth()->user()->faculty ?? abort(403, 'No faculty profile is linked to this account.');

        $facultyDeptIds = $faculty->departments()->pluck('departments.id')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', Rule::in($facultyDeptIds)],
            'semester' => ['nullable', 'integer', Rule::in(Department::semesterOptions())],
            'subject' => ['required', 'string', 'max:120'],
            'assignment_no' => ['nullable', 'integer', 'min:1', 'max:99'],
            'type' => ['required', 'in:'.implode(',', Assignment::TYPES)],
            'max_marks' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:20480'],
            'due_date' => ['nullable', 'date'],
        ]);

        $payload = [
            'faculty_id' => $faculty->id,
            'department_id' => $data['department_id'],
            'semester' => $data['semester'] ?? null,
            'academic_year_id' => AcademicYear::currentId(),
            'subject' => $data['subject'],
            'assignment_no' => $data['assignment_no'] ?? null,
            'type' => $data['type'],
            'max_marks' => $data['max_marks'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment = Assignment::create($payload);

        $this->notifyDepartmentStudents(
            $assignment,
            'New assignment: '.$assignment->title,
            "A new assignment \"{$assignment->title}\" has been posted for your department."
        );

        return redirect()->route('faculty.assignments.index')
            ->with('success', 'Assignment created and students notified.');
    }

    public function show(Assignment $assignment)
    {
        $faculty = $this->authorizeAssignment($assignment);

        $assignment->load([
            'department',
            'submissions.student.user',
        ]);

        $totalStudents = $assignment->targetStudents()->count();

        return view('faculty.assignments.show', compact('assignment', 'totalStudents'));
    }

    public function check(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorizeAssignment($assignment);

        abort_unless($submission->assignment_id === $assignment->id, 404);

        $data = $request->validate([
            'feedback' => ['nullable', 'string', 'max:1000'],
            'marks' => $assignment->isMarked()
                ? ['nullable', 'integer', 'min:0', 'max:'.$assignment->max_marks]
                : ['prohibited'],
        ], [
            'marks.max' => 'Marks cannot exceed this assignment\'s maximum of '.$assignment->max_marks.'.',
            'marks.prohibited' => 'This assignment has no marks scheme, so it cannot be scored.',
        ]);

        $submission->update([
            'status' => 'checked',
            'feedback' => $data['feedback'] ?? null,
            'marks' => $data['marks'] ?? null,
            'checked_at' => now(),
        ]);

        $submission->loadMissing('student.user');

        if ($submission->student && $submission->student->user) {
            $scoreNote = $submission->fresh()->marksLabel();

            Notification::notify(
                $submission->student->user_id,
                'Assignment checked',
                "Your submission for \"{$assignment->title}\" has been checked."
                    .($scoreNote ? " Marks: {$scoreNote}." : ''),
                route('student.assignments.index')
            );
        }

        return back()->with('success', 'Submission marked as checked.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);

        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $files = $assignment->submissions()->pluck('file_path');

        $assignment->delete();
        StoredFiles::delete($files);

        return redirect()->route('faculty.assignments.index')
            ->with('success', 'Assignment deleted.');
    }

    private function authorizeAssignment(Assignment $assignment)
    {
        $faculty = auth()->user()->faculty;

        abort_unless($faculty && $assignment->faculty_id === $faculty->id, 403, 'This assignment is not yours.');

        if (request()->isMethod('GET') === false) {
            abort_if($assignment->academicYear?->isArchived(), 403, 'This assignment belongs to an archived academic year and is read-only.');
        }

        return $faculty;
    }

    private function notifyDepartmentStudents(Assignment $assignment, string $title, string $message): void
    {
        $students = $assignment->targetStudents()->with('user')->get();

        foreach ($students as $student) {
            if ($student->user) {
                Notification::notify(
                    $student->user_id,
                    $title,
                    $message,
                    route('student.assignments.index')
                );
            }
        }
    }
}
