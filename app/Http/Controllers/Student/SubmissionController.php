<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CollegeSetting;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Support\DocumentText;
use App\Support\PlagiarismChecker;
use App\Support\StoredFiles;
use App\Support\SubmissionWindow;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function create()
    {
        $project = $this->currentProjectOrRedirect();
        if ($project instanceof RedirectResponse) {
            return $project;
        }

        $project->load('submission', 'department');
        $window = $project->submissionWindow();

        $revisionRequest = $project->openFinalRevisionRequest();

        if ($blocked = $this->lockedAfterReview($project, $revisionRequest)) {
            return $blocked;
        }

        if ($window->isClosed() && ! $revisionRequest && ! $project->submission) {
            return redirect()->route('student.project.show')
                ->with('error', 'The submission window closed on '.$window->closesAt()->format('d M Y, H:i').'. Contact your department for an extension.');
        }

        return view('student.submission.create', compact('project', 'window', 'revisionRequest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $project = $this->currentProjectOrRedirect();
        if ($project instanceof RedirectResponse) {
            return $project;
        }

        $existing = $project->submission;
        $window = $project->submissionWindow();
        $revisionRequest = $project->openFinalRevisionRequest();

        if ($blocked = $this->lockedAfterReview($project, $revisionRequest)) {
            return $blocked;
        }

        if ($window->isClosed() && ! $revisionRequest) {
            return redirect()->route('student.project.show')
                ->with('error', 'The submission window closed on '.$window->closesAt()->format('d M Y, H:i').'. Your work can no longer be submitted online.');
        }

        $request->validate([
            'report' => [$existing && $existing->report_path ? 'nullable' : 'required', 'file', 'extensions:pdf,doc,docx', 'mimes:pdf,doc,docx', 'max:51200'],
            'source_zip' => [$existing && $existing->source_zip_path ? 'nullable' : 'required', 'file', 'extensions:zip,rar', 'mimes:zip,rar', 'max:307200'],
            'ppt' => ['nullable', 'file', 'extensions:ppt,pptx,pdf', 'mimes:ppt,pptx,pdf', 'max:51200'],
            'screenshots' => ['nullable', 'array', 'max:6'],
            'screenshots.*' => ['file', 'image', 'extensions:jpg,jpeg,png', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'report.required' => 'Please upload your project report.',
            'report.uploaded' => 'The project report could not be uploaded. It may be larger than the server allows (50 MB).',
            'report.extensions' => 'The project report must be a PDF or Word document.',
            'report.mimes' => 'The project report must be a PDF or Word document.',
            'report.max' => 'The project report must not exceed 50 MB.',
            'source_zip.required' => 'Please upload your project source code as a ZIP file.',
            'source_zip.uploaded' => 'The project ZIP file could not be uploaded. It must not exceed 300 MB.',
            'source_zip.extensions' => 'The project source must be uploaded as a ZIP file (RAR is also accepted).',
            'source_zip.mimes' => 'The project source must be uploaded as a ZIP file (RAR is also accepted).',
            'source_zip.max' => 'The project ZIP file must not exceed 300 MB.',
            'ppt.uploaded' => 'The presentation could not be uploaded. It must not exceed 50 MB.',
            'ppt.extensions' => 'The presentation must be a PPT, PPTX or PDF file.',
            'ppt.mimes' => 'The presentation must be a PPT, PPTX or PDF file.',
            'ppt.max' => 'The presentation must not exceed 50 MB.',
            'screenshots.max' => 'You can upload at most 6 screenshots.',
            'screenshots.*.uploaded' => 'A screenshot could not be uploaded. Each image must not exceed 10 MB.',
            'screenshots.*.image' => 'Screenshots must be JPG or PNG images.',
            'screenshots.*.extensions' => 'Screenshots must be JPG or PNG images.',
            'screenshots.*.mimes' => 'Screenshots must be JPG or PNG images.',
            'screenshots.*.max' => 'Each screenshot must not exceed 10 MB.',
        ]);

        $folder = "submissions/project_{$project->id}";

        $submittedAt = now();
        $lateMinutes = $revisionRequest
            ? $revisionRequest->lateByMinutes($submittedAt)
            : $window->lateByMinutes($submittedAt);

        $payload = [
            'project_id' => $project->id,
            'submitted_at' => $submittedAt,
            'is_late' => $lateMinutes > 0,
            'late_by_minutes' => $lateMinutes > 0 ? $lateMinutes : null,
        ];

        $disk = 'local';
        $stored = [];

        if ($request->hasFile('report')) {
            $payload['report_path'] = $stored[] = $request->file('report')->store($folder, $disk);
            $payload['report_text'] = DocumentText::extract(Storage::disk($disk)->path($payload['report_path']));
        }
        if ($request->hasFile('source_zip')) {
            $payload['source_zip_path'] = $stored[] = $request->file('source_zip')->store($folder, $disk);
        }
        if ($request->hasFile('ppt')) {
            $payload['ppt_path'] = $stored[] = $request->file('ppt')->store($folder, $disk);
        }
        if ($request->hasFile('screenshots')) {
            $shots = [];
            foreach ($request->file('screenshots') as $file) {
                $shots[] = $stored[] = $file->store("{$folder}/screenshots", $disk);
            }
            $payload['screenshots'] = $shots;
        }

        try {
            $submission = DB::transaction(function () use ($project, $payload) {
                $locked = Project::whereKey($project->id)->lockForUpdate()->first();

                if (! $locked || ! $locked->isSynopsisApproved()) {
                    return null;
                }

                $submission = ProjectSubmission::updateOrCreate(['project_id' => $project->id], $payload);
                $locked->update(['status' => Project::STATUS_FINAL_SUBMITTED]);

                return $submission;
            });
        } catch (UniqueConstraintViolationException) {
            $submission = null;
        }

        if (! $submission) {
            Storage::disk($disk)->delete($stored);

            return redirect()->route('student.project.show')
                ->with('error', 'Your final project could not be saved because it was submitted at the same time from another window. Please refresh and try again.');
        }

        if ($existing) {
            StoredFiles::delete(collect(['report_path', 'source_zip_path', 'ppt_path', 'screenshots'])
                ->filter(fn ($key) => array_key_exists($key, $payload))
                ->map(fn ($key) => $existing->{$key}));
        }

        $project->refresh();

        $revisionRequest?->resolve();

        $similarity = PlagiarismChecker::checkReport($project, $submission->report_text);
        $topMatch = $similarity['top'] ?? null;

        if ($faculty = $project->assignedFaculty()) {
            $lateNote = $lateMinutes > 0
                ? ' Submitted LATE ('.SubmissionWindow::lateForHumans($lateMinutes).').'
                : '';

            Notification::notify(
                $faculty->user_id,
                $revisionRequest ? 'Requested re-upload received' : 'Final project submitted',
                $revisionRequest
                    ? "\"{$project->name}\" has been uploaded again as you asked. Ready for review."
                    : "Final files submitted for \"{$project->name}\". Ready for review.".$lateNote,
                route('faculty.projects.show', $project)
            );

            if ($topMatch && $topMatch['score'] >= CollegeSetting::warnPercent()) {
                Notification::notify(
                    $faculty->user_id,
                    'Report similarity flagged',
                    "The report for \"{$project->name}\" is ".number_format($topMatch['score'], 0)
                        ."% similar to \"{$topMatch['label']}\". Worth reviewing before grading.",
                    route('faculty.projects.show', $project)
                );
            }
        }

        if ($lateMinutes > 0) {
            return redirect()->route('student.project.show')
                ->with('warning', 'Final project submitted, but it was recorded as a LATE submission ('.SubmissionWindow::lateForHumans($lateMinutes).'). Your guide has been informed.');
        }

        return redirect()->route('student.project.show')
            ->with('success', 'Final project submitted successfully.');
    }

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
                ->with('error', 'Your synopsis must be approved before final project submission.');
        }

        return $project;
    }

    private function lockedAfterReview(Project $project, $revisionRequest): ?RedirectResponse
    {
        $reviewed = in_array($project->status, [Project::STATUS_FINAL_REVIEWED, Project::STATUS_COMPLETED], true);

        if ($reviewed && ! $revisionRequest) {
            return redirect()->route('student.project.show')
                ->with('error', 'Your final project has already been reviewed, so the files can no longer be changed.');
        }

        return null;
    }
}
