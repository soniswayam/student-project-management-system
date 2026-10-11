<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CollegeSetting;
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

class SubmissionDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);

        CollegeSetting::create([
            'name' => 'Test College',
            'project_grace_hours' => 12,
            'assignment_grace_hours' => 6,
        ]);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll]);
    }

    private function makeApprovedProject(Student $leader): Project
    {
        $project = Project::create([
            'project_type' => 'single',
            'name' => 'Deadline Project',
            'leader_student_id' => $leader->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('idea ', 10),
            'status' => Project::STATUS_SYNOPSIS_APPROVED,
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => $leader->id,
            'role_in_project' => 'leader',
        ]);

        return $project;
    }

    private function uploadPayload(): array
    {
        return [
            'report' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            'source_zip' => UploadedFile::fake()->create('src.zip', 100, 'application/zip'),
        ];
    }

    public function test_submission_inside_grace_period_is_accepted_and_flagged_late(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeApprovedProject($leader);

        $this->dept->update(['submission_deadline' => now()->subHours(2)]);

        $this->actingAs($leader->user)
            ->post(route('student.submission.store'), $this->uploadPayload())
            ->assertRedirect(route('student.project.show'));

        $submission = $project->fresh()->submission;

        $this->assertNotNull($submission);
        $this->assertTrue($submission->is_late);
        $this->assertGreaterThan(0, $submission->late_by_minutes);
        $this->assertSame(Project::STATUS_FINAL_SUBMITTED, $project->fresh()->status);
    }

    public function test_submission_after_grace_period_is_refused(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeApprovedProject($leader);

        $this->dept->update(['submission_deadline' => now()->subHours(20)]);

        $this->actingAs($leader->user)
            ->post(route('student.submission.store'), $this->uploadPayload())
            ->assertRedirect(route('student.project.show'));

        $this->assertNull($project->fresh()->submission);
        $this->assertSame(Project::STATUS_SYNOPSIS_APPROVED, $project->fresh()->status);
    }

    public function test_on_time_submission_is_not_flagged_late(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeApprovedProject($leader);

        $this->dept->update(['submission_deadline' => now()->addDays(3)]);

        $this->actingAs($leader->user)
            ->post(route('student.submission.store'), $this->uploadPayload())
            ->assertRedirect(route('student.project.show'));

        $submission = $project->fresh()->submission;

        $this->assertFalse($submission->is_late);
        $this->assertNull($submission->late_by_minutes);
    }

    public function test_project_override_beats_the_department_deadline(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeApprovedProject($leader);

        $this->dept->update(['submission_deadline' => now()->subDays(2)]);
        $project->update(['deadline_override' => now()->addDay()]);

        $this->actingAs($leader->user)
            ->post(route('student.submission.store'), $this->uploadPayload())
            ->assertRedirect(route('student.project.show'));

        $submission = $project->fresh()->submission;

        $this->assertNotNull($submission);
        $this->assertFalse($submission->is_late);
        $this->assertSame('override', $project->fresh()->deadlineSource());
    }

    public function test_no_deadline_means_submissions_stay_open(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $project = $this->makeApprovedProject($leader);

        $this->assertFalse($project->submissionWindow()->hasDeadline());

        $this->actingAs($leader->user)
            ->post(route('student.submission.store'), $this->uploadPayload())
            ->assertRedirect(route('student.project.show'));

        $this->assertNotNull($project->fresh()->submission);
    }

    public function test_assignment_submission_after_six_hour_grace_is_refused(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $facultyUser = User::create(['name' => 'F', 'email' => 'f@test.com', 'password' => 'password1', 'role' => 'faculty']);
        $faculty = Faculty::create(['user_id' => $facultyUser->id, 'department_id' => $this->dept->id]);

        $assignment = Assignment::create([
            'faculty_id' => $faculty->id,
            'department_id' => $this->dept->id,
            'title' => 'Overdue Homework',
            'due_date' => now()->subHours(10),
        ]);

        $this->actingAs($student->user)
            ->post(route('student.assignments.submit', $assignment), [
                'file' => UploadedFile::fake()->create('work.pdf', 50, 'application/pdf'),
            ])->assertRedirect();

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_assignment_submission_inside_grace_is_flagged_late(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $facultyUser = User::create(['name' => 'F', 'email' => 'f@test.com', 'password' => 'password1', 'role' => 'faculty']);
        $faculty = Faculty::create(['user_id' => $facultyUser->id, 'department_id' => $this->dept->id]);

        $assignment = Assignment::create([
            'faculty_id' => $faculty->id,
            'department_id' => $this->dept->id,
            'title' => 'Just Overdue',
            'due_date' => now()->subHours(2),
        ]);

        $this->actingAs($student->user)
            ->post(route('student.assignments.submit', $assignment), [
                'file' => UploadedFile::fake()->create('work.pdf', 50, 'application/pdf'),
            ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'is_late' => true,
        ]);
    }
}
