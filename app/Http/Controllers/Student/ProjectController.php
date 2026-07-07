<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /** Download the certificate — available once the final project is submitted. */
    public function certificate()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        abort_unless(
            $project && $project->isSubmitted(),
            404,
            'Your certificate is available after you submit your final project.'
        );

        $project->load('members.student.user', 'assignment.faculty.user');

        return Pdf::loadView('pdf.certificate', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y'),
        ])->setPaper('a4', 'landscape')->download('certificate-'.$project->id.'.pdf');
    }

    /** Download the student's own project synopsis as a PDF. */
    public function downloadSynopsis()
    {
        $student = auth()->user()->student;
        $project = $student?->project();

        abort_unless($project, 404, 'Please submit your synopsis first.');

        $project->load('department', 'members.student.user', 'assignment.faculty.user');

        return Pdf::loadView('pdf.synopsis', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4')->download('synopsis-'.$project->id.'.pdf');
    }
}
