<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    private const PROJECT_FIELDS = [
        'report' => 'report_path',
        'source_zip' => 'source_zip_path',
        'ppt' => 'ppt_path',
    ];

    public function project(ProjectSubmission $submission, string $type): StreamedResponse
    {
        $this->authorizeProjectSubmission($submission);

        abort_unless(isset(self::PROJECT_FIELDS[$type]), 404);

        return $this->serve(
            $submission->{self::PROJECT_FIELDS[$type]},
            'project-'.$submission->project_id.'-'.$type
        );
    }

    public function screenshot(ProjectSubmission $submission, int $index): StreamedResponse
    {
        $this->authorizeProjectSubmission($submission);

        return $this->serve(
            $submission->screenshots[$index] ?? null,
            'project-'.$submission->project_id.'-screenshot-'.($index + 1)
        );
    }

    private function authorizeProjectSubmission(ProjectSubmission $submission): void
    {
        $submission->loadMissing('project.assignment', 'project.members', 'project.academicYear');

        abort_unless($this->canSeeProject(auth()->user(), $submission->project), 403, 'You are not authorized to access this submission.');
    }

    public function assignment(AssignmentSubmission $submission): StreamedResponse
    {
        $submission->loadMissing('assignment');

        abort_unless($this->canSeeAssignmentSubmission(auth()->user(), $submission), 403, 'You are not authorized to access this submission.');

        $name = 'assignment-'.$submission->assignment_id.'-student-'.$submission->student_id;

        return $this->serve($submission->file_path, $name);
    }

    private function canSeeProject(User $user, ?Project $project): bool
    {
        if (! $project) {
            return false;
        }

        if ($user->isStudent()) {
            $studentId = $user->student?->id;

            return $studentId !== null && $project->members->contains('student_id', $studentId);
        }

        if ($user->isFaculty()) {
            $facultyId = $user->faculty?->id;

            return $facultyId !== null && $project->assignment?->faculty_id === $facultyId;
        }

        if ($user->isStaff()) {
            return $user->hasPermission('projects.view')
                || ($project->isArchived() && $user->hasPermission('archive.view'));
        }

        return false;
    }

    private function canSeeAssignmentSubmission(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->isStudent()) {
            return $user->student !== null && $submission->student_id === $user->student->id;
        }

        if ($user->isFaculty()) {
            return $user->faculty !== null && $submission->assignment?->faculty_id === $user->faculty->id;
        }

        if ($user->isStaff()) {
            return $user->hasPermission('assignments.view');
        }

        return false;
    }

    private function serve(?string $path, string $downloadBase): StreamedResponse
    {
        abort_if(! $path || str_contains($path, '..') || str_starts_with($path, '/') || str_contains($path, '\\'), 404, 'The requested file could not be found.');

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return Storage::disk($disk)->response($path, $downloadBase.($extension ? '.'.$extension : ''), [
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404, 'The requested file could not be found.');
    }
}
