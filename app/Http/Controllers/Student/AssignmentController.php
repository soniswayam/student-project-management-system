<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Notification;
use App\Support\SpreadsheetExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /** List assignments for the student's department, with their own submission status. */
    public function index()
    {
        [$assignments, $mySubmissions] = $this->studentAssignments();

        return view('student.assignments.index', compact('assignments', 'mySubmissions'));
    }

    /** Download the student's own assignment list as an Excel (.xlsx) sheet. */
    public function exportExcel()
    {
        [$assignments, $mySubmissions] = $this->studentAssignments();

        $rows = $assignments->values()->map(function (Assignment $a, int $i) use ($mySubmissions) {
            $mine = $mySubmissions->get($a->id);

            return [
                $i + 1,
                $a->subject ?? '—',
                $a->assignment_no ?? '—',
                $a->title,
                $a->type,
                $a->due_date?->format('d M Y') ?? '—',
                $mine?->submitted_at?->format('d M Y, H:i') ?? '—',
                $this->statusLabel($mine),
            ];
        })->toArray();

        return SpreadsheetExporter::download(
            'my-assignments-'.now()->format('Y-m-d').'.xlsx',
            'My Assignments — '.config('college.name'),
            ['No.', 'Subject', 'Asg No', 'Title', 'Type', 'Due Date', 'Submitted On', 'Status'],
            $rows
        );
    }

    /** Download the student's own assignment list as a PDF. */
    public function exportPdf()
    {
        [$assignments, $mySubmissions] = $this->studentAssignments();
        $student = auth()->user()->student->load('user', 'department');

        return Pdf::loadView('pdf.student-assignments', [
            'assignments' => $assignments->values(),
            'mySubmissions' => $mySubmissions,
            'student' => $student,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('my-assignments-'.now()->format('Y-m-d').'.pdf');
    }

    /** Assignments for the current student's department + their own submissions. */
    private function studentAssignments(): array
    {
        $student = auth()->user()->student;

        $assignments = Assignment::where('department_id', $student->department_id)
            ->with(['faculty.user'])
            ->latest()
            ->get();

        $mySubmissions = $student->assignmentSubmissions()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return [$assignments, $mySubmissions];
    }

    /** Human status for a submission (or the lack of one). */
    private function statusLabel(?AssignmentSubmission $mine): string
    {
        if (! $mine) {
            return 'Pending';
        }
        if ($mine->isChecked()) {
            return 'Checked';
        }

        return $mine->isLate() ? 'Submitted (Late)' : 'Submitted';
    }

    /** Store (or replace) the student's submission for an assignment. */
    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = auth()->user()->student;

        // A student may only submit to assignments for their own department.
        abort_unless($assignment->department_id === $student->department_id, 403);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png', 'max:20480'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $request->file('file')->store("assignment_submissions/assignment_{$assignment->id}", 'public');

        // Re-submitting replaces the previous file and resets the checked status.
        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'file_path' => $path,
                'remarks' => $data['remarks'] ?? null,
                'submitted_at' => now(),
                'status' => 'submitted',
                'feedback' => null,
                'checked_at' => null,
            ]
        );

        if ($assignment->faculty) {
            Notification::notify(
                $assignment->faculty->user_id,
                'Assignment submitted',
                "A student submitted \"{$assignment->title}\".",
                route('faculty.assignments.show', $assignment)
            );
        }

        return back()->with('success', 'Assignment submitted successfully.');
    }
}
