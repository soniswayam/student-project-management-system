<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    /** List the assignments created by the current faculty. */
    public function index()
    {
        $faculty = auth()->user()->faculty;

        $assignments = $faculty->givenAssignments()
            ->with('department')
            ->withCount('submissions')
            ->latest()
            ->get();

        return view('faculty.assignments.index', compact('assignments'));
    }

    /** Show the "create assignment" form. */
    public function create()
    {
        $faculty = auth()->user()->faculty;
        // Only the departments/courses this faculty actually teaches.
        $departments = $faculty->departments()->orderBy('name')->get();

        // Subjects this faculty has used before, to suggest in a datalist
        // (keeps spelling consistent so distribution grouping stays clean).
        $subjects = $faculty->givenAssignments()
            ->whereNotNull('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');

        return view('faculty.assignments.create', compact('departments', 'faculty', 'subjects'));
    }

    /** Store a new assignment and notify the target department's students. */
    public function store(Request $request): RedirectResponse
    {
        $faculty = auth()->user()->faculty;

        // A faculty may only post assignments to departments/courses they teach.
        $facultyDeptIds = $faculty->departments()->pluck('departments.id')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', Rule::in($facultyDeptIds)],
            'subject' => ['required', 'string', 'max:120'],
            'assignment_no' => ['nullable', 'integer', 'min:1', 'max:99'],
            'type' => ['required', 'in:'.implode(',', Assignment::TYPES)],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:20480'],
            'due_date' => ['nullable', 'date'],
        ]);

        $payload = [
            'faculty_id' => $faculty->id,
            'department_id' => $data['department_id'],
            'subject' => $data['subject'],
            'assignment_no' => $data['assignment_no'] ?? null,
            'type' => $data['type'],
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

    /** Show one assignment with its submissions. */
    public function show(Assignment $assignment)
    {
        $faculty = $this->authorizeAssignment($assignment);

        $assignment->load([
            'department',
            'submissions.student.user',
        ]);

        // Total students in the target department, to show a "X of Y submitted" figure.
        $totalStudents = $assignment->department->students()->count();

        return view('faculty.assignments.show', compact('assignment', 'totalStudents'));
    }

    /** Mark a submission as checked, with optional feedback, and notify the student. */
    public function check(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorizeAssignment($assignment);

        abort_unless($submission->assignment_id === $assignment->id, 404);

        $data = $request->validate([
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update([
            'status' => 'checked',
            'feedback' => $data['feedback'] ?? null,
            'checked_at' => now(),
        ]);

        $submission->loadMissing('student.user');

        if ($submission->student && $submission->student->user) {
            Notification::notify(
                $submission->student->user_id,
                'Assignment checked',
                "Your submission for \"{$assignment->title}\" has been checked.",
                route('student.assignments.index')
            );
        }

        return back()->with('success', 'Submission marked as checked.');
    }

    /** Delete an assignment (and its submissions via cascade). */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);

        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return redirect()->route('faculty.assignments.index')
            ->with('success', 'Assignment deleted.');
    }

    /** Ensure the assignment belongs to the current faculty; return the Faculty model. */
    private function authorizeAssignment(Assignment $assignment)
    {
        $faculty = auth()->user()->faculty;

        abort_unless($faculty && $assignment->faculty_id === $faculty->id, 403, 'This assignment is not yours.');

        return $faculty;
    }

    /** Notify every student in the assignment's target department. */
    private function notifyDepartmentStudents(Assignment $assignment, string $title, string $message): void
    {
        $students = $assignment->department->students()->with('user')->get();

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
