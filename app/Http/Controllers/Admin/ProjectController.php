<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('leader.user', 'department', 'assignment.faculty.user', 'members.student.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->latest()->paginate(15)->withQueryString();
        $statuses = $this->statuses();

        return view('admin.projects.index', compact('projects', 'statuses'));
    }

    public function show(Project $project)
    {
        $project->load(
            'leader.user',
            'department',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user'
        );

        $faculties = Faculty::with('user')->orderBy('id')->get();

        return view('admin.projects.show', compact('project', 'faculties'));
    }

    /** Assign (or reassign) one faculty member to a project. */
    public function assignFaculty(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
        ]);

        FacultyAssignment::updateOrCreate(
            ['project_id' => $project->id],
            ['faculty_id' => $data['faculty_id'], 'assigned_at' => now()]
        );

        $faculty = Faculty::with('user')->find($data['faculty_id']);

        // Notify the faculty member.
        Notification::notify(
            $faculty->user_id,
            'New project assigned',
            "You have been assigned to review the project \"{$project->name}\".",
            route('faculty.projects.show', $project)
        );

        return back()->with('success', "Assigned to {$faculty->user->name}.");
    }

    private function statuses(): array
    {
        return [
            Project::STATUS_SYNOPSIS_PENDING,
            Project::STATUS_SYNOPSIS_REVIEW,
            Project::STATUS_SYNOPSIS_APPROVED,
            Project::STATUS_CORRECTION,
            Project::STATUS_FINAL_SUBMITTED,
            Project::STATUS_FINAL_REVIEWED,
            Project::STATUS_COMPLETED,
        ];
    }
}
