<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Models\RevisionRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Viva;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();

        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
        CollegeSetting::create(['name' => 'Test College', 'duplicate_block_percent' => 100]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'root@test.com', 'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function makeFaculty(string $email, string $name = 'Guide'): Faculty
    {
        $user = User::create([
            'name' => $name, 'email' => $email, 'password' => 'password', 'role' => 'faculty',
        ]);

        return Faculty::create([
            'user_id' => $user->id, 'department_id' => $this->dept->id, 'designation' => 'Professor',
        ]);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create([
            'name' => 'S'.$roll, 'email' => $email, 'password' => 'password', 'role' => 'student',
        ]);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll, 'semester' => '4',
        ]);
    }

    private function makeProject(Student $student, ?Faculty $guide = null, string $status = Project::STATUS_FINAL_SUBMITTED): Project
    {
        $project = Project::create([
            'project_type' => 'single',
            'name' => 'Project '.$student->roll_no,
            'leader_student_id' => $student->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'An abstract about '.$student->roll_no,
            'status' => $status,
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader',
        ]);

        if ($guide) {
            FacultyAssignment::create([
                'project_id' => $project->id, 'faculty_id' => $guide->id, 'assigned_at' => now(),
            ]);
        }

        return $project->fresh();
    }

    public function test_the_assigned_guide_can_schedule_a_viva(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        $this->actingAs($guide->user)->post(route('faculty.projects.viva.store', $project), [
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'venue' => 'Lab 3',
            'mode' => 'offline',
        ])->assertRedirect();

        $this->assertDatabaseHas('vivas', ['project_id' => $project->id, 'venue' => 'Lab 3']);

        $viva = Viva::where('project_id', $project->id)->first();
        $this->assertTrue($viva->panelists->contains('id', $guide->id));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $project->leader->user_id,
            'title' => 'Viva scheduled',
        ]);
    }

    public function test_a_faculty_who_does_not_guide_the_project_cannot_schedule_its_viva(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $other = $this->makeFaculty('other@test.com', 'Other');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        $this->actingAs($other->user)->post(route('faculty.projects.viva.store', $project), [
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'mode' => 'offline',
        ])->assertForbidden();

        $this->assertDatabaseCount('vivas', 0);
    }

    public function test_an_online_viva_requires_a_meeting_link(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        $this->actingAs($guide->user)->post(route('faculty.projects.viva.store', $project), [
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'mode' => 'online',
        ])->assertSessionHasErrors('meeting_link');
    }

    public function test_no_viva_row_means_nothing_renders_for_the_student(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');
        $this->makeProject($student, $guide);

        $this->actingAs($student->user)
            ->get(route('student.project.show'))
            ->assertOk()
            ->assertDontSee('Viva');
    }

    public function test_rescheduling_updates_the_same_slot_rather_than_adding_one(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        foreach ([now()->addWeek(), now()->addWeeks(2)] as $when) {
            $this->actingAs($guide->user)->post(route('faculty.projects.viva.store', $project), [
                'scheduled_at' => $when->format('Y-m-d\TH:i'),
                'mode' => 'offline',
            ]);
        }

        $this->assertDatabaseCount('vivas', 1);
    }

    public function test_an_open_request_reopens_a_closed_window_for_that_student(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');
        $project = $this->makeProject($student, $guide, Project::STATUS_FINAL_SUBMITTED);

        ProjectSubmission::create([
            'project_id' => $project->id,
            'report_path' => 'x.pdf',
            'source_zip_path' => 'x.zip',
            'submitted_at' => now()->subDays(5),
        ]);

        $this->dept->update(['submission_deadline' => now()->subDays(10)]);

        $this->actingAs($student->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('r.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('student.project.show'));

        $this->actingAs($guide->user)->post(route('faculty.projects.requestReupload', $project), [
            'reason' => 'The testing chapter is missing',
        ])->assertRedirect();

        $this->assertDatabaseHas('revision_requests', [
            'project_id' => $project->id,
            'stage' => RevisionRequest::STAGE_FINAL,
            'resolved_at' => null,
        ]);

        $this->actingAs($student->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('r2.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('student.project.show'));

        $this->assertNotNull(RevisionRequest::first()->resolved_at);
    }

    public function test_a_requested_re_upload_is_not_flagged_late(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');
        $project = $this->makeProject($student, $guide);

        ProjectSubmission::create([
            'project_id' => $project->id, 'report_path' => 'x.pdf',
            'source_zip_path' => 'x.zip', 'submitted_at' => now()->subDays(5),
        ]);

        $this->dept->update(['submission_deadline' => now()->subDays(10)]);

        $this->actingAs($guide->user)->post(route('faculty.projects.requestReupload', $project), [
            'reason' => 'Please resend',
        ]);

        $this->actingAs($student->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('r2.pdf', 10, 'application/pdf'),
        ]);

        $this->assertFalse((bool) ProjectSubmission::first()->is_late);
    }

    public function test_the_bypass_does_not_leak_to_other_students(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $guide = $this->makeFaculty('guide@test.com');
        $asked = $this->makeStudent('asked@test.com', 'R1');
        $other = $this->makeStudent('other@test.com', 'R2');

        $askedProject = $this->makeProject($asked, $guide);
        $this->makeProject($other, $guide);

        ProjectSubmission::create([
            'project_id' => $askedProject->id, 'report_path' => 'x.pdf',
            'source_zip_path' => 'x.zip', 'submitted_at' => now()->subDays(5),
        ]);

        $this->dept->update(['submission_deadline' => now()->subDays(10)]);

        $this->actingAs($guide->user)->post(route('faculty.projects.requestReupload', $askedProject), [
            'reason' => 'Please resend',
        ]);

        $this->actingAs($other->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('r.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('student.project.show'));

        $this->assertDatabaseMissing('project_submissions', ['project_id' => $other->ledProject->id ?? 0]);
    }

    public function test_only_one_open_request_per_project(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        foreach (['first reason', 'second reason'] as $reason) {
            $this->actingAs($guide->user)
                ->post(route('faculty.projects.requestReupload', $project), ['reason' => $reason]);
        }

        $this->assertSame(1, RevisionRequest::where('project_id', $project->id)->count());
    }

    public function test_replacing_a_guide_records_history_and_tells_everyone(): void
    {
        $outgoing = $this->makeFaculty('out@test.com', 'Outgoing');
        $incoming = $this->makeFaculty('in@test.com', 'Incoming');
        $student = $this->makeStudent('s1@test.com', 'R1');
        $project = $this->makeProject($student, $outgoing);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.projects.assign', $project), [
                'faculty_id' => $incoming->id,
                'reason' => 'Original guide on leave',
            ])->assertRedirect();

        $this->assertDatabaseHas('faculty_assignment_histories', [
            'project_id' => $project->id,
            'from_faculty_id' => $outgoing->id,
            'to_faculty_id' => $incoming->id,
            'reason' => 'Original guide on leave',
        ]);

        $this->assertDatabaseHas('faculty_assignments', [
            'project_id' => $project->id, 'faculty_id' => $incoming->id,
        ]);

        $this->assertDatabaseHas('notifications', ['user_id' => $outgoing->user_id, 'title' => 'Project reassigned']);
        $this->assertDatabaseHas('notifications', ['user_id' => $incoming->user_id, 'title' => 'Project reassigned to you']);
        $this->assertDatabaseHas('notifications', ['user_id' => $student->user_id, 'title' => 'Your project guide has changed']);
    }

    public function test_reassigning_to_the_same_guide_changes_nothing(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s1@test.com', 'R1'), $guide);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.projects.assign', $project), ['faculty_id' => $guide->id])
            ->assertRedirect();

        $this->assertDatabaseCount('faculty_assignment_histories', 0);
    }

    public function test_bulk_assign_reports_assigned_and_skipped(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $a = $this->makeProject($this->makeStudent('s1@test.com', 'R1'));
        $b = $this->makeProject($this->makeStudent('s2@test.com', 'R2'));
        $c = $this->makeProject($this->makeStudent('s3@test.com', 'R3'), $guide);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.projects.bulkAssign'), [
                'faculty_id' => $guide->id,
                'project_ids' => [$a->id, $b->id, $c->id],
            ])->assertRedirect();

        $this->assertSame(3, FacultyAssignment::where('faculty_id', $guide->id)->count());
        $this->assertDatabaseCount('faculty_assignment_histories', 2);
    }

    public function test_bulk_assign_warns_when_it_pushes_past_capacity(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $guide->update(['max_projects' => 1]);

        $a = $this->makeProject($this->makeStudent('s1@test.com', 'R1'));
        $b = $this->makeProject($this->makeStudent('s2@test.com', 'R2'));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.projects.bulkAssign'), [
                'faculty_id' => $guide->id,
                'project_ids' => [$a->id, $b->id],
            ])
            ->assertSessionHas('warning');

        $this->assertSame(2, FacultyAssignment::where('faculty_id', $guide->id)->count());
    }

    public function test_bulk_assign_needs_the_assign_permission(): void
    {
        $student = $this->makeStudent('s1@test.com', 'R1');
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($student);

        $this->actingAs($student->user)
            ->post(route('admin.projects.bulkAssign'), [
                'faculty_id' => $guide->id,
                'project_ids' => [$project->id],
            ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('faculty_assignments', 0);
    }

    public function test_marks_are_optional_on_an_unmarked_assignment(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');

        $assignment = Assignment::create([
            'faculty_id' => $guide->id, 'department_id' => $this->dept->id,
            'subject' => 'DBMS', 'type' => 'Theory', 'title' => 'ER Diagrams',
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $student->id,
            'file_path' => 'x.pdf', 'submitted_at' => now(),
        ]);

        $this->actingAs($guide->user)
            ->post(route('faculty.assignments.check', [$assignment, $submission]), [
                'feedback' => 'Good work',
            ])->assertRedirect();

        $submission->refresh();
        $this->assertTrue($submission->isChecked());
        $this->assertNull($submission->marks);
        $this->assertNull($submission->marksLabel());
    }

    public function test_marks_are_recorded_and_capped_by_the_assignment_maximum(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');

        $assignment = Assignment::create([
            'faculty_id' => $guide->id, 'department_id' => $this->dept->id,
            'subject' => 'DBMS', 'type' => 'Theory', 'title' => 'ER Diagrams', 'max_marks' => 10,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $student->id,
            'file_path' => 'x.pdf', 'submitted_at' => now(),
        ]);

        $this->actingAs($guide->user)
            ->post(route('faculty.assignments.check', [$assignment, $submission]), ['marks' => 11])
            ->assertSessionHasErrors('marks');

        $this->actingAs($guide->user)
            ->post(route('faculty.assignments.check', [$assignment, $submission]), ['marks' => 8])
            ->assertRedirect();

        $this->assertSame('8 / 10', $submission->fresh()->marksLabel());
    }

    public function test_dry_run_sends_nothing_and_a_second_run_does_not_double_send(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s1@test.com', 'R1');
        $this->makeProject($student, $guide, Project::STATUS_SYNOPSIS_APPROVED);

        $this->dept->update(['submission_deadline' => now()->addDay()]);

        $this->artisan('reminders:send --dry-run')->assertSuccessful();
        $this->assertDatabaseCount('reminder_logs', 0);
        $this->assertDatabaseCount('notifications', 0);

        $this->artisan('reminders:send')->assertSuccessful();
        $firstRun = Notification::count();
        $this->assertGreaterThan(0, $firstRun);

        $this->artisan('reminders:send')->assertSuccessful();
        $this->assertSame($firstRun, Notification::count());
    }
}
