<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    /** Show the current student's own project (synopsis status, reviews, submission). */
    public function show()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        if (! $project) {
            return redirect()->route('student.synopsis.create')
                ->with('info', 'Please submit your synopsis first.');
        }

        $project->load(
            'leader.user',
            'department',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user'
        );

        return view('student.project.show', compact('project', 'student'));
    }
}
