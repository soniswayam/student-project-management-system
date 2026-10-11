<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSynopsisRequest;
use App\Http\Requests\Student\UpdateSynopsisRequest;
use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Support\PlagiarismChecker;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SynopsisController extends Controller
{
    public function create()
    {
        $student = auth()->user()->student ?? abort(403, 'No student profile is linked to this account.');

        if ($student->hasProject()) {
            return redirect()->route('student.project.show')
                ->with('info', 'You have already submitted a synopsis.');
        }

        $availablePartners = Student::availablePartnersFor($student);

        return view('student.synopsis.create', compact('student', 'availablePartners'));
    }

    public function store(StoreSynopsisRequest $request): RedirectResponse
    {
        $student = auth()->user()->student ?? abort(403, 'No student profile is linked to this account.');

        if ($student->hasProject()) {
            return redirect()->route('student.project.show')
                ->with('info', 'You have already submitted a synopsis.');
        }

        $data = $request->validated();
        $partnerId = $data['project_type'] === 'group' ? (int) $data['partner_student_id'] : null;

        $screen = PlagiarismChecker::screenNewSynopsis($data['name'], $data['abstract']);

        if ($screen['blocked']) {
            return back()
                ->withInput()
                ->with('error', $this->duplicateMessage($screen['top']));
        }

        $created = null;

        try {
            DB::transaction(function () use ($data, $student, $partnerId, &$created) {
                $project = Project::create([
                    'project_type' => $data['project_type'],
                    'name' => $data['name'],
                    'leader_student_id' => $student->id,
                    'department_id' => $student->department_id,
                    'academic_year_id' => AcademicYear::currentId(),
                    'frontend_tech' => $data['frontend_tech'],
                    'backend_tech' => $data['backend_tech'],
                    'abstract' => $data['abstract'],
                    'status' => Project::STATUS_SYNOPSIS_REVIEW,
                ]);

                ProjectMember::create([
                    'project_id' => $project->id,
                    'student_id' => $student->id,
                    'role_in_project' => 'leader',
                ]);

                if ($partnerId) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'student_id' => $partnerId,
                        'role_in_project' => 'partner',
                    ]);
                }

                $created = $project;
            });
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('student.project.show')
                ->with('error', 'You or your selected partner already belong to a project.');
        }

        $result = PlagiarismChecker::checkSynopsis($created);

        $this->notifyGuideOfSimilarity($created, $result['top']);

        $redirect = redirect()->route('student.project.show')
            ->with('success', 'Synopsis submitted. It is now under review.');

        if ($screen['warn']) {
            $redirect->with('warning', $this->duplicateMessage($screen['top'], false));
        }

        return $redirect;
    }

    public function edit()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        if (! $project) {
            return redirect()->route('student.synopsis.create')
                ->with('info', 'Please submit your synopsis first.');
        }

        if (! $project->isSynopsisEditable()) {
            return redirect()->route('student.project.show')
                ->with('error', 'Your synopsis can only be edited when faculty asks for a correction or rejects it.');
        }

        $project->load('members', 'reviews.faculty.user');
        $availablePartners = Student::availablePartnersFor($student, $project);

        return view('student.synopsis.edit', compact('student', 'project', 'availablePartners'));
    }

    public function update(UpdateSynopsisRequest $request): RedirectResponse
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        if (! $project || ! $project->isSynopsisEditable()) {
            return redirect()->route('student.project.show')
                ->with('error', 'This synopsis can no longer be edited.');
        }

        $data = $request->validated();
        $partnerId = $data['project_type'] === 'group' ? (int) $data['partner_student_id'] : null;

        $screen = PlagiarismChecker::screenNewSynopsis($data['name'], $data['abstract'], $project->id);

        if ($screen['blocked']) {
            return back()
                ->withInput()
                ->with('error', $this->duplicateMessage($screen['top']));
        }

        try {
            DB::transaction(function () use ($data, $project, $partnerId, $student) {
                $project->update([
                    'leader_student_id' => $student->id,
                    'project_type' => $data['project_type'],
                    'name' => $data['name'],
                    'frontend_tech' => $data['frontend_tech'],
                    'backend_tech' => $data['backend_tech'],
                    'abstract' => $data['abstract'],
                    'status' => Project::STATUS_SYNOPSIS_REVIEW,
                    'revision' => $project->revision + 1,
                ]);

                ProjectMember::where('project_id', $project->id)
                    ->where('student_id', '!=', $student->id)
                    ->delete();

                ProjectMember::where('project_id', $project->id)
                    ->where('student_id', $student->id)
                    ->update(['role_in_project' => 'leader']);

                if ($partnerId) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'student_id' => $partnerId,
                        'role_in_project' => 'partner',
                    ]);
                }
            });
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['partner_student_id' => 'The selected partner already belongs to another project.']);
        }

        $result = PlagiarismChecker::checkSynopsis($project->refresh());

        if ($faculty = $project->assignedFaculty()) {
            Notification::notify(
                $faculty->user_id,
                'Synopsis resubmitted',
                "\"{$project->name}\" was revised and resubmitted (revision {$project->revision}).",
                route('faculty.projects.show', $project)
            );
        }

        $this->notifyGuideOfSimilarity($project, $result['top']);

        $redirect = redirect()->route('student.project.show')
            ->with('success', 'Synopsis updated and resubmitted. It is under review again.');

        if ($screen['warn']) {
            $redirect->with('warning', $this->duplicateMessage($screen['top'], false));
        }

        return $redirect;
    }

    private function duplicateMessage(?array $top, bool $blocking = true): string
    {
        $percent = number_format($top['score'] ?? 0, 0);
        $name = $top['label'] ?? 'an existing project';

        if ($blocking) {
            return "This synopsis is {$percent}% similar to an existing project (\"{$name}\") and cannot be submitted. "
                .'Please choose a different topic, or rewrite the title and abstract to describe your own work.';
        }

        return "Heads up: this synopsis is {$percent}% similar to \"{$name}\". "
            .'It has been submitted, but your guide will see the match — be ready to explain how your work differs.';
    }

    private function notifyGuideOfSimilarity(Project $project, ?array $top): void
    {
        if (! $top || $top['score'] < CollegeSetting::warnPercent()) {
            return;
        }

        if (! $faculty = $project->assignedFaculty()) {
            return;
        }

        Notification::notify(
            $faculty->user_id,
            'Possible duplicate synopsis',
            "\"{$project->name}\" is ".number_format($top['score'], 0)."% similar to \"{$top['label']}\". Worth a look before approving.",
            route('faculty.projects.show', $project)
        );
    }
}
