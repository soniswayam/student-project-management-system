<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\DegreeLevelSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Models\RevisionRequest;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->seed(RoleSeeder::class);
        DegreeLevelSetting::flushCache();

        $this->dept = Department::create(['name' => 'MSc IT', 'code' => 'MSCIT']);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll, 'semester' => '3']);
    }

    private function makeFaculty(string $email): Faculty
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => 'faculty']);

        return Faculty::create(['user_id' => $user->id, 'department_id' => $this->dept->id]);
    }

    private function makeAdmin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password1', 'role' => 'admin']);
    }

    private function makeProject(Student $student, ?Faculty $guide = null, string $status = Project::STATUS_SYNOPSIS_APPROVED): Project
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

        ProjectMember::create(['project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader']);

        if ($guide) {
            FacultyAssignment::create(['project_id' => $project->id, 'faculty_id' => $guide->id, 'assigned_at' => now()]);
        }

        return $project;
    }

    private function makeAssignment(Faculty $faculty, array $overrides = []): Assignment
    {
        return Assignment::create($overrides + [
            'faculty_id' => $faculty->id,
            'department_id' => $this->dept->id,
            'title' => 'Homework 1',
            'due_date' => now()->addDays(3),
        ]);
    }

    private function finalPayload(int $zipKb = 200): array
    {
        return [
            'report' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            'source_zip' => UploadedFile::fake()->create('src.zip', $zipKb, 'application/zip'),
        ];
    }

    private function storedSubmission(Project $project): ProjectSubmission
    {
        Storage::disk('local')->put('submissions/project_'.$project->id.'/report.pdf', '%PDF-1.4 test');

        return ProjectSubmission::create([
            'project_id' => $project->id,
            'report_path' => 'submissions/project_'.$project->id.'/report.pdf',
            'submitted_at' => now(),
        ]);
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = $this->makeStudent('s1@test.com', 'R1');

        foreach (['admin.dashboard', 'admin.students.index', 'admin.projects.index', 'admin.reports.index', 'admin.settings.edit'] as $route) {
            $this->actingAs($student->user)->get(route($route))
                ->assertRedirect(route('dashboard'));
        }
    }

    public function test_student_cannot_access_faculty_routes(): void
    {
        $student = $this->makeStudent('s1@test.com', 'R1');

        $this->actingAs($student->user)->get(route('faculty.dashboard'))->assertRedirect(route('dashboard'));
        $this->actingAs($student->user)->get(route('faculty.assignments.index'))->assertRedirect(route('dashboard'));
    }

    public function test_staff_route_needs_the_matching_permission(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
        $this->actingAs($admin)->get(route('admin.roles.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_blocked_faculty_cannot_sign_in(): void
    {
        $faculty = $this->makeFaculty('f@test.com');
        $faculty->user->update(['status' => User::STATUS_BLOCKED]);

        $this->post(route('login'), ['email' => 'f@test.com', 'password' => 'password1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_student_cannot_download_another_students_project_file(): void
    {
        $owner = $this->makeStudent('owner@test.com', 'R1');
        $intruder = $this->makeStudent('intruder@test.com', 'R2');
        $submission = $this->storedSubmission($this->makeProject($owner, null, Project::STATUS_FINAL_SUBMITTED));

        $this->actingAs($intruder->user)
            ->get(route('files.project-submission', [$submission, 'report']))
            ->assertForbidden();
    }

    public function test_owner_and_guide_and_admin_can_download_the_project_file(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $owner = $this->makeStudent('owner@test.com', 'R1');
        $submission = $this->storedSubmission($this->makeProject($owner, $guide, Project::STATUS_FINAL_SUBMITTED));

        foreach ([$owner->user, $guide->user, $this->makeAdmin()] as $user) {
            $this->actingAs($user)
                ->get(route('files.project-submission', [$submission, 'report']))
                ->assertOk();
        }
    }

    public function test_another_faculty_cannot_download_the_project_file(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $other = $this->makeFaculty('other@test.com');
        $submission = $this->storedSubmission($this->makeProject($this->makeStudent('o@test.com', 'R1'), $guide, Project::STATUS_FINAL_SUBMITTED));

        $this->actingAs($other->user)
            ->get(route('files.project-submission', [$submission, 'report']))
            ->assertForbidden();
    }

    public function test_student_cannot_download_another_students_assignment_file(): void
    {
        $faculty = $this->makeFaculty('f@test.com');
        $owner = $this->makeStudent('owner@test.com', 'R1');
        $intruder = $this->makeStudent('intruder@test.com', 'R2');
        $assignment = $this->makeAssignment($faculty);

        Storage::disk('local')->put('assignment_submissions/a.pdf', '%PDF-1.4');
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $owner->id,
            'file_path' => 'assignment_submissions/a.pdf', 'submitted_at' => now(), 'status' => 'submitted',
        ]);

        $this->actingAs($intruder->user)
            ->get(route('files.assignment-submission', $submission))
            ->assertForbidden();

        $this->actingAs($owner->user)
            ->get(route('files.assignment-submission', $submission))
            ->assertOk();

        $this->actingAs($faculty->user)
            ->get(route('files.assignment-submission', $submission))
            ->assertOk();
    }

    public function test_legacy_file_on_the_public_disk_is_still_served(): void
    {
        $owner = $this->makeStudent('owner@test.com', 'R1');
        $project = $this->makeProject($owner, null, Project::STATUS_FINAL_SUBMITTED);

        Storage::disk('public')->put('submissions/old.pdf', '%PDF-1.4 old');
        $submission = ProjectSubmission::create([
            'project_id' => $project->id, 'report_path' => 'submissions/old.pdf', 'submitted_at' => now(),
        ]);

        $this->actingAs($owner->user)
            ->get(route('files.project-submission', [$submission, 'report']))
            ->assertOk();
    }

    public function test_student_cannot_overwrite_another_students_assignment_submission(): void
    {
        $faculty = $this->makeFaculty('f@test.com');
        $owner = $this->makeStudent('owner@test.com', 'R1');
        $intruder = $this->makeStudent('intruder@test.com', 'R2');
        $assignment = $this->makeAssignment($faculty);

        $original = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $owner->id,
            'file_path' => 'assignment_submissions/owner.pdf', 'submitted_at' => now(), 'status' => 'submitted',
        ]);

        $this->actingAs($intruder->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('mine.pdf', 20, 'application/pdf'),
            'student_id' => $owner->id,
        ]);

        $this->assertSame('assignment_submissions/owner.pdf', $original->fresh()->file_path);
        $this->assertDatabaseHas('assignment_submissions', ['assignment_id' => $assignment->id, 'student_id' => $intruder->id]);
    }

    public function test_student_cannot_submit_another_departments_assignment(): void
    {
        $otherDept = Department::create(['name' => 'BCA', 'code' => 'BCA']);
        $faculty = $this->makeFaculty('f@test.com');
        $assignment = $this->makeAssignment($faculty, ['department_id' => $otherDept->id]);
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('w.pdf', 20, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_student_cannot_submit_the_same_assignment_twice(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'));
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('first.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('success');

        $first = AssignmentSubmission::first()->file_path;

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('second.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('error', 'You have already submitted this assignment.');

        $this->assertDatabaseCount('assignment_submissions', 1);
        $this->assertSame($first, AssignmentSubmission::first()->file_path);
    }

    public function test_assignment_file_is_stored_privately_with_a_generated_name(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'));
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('../../evil name.pdf', 20, 'application/pdf'),
        ]);

        $path = AssignmentSubmission::first()->file_path;

        $this->assertStringNotContainsString('evil', $path);
        $this->assertStringNotContainsString('..', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_faculty_re_upload_request_allows_one_controlled_resubmission(): void
    {
        $faculty = $this->makeFaculty('f@test.com');
        $assignment = $this->makeAssignment($faculty);
        $student = $this->makeStudent('s@test.com', 'R1');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $student->id,
            'file_path' => 'old.pdf', 'submitted_at' => now()->subDay(), 'status' => 'submitted',
        ]);
        RevisionRequest::create([
            'assignment_submission_id' => $submission->id, 'stage' => RevisionRequest::STAGE_ASSIGNMENT,
            'faculty_id' => $faculty->id, 'reason' => 'Wrong file',
        ]);

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('fixed.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('success');

        $this->assertNotSame('old.pdf', $submission->fresh()->file_path);
        $this->assertNotNull(RevisionRequest::first()->resolved_at);

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('again.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('error', 'You have already submitted this assignment.');
    }

    public function test_student_cannot_submit_after_the_assignment_deadline(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'), ['due_date' => now()->subDays(2)]);
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('late.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('error', 'The assignment submission deadline has passed.');

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_final_submission_is_refused_for_every_unapproved_synopsis_status(): void
    {
        $statuses = [
            Project::STATUS_SYNOPSIS_PENDING,
            Project::STATUS_SYNOPSIS_REVIEW,
            Project::STATUS_CORRECTION,
            Project::STATUS_REJECTED,
        ];

        foreach ($statuses as $i => $status) {
            $student = $this->makeStudent("s{$i}@test.com", "R{$i}");
            $project = $this->makeProject($student, null, $status);

            $this->actingAs($student->user)
                ->post(route('student.submission.store'), $this->finalPayload())
                ->assertRedirect(route('student.project.show'))
                ->assertSessionHas('error', 'Your synopsis must be approved before final project submission.');

            $this->assertNull($project->fresh()->submission, "Upload was accepted for status {$status}");
            $this->assertSame($status, $project->fresh()->status);
        }
    }

    public function test_final_submission_is_accepted_after_approval(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = $this->makeProject($student);

        $this->actingAs($student->user)
            ->post(route('student.submission.store'), $this->finalPayload())
            ->assertSessionHas('success');

        $submission = $project->fresh()->submission;
        $this->assertNotNull($submission);
        $this->assertSame(Project::STATUS_FINAL_SUBMITTED, $project->fresh()->status);
        Storage::disk('local')->assertExists($submission->source_zip_path);
    }

    public function test_reviewed_project_files_cannot_be_replaced_without_a_re_upload_request(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = $this->makeProject($student, null, Project::STATUS_COMPLETED);
        $this->storedSubmission($project);

        $this->actingAs($student->user)
            ->post(route('student.submission.store'), $this->finalPayload())
            ->assertSessionHas('error');

        $this->assertSame(Project::STATUS_COMPLETED, $project->fresh()->status);
    }

    public function test_faculty_cannot_review_a_project_they_do_not_guide(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $other = $this->makeFaculty('other@test.com');
        $project = $this->makeProject($this->makeStudent('s@test.com', 'R1'), $guide, Project::STATUS_SYNOPSIS_REVIEW);

        $this->actingAs($other->user)
            ->post(route('faculty.projects.reviewSynopsis', $project), ['action' => 'approved'])
            ->assertForbidden();
        $this->actingAs($other->user)->get(route('faculty.projects.show', $project))->assertForbidden();

        $this->assertSame(Project::STATUS_SYNOPSIS_REVIEW, $project->fresh()->status);
    }

    public function test_faculty_cannot_push_a_completed_project_back_to_synopsis_approved(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $project = $this->makeProject($this->makeStudent('s@test.com', 'R1'), $guide, Project::STATUS_COMPLETED);

        $this->actingAs($guide->user)
            ->post(route('faculty.projects.reviewSynopsis', $project), ['action' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame(Project::STATUS_COMPLETED, $project->fresh()->status);
    }

    public function test_student_cannot_set_marks_or_status_on_an_assignment_submission(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'), ['max_marks' => 10]);
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('w.pdf', 20, 'application/pdf'),
            'marks' => 10,
            'status' => 'checked',
            'feedback' => 'Excellent',
            'checked_at' => now()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'student_id' => $student->id, 'marks' => null, 'status' => 'submitted', 'feedback' => null, 'checked_at' => null,
        ]);
    }

    public function test_student_cannot_set_project_status_marks_or_guide_through_the_synopsis_form(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $faculty = $this->makeFaculty('f@test.com');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Inventory Tracker',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A small inventory tracker for the college store room and lab.',
            'status' => Project::STATUS_COMPLETED,
            'marks' => 100,
            'faculty_id' => $faculty->id,
            'leader_student_id' => 999,
        ])->assertRedirect(route('student.project.show'));

        $project = Project::first();
        $this->assertSame(Project::STATUS_SYNOPSIS_REVIEW, $project->status);
        $this->assertNull($project->marks);
        $this->assertSame($student->id, $project->leader_student_id);
        $this->assertDatabaseCount('faculty_assignments', 0);
    }

    public function test_student_cannot_post_to_the_faculty_grading_route(): void
    {
        $guide = $this->makeFaculty('guide@test.com');
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = $this->makeProject($student, $guide, Project::STATUS_FINAL_SUBMITTED);

        $this->actingAs($student->user)
            ->post(route('faculty.projects.reviewFinal', $project), ['marks' => 100, 'complete' => 1])
            ->assertRedirect(route('dashboard'));

        $this->assertNull($project->fresh()->marks);
        $this->assertSame(Project::STATUS_FINAL_SUBMITTED, $project->fresh()->status);
    }

    public function test_invalid_assignment_file_type_is_rejected(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'));
        $student = $this->makeStudent('s@test.com', 'R1');

        foreach (['shell.php' => 'application/x-php', 'run.exe' => 'application/x-msdownload', 'doc.pdf.php' => 'application/x-php'] as $name => $mime) {
            $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
                'file' => UploadedFile::fake()->create($name, 5, $mime),
            ])->assertSessionHasErrors('file');
        }

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_oversized_assignment_file_is_rejected(): void
    {
        $assignment = $this->makeAssignment($this->makeFaculty('f@test.com'));
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('huge.pdf', 51201, 'application/pdf'),
        ])->assertSessionHasErrors(['file' => 'The assignment file must not exceed 50 MB.']);

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_oversized_project_zip_is_rejected(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = $this->makeProject($student);

        $this->actingAs($student->user)
            ->post(route('student.submission.store'), $this->finalPayload(307201))
            ->assertSessionHasErrors(['source_zip' => 'The project ZIP file must not exceed 300 MB.']);

        $this->assertNull($project->fresh()->submission);
    }

    public function test_project_source_must_be_an_archive(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $this->makeProject($student);

        $this->actingAs($student->user)->post(route('student.submission.store'), [
            'report' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            'source_zip' => UploadedFile::fake()->create('src.exe', 100, 'application/x-msdownload'),
        ])->assertSessionHasErrors('source_zip');
    }

    public function test_project_zip_just_under_the_limit_is_accepted(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = $this->makeProject($student);

        $this->actingAs($student->user)
            ->post(route('student.submission.store'), $this->finalPayload(307200))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertNotNull($project->fresh()->submission);
    }

    public function test_oversized_screenshot_is_rejected(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $this->makeProject($student);

        $this->actingAs($student->user)->post(route('student.submission.store'), $this->finalPayload() + [
            'screenshots' => [UploadedFile::fake()->create('big.png', 10241, 'image/png')],
        ])->assertSessionHasErrors('screenshots.0');
    }

    public function test_student_cannot_join_a_second_project_as_a_partner(): void
    {
        $taken = $this->makeStudent('taken@test.com', 'R1');
        $this->makeProject($taken);
        $leader = $this->makeStudent('leader@test.com', 'R2');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Second Project',
            'partner_student_id' => $taken->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A different idea that tries to reuse a partner who is already taken.',
        ])->assertSessionHasErrors('partner_student_id');

        $this->assertSame(1, Project::count());
        $this->assertSame(1, ProjectMember::where('student_id', $taken->id)->count());
    }

    public function test_project_member_cannot_create_a_second_project(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $this->makeProject($student);

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Another One',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'Trying to start a second project while already in one.',
        ])->assertRedirect(route('student.project.show'));

        $this->assertSame(1, Project::count());
    }

    public function test_group_size_rule_is_enforced_for_an_individual_only_level(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => $this->dept->level ?? Department::LEVEL_GRADUATION],
            ['max_group_size' => 1]
        );
        DegreeLevelSetting::flushCache();

        $leader = $this->makeStudent('leader@test.com', 'R1');
        $partner = $this->makeStudent('partner@test.com', 'R2');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Team Attempt',
            'partner_student_id' => $partner->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A group attempt where the course only allows individual projects.',
        ])->assertSessionHasErrors('project_type');

        $this->assertSame(0, Project::count());
    }

    public function test_blocked_student_cannot_be_added_as_a_partner(): void
    {
        $leader = $this->makeStudent('leader@test.com', 'R1');
        $partner = $this->makeStudent('partner@test.com', 'R2');
        $partner->user->update(['status' => User::STATUS_BLOCKED]);

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Team Attempt',
            'partner_student_id' => $partner->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A group attempt that names a blocked account as the partner.',
        ])->assertSessionHasErrors('partner_student_id');

        $this->assertSame(0, Project::count());
    }
}
