<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $project_ids = $this->assignedProjectIds();

        $projects = Project::with('leader.user', 'members.student.user', 'submission')
            ->whereIn('id', $project_ids)
            ->latest()
            ->paginate(15);

        return view('faculty.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(
            'leader.user',
            'department',
            'members.student.user',
            'submission',
            'reviews.faculty.user'
        );

        return view('faculty.projects.show', compact('project'));
    }

    /** IDs of projects assigned to the current faculty member. */
    private function assignedProjectIds()
    {
        $faculty = auth()->user()->faculty;

        return $faculty ? $faculty->assignments()->pluck('project_id') : collect();
    }

    /** Ensure the faculty member can only access their own assigned projects. */
    private function authorizeProject(Project $project): void
    {
        $faculty = auth()->user()->faculty;
        $owns = $faculty && $project->assignment && $project->assignment->faculty_id === $faculty->id;

        abort_unless($owns, 403, 'This project is not assigned to you.');
    }
}
