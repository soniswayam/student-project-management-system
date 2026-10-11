<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Notification;
use App\Support\SpreadsheetExporter;
use App\Support\StoredFiles;
use App\Support\SubmissionWindow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index()
    {
        [$assignments, $mySubmissions] = $this->studentAssignments();

        return view('student.assignments.index', compact('assignments', 'mySubmissions'));
    }

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

    private function studentAssignments(): array
    {
        $student = auth()->user()->student;

        $assignments = Assignment::query()
            ->inActiveYear()
            ->forStudent($student)
            ->with(['faculty.user'])
            ->latest()
            ->get();

        $mySubmissions = $student->assignmentSubmissions()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return [$assignments, $mySubmissions];
    }

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

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = auth()->user()->student;

        abort_unless($student && $assignment->isFor($student), 403, 'You are not authorized to submit this assignment.');

        if ($assignment->academicYear?->isArchived()) {
            return back()->with('error', 'This assignment belongs to an archived academic year and no longer accepts submissions.');
        }

        $window = $assignment->submissionWindow();

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();
        $revisionRequest = $existing?->openRevisionRequest();

        if ($window->isClosed() && ! $revisionRequest) {
            return back()->with('error', 'The assignment submission deadline has passed.');
        }

        if ($existing && ! $revisionRequest) {
            return back()->with('error', 'You have already submitted this assignment.');
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'extensions:pdf,doc,docx,zip,rar,jpg,jpeg,png', 'mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png', 'max:51200'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'Please choose a file to upload.',
            'file.uploaded' => 'The file could not be uploaded. It may be larger than the server allows (50 MB).',
            'file.extensions' => 'The assignment file must be a PDF, Word document, ZIP/RAR archive or JPG/PNG image.',
            'file.mimes' => 'The assignment file must be a PDF, Word document, ZIP/RAR archive or JPG/PNG image.',
            'file.max' => 'The assignment file must not exceed 50 MB.',
        ]);

        $path = $request->file('file')->store("assignment_submissions/assignment_{$assignment->id}", 'local');

        $submittedAt = now();
        $lateMinutes = $revisionRequest
            ? $revisionRequest->lateByMinutes($submittedAt)
            : $window->lateByMinutes($submittedAt);

        $values = [
            'file_path' => $path,
            'remarks' => $data['remarks'] ?? null,
            'submitted_at' => $submittedAt,
            'is_late' => $lateMinutes > 0,
            'late_by_minutes' => $lateMinutes > 0 ? $lateMinutes : null,
            'status' => 'submitted',
            'feedback' => null,
            'marks' => null,
            'checked_at' => null,
        ];

        try {
            $saved = DB::transaction(function () use ($assignment, $student, $existing, $values) {
                if (! $existing) {
                    AssignmentSubmission::create($values + [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ]);

                    return true;
                }

                $row = AssignmentSubmission::whereKey($existing->id)->lockForUpdate()->first();
                $open = $row?->revisionRequests()->open()->lockForUpdate()->first();

                if (! $open) {
                    return false;
                }

                $replaced = $row->file_path;
                $row->update($values);
                $open->resolve();

                if ($replaced && $replaced !== $values['file_path']) {
                    DB::afterCommit(fn () => StoredFiles::delete([$replaced]));
                }

                return true;
            });
        } catch (UniqueConstraintViolationException) {
            $saved = false;
        }

        if (! $saved) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'You have already submitted this assignment.');
        }

        if ($assignment->faculty) {
            $lateNote = $lateMinutes > 0
                ? ' Submitted LATE ('.SubmissionWindow::lateForHumans($lateMinutes).').'
                : '';

            Notification::notify(
                $assignment->faculty->user_id,
                'Assignment submitted',
                "A student submitted \"{$assignment->title}\".".$lateNote,
                route('faculty.assignments.show', $assignment)
            );
        }

        if ($lateMinutes > 0) {
            return back()->with('warning', 'Assignment submitted, but recorded as a LATE submission ('.SubmissionWindow::lateForHumans($lateMinutes).').');
        }

        return back()->with('success', 'Assignment submitted successfully.');
    }
}
