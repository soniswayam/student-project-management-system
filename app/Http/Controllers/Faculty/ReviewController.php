<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\ReviewFinalRequest;
use App\Http\Requests\Faculty\ReviewSynopsisRequest;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectReview;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /** Review a synopsis: approve / reject / request correction. */
    public function reviewSynopsis(ReviewSynopsisRequest $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        $data = $request->validated();

        ProjectReview::create([
            'project_id' => $project->id,
            'faculty_id' => $faculty->id,
            'stage' => 'synopsis',
            'action' => $data['action'],
            'comments' => $data['comments'] ?? null,
        ]);

        $status = match ($data['action']) {
            'approved' => Project::STATUS_SYNOPSIS_APPROVED,
            'rejected' => Project::STATUS_REJECTED,
            'correction' => Project::STATUS_CORRECTION,
        };
        $project->update(['status' => $status]);

        $this->notifyMembers(
            $project,
            'Synopsis '.ucfirst($data['action']),
            "Your synopsis for \"{$project->name}\" was marked: {$data['action']}."
        );

        return back()->with('success', 'Synopsis review submitted.');
    }

    /** Review the final submission and award marks. */
    public function reviewFinal(ReviewFinalRequest $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        // A final review is only valid once the student has actually submitted final files.
        if (! in_array($project->status, [Project::STATUS_FINAL_SUBMITTED, Project::STATUS_FINAL_REVIEWED], true)) {
            return back()->with('error', 'The student has not submitted the final project yet.');
        }

        $data = $request->validated();

        ProjectReview::create([
            'project_id' => $project->id,
            'faculty_id' => $faculty->id,
            'stage' => 'final',
            'action' => 'reviewed',
            'comments' => $data['comments'] ?? null,
            'marks' => $data['marks'] ?? null,
        ]);

        $project->update([
            'marks' => $data['marks'] ?? null,
            'final_remarks' => $data['final_remarks'] ?? null,
            'status' => $request->boolean('complete')
                ? Project::STATUS_COMPLETED
                : Project::STATUS_FINAL_REVIEWED,
        ]);

        $marksNote = isset($data['marks']) ? " Marks: {$data['marks']}/100." : '';
        $this->notifyMembers(
            $project,
            'Final project reviewed',
            "Your project \"{$project->name}\" was reviewed.".$marksNote
        );

        return back()->with('success', 'Final review submitted.');
    }

    /** Ensure the project is assigned to the current faculty; return the Faculty model. */
    private function authorizeProject(Project $project)
    {
        $faculty = auth()->user()->faculty;
        $owns = $faculty && $project->assignment && $project->assignment->faculty_id === $faculty->id;

        abort_unless($owns, 403, 'This project is not assigned to you.');

        return $faculty;
    }

    /** Send a notification to every member of the project. */
    private function notifyMembers(Project $project, string $title, string $message): void
    {
        $project->loadMissing('members.student.user');

        foreach ($project->members as $member) {
            if ($member->student && $member->student->user) {
                Notification::notify(
                    $member->student->user_id,
                    $title,
                    $message,
                    route('student.project.show')
                );
            }
        }
    }
}
