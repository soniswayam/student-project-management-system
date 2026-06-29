<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Review a synopsis: approve / reject / request correction. */
    public function reviewSynopsis(Request $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        $data = $request->validate([
            'action' => ['required', 'in:approved,rejected,correction'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        ProjectReview::create([
            'project_id' => $project->id,
            'faculty_id' => $faculty->id,
            'stage' => 'synopsis',
            'action' => $data['action'],
            'comments' => $data['comments'] ?? null,
        ]);

        $status = match ($data['action']) {
            'approved' => Project::STATUS_SYNOPSIS_APPROVED,
            'rejected' => Project::STATUS_SYNOPSIS_REVIEW, // stays under review; rejected recorded in history
            'correction' => Project::STATUS_CORRECTION,
        };
        $project->update(['status' => $status]);

        $this->notifyMembers(
            $project,
            'Synopsis ' . ucfirst($data['action']),
            "Your synopsis for \"{$project->name}\" was marked: {$data['action']}."
        );

        return back()->with('success', 'Synopsis review submitted.');
    }

    /** Review the final submission and award marks. */
    public function reviewFinal(Request $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        // A final review is only valid once the student has actually submitted final files.
        if (! in_array($project->status, [Project::STATUS_FINAL_SUBMITTED, Project::STATUS_FINAL_REVIEWED], true)) {
            return back()->with('error', 'The student has not submitted the final project yet.');
        }

        $data = $request->validate([
            'comments' => ['nullable', 'string', 'max:2000'],
            'marks' => ['required', 'integer', 'min:0', 'max:100'],
            'final_remarks' => ['nullable', 'string', 'max:2000'],
            'complete' => ['nullable', 'boolean'],
        ]);

        ProjectReview::create([
            'project_id' => $project->id,
            'faculty_id' => $faculty->id,
            'stage' => 'final',
            'action' => 'reviewed',
            'comments' => $data['comments'] ?? null,
            'marks' => $data['marks'],
        ]);

        $project->update([
            'marks' => $data['marks'],
            'final_remarks' => $data['final_remarks'] ?? null,
            'status' => $request->boolean('complete')
                ? Project::STATUS_COMPLETED
                : Project::STATUS_FINAL_REVIEWED,
        ]);

        $this->notifyMembers(
            $project,
            'Final project reviewed',
            "Your project \"{$project->name}\" was reviewed. Marks: {$data['marks']}/100."
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
