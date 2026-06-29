<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $faculty = auth()->user()->faculty;

        $projectIds = $faculty
            ? $faculty->assignments()->pluck('project_id')
            : collect();

        $projects = Project::with('leader.user', 'submission')
            ->whereIn('id', $projectIds)
            ->latest()
            ->get();

        $stats = [
            'assigned' => $projects->count(),
            'pending_synopsis' => $projects->where('status', Project::STATUS_SYNOPSIS_REVIEW)->count(),
            'pending_final' => $projects->where('status', Project::STATUS_FINAL_SUBMITTED)->count(),
            'completed' => $projects->where('status', Project::STATUS_COMPLETED)->count(),
        ];

        return view('faculty.dashboard', compact('projects', 'stats'));
    }
}
