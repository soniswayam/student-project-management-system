<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectController extends Controller
{
    public function show()
    {
        $student = auth()->user()->student;

        $project = null;
        if ($student) {
            $project = $student->project();
        }

        if (!$project) {
            return redirect()->route('student.synopsis.create')
                ->with('info', 'Please submit your synopsis first.');
        }

        $project->load(
            'department',
            'members.student.user',
            'assignment.faculty.user',
            'submission',
            'reviews.faculty.user',
            'viva.panelists.user'
        );

        $revisionRequest = $project->openFinalRevisionRequest();

        return view('student.project.show', compact('project', 'student', 'revisionRequest'));
    }

    public function certificate()
    {
        $student = auth()->user()->student;

        $project = null;
        if ($student) {
            $project = $student->project();
        }

        if (!$project || !$project->isSubmitted()) {
            abort(404, 'Your certificate is available after you submit your final project.');
        }

        $project->load('members.student.user', 'assignment.faculty.user');

        $pdf = Pdf::loadView('pdf.certificate', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y'),
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $project->id . '.pdf');
    }

    public function downloadSynopsis()
    {
        $student = auth()->user()->student;

        $project = null;
        if ($student) {
            $project = $student->project();
        }

        if (!$project) {
            abort(404, 'Please submit your synopsis first.');
        }

        $project->load('department', 'members.student.user', 'assignment.faculty.user');

        $pdf = Pdf::loadView('pdf.synopsis', [
            'project' => $project,
            'college' => config('college'),
            'generatedAt' => now()->format('d M Y, H:i'),
        ]);
        $pdf->setPaper('a4');

        return $pdf->download('synopsis-' . $project->id . '.pdf');
    }
}
