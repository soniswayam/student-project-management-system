<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SynopsisController extends Controller
{
    /** Show the synopsis (project creation) form. */
    public function create()
    {
        $student = auth()->user()->student;

        // A student may create only ONE project. If they already have one, send them home.
        if ($student->hasProject()) {
            return redirect()->route('student.project.show')
                ->with('info', 'You have already submitted a synopsis.');
        }

        // Students with no project yet are eligible to be a partner.
        $availablePartners = Student::with('user')
            ->where('id', '!=', $student->id)
            ->whereDoesntHave('membership')
            ->orderBy('roll_no')
            ->get();

        return view('student.synopsis.create', compact('student', 'availablePartners'));
    }

    /** Store the synopsis and create the project. */
    public function store(Request $request): RedirectResponse
    {
        $student = auth()->user()->student;

        if ($student->hasProject()) {
            return redirect()->route('student.project.show')
                ->with('info', 'You have already submitted a synopsis.');
        }

        $data = $request->validate([
            'project_type' => ['required', 'in:single,group'],
            'name' => ['required', 'string', 'max:255'],
            'frontend_tech' => ['required', 'string', 'max:255'],
            'backend_tech' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'min:30'],
            // Partner is required only for group projects.
            'partner_student_id' => [
                Rule::requiredIf(fn () => $request->input('project_type') === 'group'),
                'nullable',
                'exists:students,id',
            ],
        ], [
            'partner_student_id.required' => 'Please select a partner for a group project.',
        ]);

        $partnerId = $data['project_type'] === 'group' ? (int) $data['partner_student_id'] : null;

        // Rule: leader and partner cannot be the same person.
        if ($partnerId && $partnerId === $student->id) {
            throw ValidationException::withMessages([
                'partner_student_id' => 'The partner cannot be the same as the leader.',
            ]);
        }

        // Rule: a student can join only one project (max 2 members per project).
        if ($partnerId) {
            $partner = Student::findOrFail($partnerId);
            if ($partner->hasProject()) {
                throw ValidationException::withMessages([
                    'partner_student_id' => 'The selected partner already belongs to another project.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $student, $partnerId) {
            $project = Project::create([
                'project_type' => $data['project_type'],
                'name' => $data['name'],
                'leader_student_id' => $student->id,
                'department_id' => $student->department_id,
                'frontend_tech' => $data['frontend_tech'],
                'backend_tech' => $data['backend_tech'],
                'abstract' => $data['abstract'],
                'status' => Project::STATUS_SYNOPSIS_REVIEW,
            ]);

            // Leader membership.
            ProjectMember::create([
                'project_id' => $project->id,
                'student_id' => $student->id,
                'role_in_project' => 'leader',
            ]);

            // Partner membership (group only) — enforces the 2-member maximum.
            if ($partnerId) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'student_id' => $partnerId,
                    'role_in_project' => 'partner',
                ]);
            }
        });

        return redirect()->route('student.project.show')
            ->with('success', 'Synopsis submitted. It is now under review.');
    }
}
