<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SynopsisResubmitTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll]);
    }

    private function makeProject(Student $leader, string $status): Project
    {
        $project = Project::create([
            'project_type' => 'single',
            'name' => 'Original Name',
            'leader_student_id' => $leader->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('original idea ', 5),
            'status' => $status,
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => $leader->id,
            'role_in_project' => 'leader',
        ]);

        return $project;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'project_type' => 'single',
            'name' => 'Revised Name',
            'frontend_tech' => 'Vue',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('a properly revised idea ', 3),
        ], $overrides);
    }

    public function test_student_can_edit_synopsis_when_correction_required(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $this->makeProject($leader, Project::STATUS_CORRECTION);

        $this->actingAs($leader->user)
            ->get(route('student.synopsis.edit'))
            ->assertOk();
    }

    public function test_resubmitting_returns_project_to_review_and_bumps_revision(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeProject($leader, Project::STATUS_CORRECTION);

        $this->actingAs($leader->user)
            ->put(route('student.synopsis.update'), $this->payload())
            ->assertRedirect(route('student.project.show'));

        $project->refresh();

        $this->assertSame('Revised Name', $project->name);
        $this->assertSame(Project::STATUS_SYNOPSIS_REVIEW, $project->status);
        $this->assertSame(2, $project->revision);
    }

    public function test_rejected_synopsis_can_also_be_resubmitted(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeProject($leader, Project::STATUS_REJECTED);

        $this->actingAs($leader->user)
            ->put(route('student.synopsis.update'), $this->payload())
            ->assertRedirect(route('student.project.show'));

        $this->assertSame(Project::STATUS_SYNOPSIS_REVIEW, $project->fresh()->status);
    }

    public function test_approved_synopsis_cannot_be_edited(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeProject($leader, Project::STATUS_SYNOPSIS_APPROVED);

        $this->actingAs($leader->user)
            ->get(route('student.synopsis.edit'))
            ->assertRedirect(route('student.project.show'));

        $this->actingAs($leader->user)
            ->put(route('student.synopsis.update'), $this->payload())
            ->assertRedirect(route('student.project.show'));

        $this->assertSame('Original Name', $project->fresh()->name);
    }

    public function test_any_group_member_can_edit_the_synopsis(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $partner = $this->makeStudent('partner@test.com', 'R2');
        $project = $this->makeProject($leader, Project::STATUS_CORRECTION);

        ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => $partner->id,
            'role_in_project' => 'partner',
        ]);

        $this->actingAs($partner->user)
            ->get(route('student.synopsis.edit'))
            ->assertOk();

        $this->actingAs($partner->user)
            ->put(route('student.synopsis.update'), $this->payload())
            ->assertRedirect(route('student.project.show'));

        $this->assertSame('Revised Name', $project->fresh()->name);
    }

    public function test_student_outside_the_project_cannot_edit_the_synopsis(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $outsider = $this->makeStudent('outsider@test.com', 'R3');
        $project = $this->makeProject($leader, Project::STATUS_CORRECTION);

        $this->actingAs($outsider->user)
            ->get(route('student.synopsis.edit'))
            ->assertRedirect(route('student.synopsis.create'));

        $this->assertSame('Original Name', $project->fresh()->name);
    }
}
