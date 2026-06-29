<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /** Show the final-submission upload form (only after synopsis approval). */
    public function create()
    {
        $project = $this->currentProjectOrRedirect();
        if ($project instanceof RedirectResponse) {
            return $project;
        }

        $project->load('submission');

        return view('student.submission.create', compact('project'));
    }

    /** Store the uploaded final files. */
    public function store(Request $request): RedirectResponse
    {
        $project = $this->currentProjectOrRedirect();
        if ($project instanceof RedirectResponse) {
            return $project;
        }

        $existing = $project->submission;

        $data = $request->validate([
            'report' => [$existing && $existing->report_path ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'source_zip' => [$existing && $existing->source_zip_path ? 'nullable' : 'required', 'file', 'mimes:zip,rar', 'max:51200'],
            'ppt' => ['nullable', 'file', 'mimes:ppt,pptx,pdf', 'max:20480'],
            'screenshots' => ['nullable', 'array', 'max:6'],
            'screenshots.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $folder = "submissions/project_{$project->id}";

        $payload = ['project_id' => $project->id, 'submitted_at' => now()];

        if ($request->hasFile('report')) {
            $payload['report_path'] = $request->file('report')->store($folder, 'public');
        }
        if ($request->hasFile('source_zip')) {
            $payload['source_zip_path'] = $request->file('source_zip')->store($folder, 'public');
        }
        if ($request->hasFile('ppt')) {
            $payload['ppt_path'] = $request->file('ppt')->store($folder, 'public');
        }
        if ($request->hasFile('screenshots')) {
            $shots = [];
            foreach ($request->file('screenshots') as $file) {
                $shots[] = $file->store("{$folder}/screenshots", 'public');
            }
            $payload['screenshots'] = $shots;
        }

        ProjectSubmission::updateOrCreate(['project_id' => $project->id], $payload);

        $project->update(['status' => Project::STATUS_FINAL_SUBMITTED]);

        // Notify the assigned faculty, if any.
        if ($faculty = $project->assignedFaculty()) {
            Notification::notify(
                $faculty->user_id,
                'Final project submitted',
                "Final files submitted for \"{$project->name}\". Ready for review.",
                route('faculty.projects.show', $project)
            );
        }

        return redirect()->route('student.project.show')
            ->with('success', 'Final project submitted successfully.');
    }

    /**
     * Resolve the current student's project, enforcing that the synopsis is approved.
     * Returns a RedirectResponse when access should be blocked.
     */
    private function currentProjectOrRedirect()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        if (! $project) {
            return redirect()->route('student.synopsis.create')
                ->with('info', 'Please submit your synopsis first.');
        }

        if (! $project->isSynopsisApproved()) {
            return redirect()->route('student.project.show')
                ->with('error', 'You can upload final files only after your synopsis is approved.');
        }

        return $project;
    }
}
