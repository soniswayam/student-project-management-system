<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        if ($project) {
            $project->load('leader.user', 'members.student.user', 'assignment.faculty.user', 'submission', 'reviews.faculty.user');
        }

        return view('student.dashboard', compact('student', 'project'));
    }
}
