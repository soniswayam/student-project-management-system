<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Faculty;
use App\Models\Notification;
use App\Models\Project;
use App\Models\RevisionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RevisionRequestController extends Controller
{
    public function requestFinal(Request $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        $data = $this->validateRequest($request);

        if ($project->openFinalRevisionRequest()) {
            return back()->with('info', 'There is already an open re-upload request for this project.');
        }

        RevisionRequest::create([
            'project_id' => $project->id,
            'stage' => RevisionRequest::STAGE_FINAL,
            'faculty_id' => $faculty->id,
            'reason' => $data['reason'],
            'due_at' => $data['due_at'] ?? null,
        ]);

        $this->notifyMembers(
            $project,
            'Re-upload requested',
            "Your guide has asked you to upload the final files for \"{$project->name}\" again. Reason: {$data['reason']}",
            route('student.submission.create')
        );

        return back()->with('success', 'Re-upload requested. The student can submit again even though the deadline has passed.');
    }

    public function requestAssignment(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $faculty = $this->authorizeAssignment($assignment);

        abort_unless($submission->assignment_id === $assignment->id, 404);

        $data = $this->validateRequest($request);

        if ($submission->openRevisionRequest()) {
            return back()->with('info', 'There is already an open re-upload request for this submission.');
        }

        RevisionRequest::create([
            'assignment_submission_id' => $submission->id,
            'stage' => RevisionRequest::STAGE_ASSIGNMENT,
            'faculty_id' => $faculty->id,
            'reason' => $data['reason'],
            'due_at' => $data['due_at'] ?? null,
        ]);

        $submission->update(['status' => 'submitted', 'checked_at' => null]);

        $submission->loadMissing('student.user');

        if ($userId = $submission->student?->user_id) {
            Notification::notify(
                $userId,
                'Re-upload requested',
                "Your guide has asked you to submit \"{$assignment->title}\" again. Reason: {$data['reason']}",
                route('student.assignments.index')
            );
        }

        return back()->with('success', 'Re-upload requested. The student can submit again.');
    }

    public function cancel(RevisionRequest $revisionRequest): RedirectResponse
    {
        $faculty = auth()->user()->faculty;

        abort_unless($faculty && $revisionRequest->faculty_id === $faculty->id, 403);

        if (! $revisionRequest->isOpen()) {
            return back()->with('info', 'That request has already been dealt with.');
        }

        $revisionRequest->resolve();

        return back()->with('success', 'Re-upload request withdrawn.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'due_at' => ['nullable', 'date', 'after:now'],
        ], [
            'due_at.after' => 'A re-upload deadline has to be in the future.',
        ]);
    }

    private function authorizeProject(Project $project): Faculty
    {
        $faculty = auth()->user()->faculty;
        $owns = $faculty && $project->assignment && $project->assignment->faculty_id === $faculty->id;

        abort_unless($owns, 403, 'This project is not assigned to you.');
        abort_if($project->isArchived(), 403, 'This project belongs to an archived academic year and is read-only.');

        return $faculty;
    }

    private function authorizeAssignment(Assignment $assignment): Faculty
    {
        $faculty = auth()->user()->faculty;

        abort_unless($faculty && $assignment->faculty_id === $faculty->id, 403, 'This assignment is not yours.');

        return $faculty;
    }

    private function notifyMembers(Project $project, string $title, string $message, string $link): void
    {
        $project->loadMissing('members.student.user');

        foreach ($project->members as $member) {
            if ($userId = $member->student?->user_id) {
                Notification::notify($userId, $title, $message, $link);
            }
        }
    }
}
