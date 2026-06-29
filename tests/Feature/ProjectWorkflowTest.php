<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll]);
    }

    private function makeFaculty(string $email): Faculty
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password', 'role' => 'faculty']);

        return Faculty::create(['user_id' => $user->id, 'department_id' => $this->dept->id]);
    }

    public function test_student_can_submit_group_synopsis(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $partner = $this->makeStudent('partner@test.com', 'R2');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Library System',
            'partner_student_id' => $partner->id,
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('A great project idea. ', 3),
        ])->assertRedirect(route('student.project.show'));

        $this->assertDatabaseHas('projects', ['name' => 'Library System', 'status' => Project::STATUS_SYNOPSIS_REVIEW]);
        $this->assertEquals(2, ProjectMember::count());
    }

    public function test_partner_is_required_for_group(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'No Partner',
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('idea ', 10),
        ])->assertSessionHasErrors('partner_student_id');

        $this->assertEquals(0, Project::count());
    }

    public function test_student_cannot_create_two_projects(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');

        $payload = [
            'project_type' => 'single',
            'name' => 'First',
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('idea ', 10),
        ];

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), $payload);
        $this->actingAs($leader->user)->post(route('student.synopsis.store'), $payload + ['name' => 'Second']);

        $this->assertEquals(1, Project::count());
    }

    public function test_partner_already_in_project_is_rejected(): void
    {
        $leader1 = $this->makeStudent('l1@test.com', 'R1');
        $partner = $this->makeStudent('p@test.com', 'R2');
        $leader2 = $this->makeStudent('l2@test.com', 'R3');

        // leader1 + partner form a group
        $this->actingAs($leader1->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group', 'name' => 'P1', 'partner_student_id' => $partner->id,
            'frontend_tech' => 'React', 'backend_tech' => 'Laravel', 'abstract' => str_repeat('idea ', 10),
        ]);

        // leader2 tries to take the already-engaged partner
        $this->actingAs($leader2->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group', 'name' => 'P2', 'partner_student_id' => $partner->id,
            'frontend_tech' => 'React', 'backend_tech' => 'Laravel', 'abstract' => str_repeat('idea ', 10),
        ])->assertSessionHasErrors('partner_student_id');

        $this->assertEquals(1, Project::count());
    }

    public function test_student_cannot_upload_before_synopsis_approved(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single', 'name' => 'P', 'frontend_tech' => 'R',
            'backend_tech' => 'L', 'abstract' => str_repeat('idea ', 10),
        ]);

        // Still under review -> upload form should redirect away with an error.
        $this->actingAs($leader->user)->get(route('student.submission.create'))
            ->assertRedirectContains('project');
    }

    public function test_full_flow_assign_review_upload_grade(): void
    {
        Storage::fake('public');

        $leader = $this->makeStudent('leader@test.com', 'R1');
        $faculty = $this->makeFaculty('fac@test.com');
        $admin = User::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'password', 'role' => 'admin']);

        // 1. Submit synopsis
        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single', 'name' => 'P', 'frontend_tech' => 'R',
            'backend_tech' => 'L', 'abstract' => str_repeat('idea ', 10),
        ]);
        $project = Project::first();

        // 2. Admin assigns faculty
        $this->actingAs($admin)->post(route('admin.projects.assign', $project), ['faculty_id' => $faculty->id]);
        $this->assertDatabaseHas('faculty_assignments', ['project_id' => $project->id, 'faculty_id' => $faculty->id]);

        // 3. Faculty approves synopsis
        $this->actingAs($faculty->user)->post(route('faculty.projects.reviewSynopsis', $project), [
            'action' => 'approved', 'comments' => 'Good.',
        ]);
        $this->assertEquals(Project::STATUS_SYNOPSIS_APPROVED, $project->fresh()->status);

        // 4. Student uploads final files
        $this->actingAs($leader->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            'source_zip' => UploadedFile::fake()->create('src.zip', 200, 'application/zip'),
            'screenshots' => [UploadedFile::fake()->image('shot.png')],
        ])->assertRedirect(route('student.project.show'));
        $this->assertEquals(Project::STATUS_FINAL_SUBMITTED, $project->fresh()->status);
        $this->assertDatabaseHas('project_submissions', ['project_id' => $project->id]);

        // 5. Faculty grades and completes
        $this->actingAs($faculty->user)->post(route('faculty.projects.reviewFinal', $project), [
            'marks' => 90, 'comments' => 'Great', 'final_remarks' => 'Well done', 'complete' => 1,
        ]);
        $fresh = $project->fresh();
        $this->assertEquals(Project::STATUS_COMPLETED, $fresh->status);
        $this->assertEquals(90, $fresh->marks);
    }

    public function test_role_middleware_blocks_cross_role_access(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');

        $this->actingAs($leader->user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($leader->user)->get(route('faculty.dashboard'))->assertForbidden();
    }
}
