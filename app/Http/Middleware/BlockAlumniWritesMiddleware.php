<?php

namespace App\Http\Middleware;

use App\Models\AssignmentSubmission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockAlumniWritesMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isGraduated() && ! $this->hasOpenRevisionRequest($request, $user)) {
            return redirect()->route('student.project.show')->with(
                'error',
                'You have passed out, so your records are read-only. You can still view your '
                .'project and download your certificate.'
            );
        }

        return $next($request);
    }

    private function hasOpenRevisionRequest(Request $request, $user): bool
    {
        $student = $user->student;

        if (! $student) {
            return false;
        }

        if ($request->routeIs('student.submission.create', 'student.submission.store')) {
            return (bool) $student->project()?->openFinalRevisionRequest();
        }

        if ($request->routeIs('student.assignments.submit')) {
            $assignment = $request->route('assignment');

            $submission = $assignment
                ? AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->first()
                : null;

            return (bool) $submission?->openRevisionRequest();
        }

        return false;
    }
}
