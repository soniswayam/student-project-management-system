<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\DegreeLevelSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectReview;
use App\Models\RevisionRequest;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BugFixRegressionTest extends TestCase
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

    private function user(string $email, string $role, string $status = User::STATUS_ACTIVE): User
    {
        return User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => $role, 'status' => $status]);
    }

    private function student(string $email, string $roll, string $status = User::STATUS_ACTIVE, string $semester = '3'): Student
    {
        $user = $this->user($email, 'student', $status);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll, 'semester' => $semester]);
    }

    private function faculty(string $email): Faculty
    {
        return Faculty::create(['user_id' => $this->user($email, 'faculty')->id, 'department_id' => $this->dept->id]);
    }

    private function staffWith(array $permissions): User
    {
        Role::create(['name' => 'staff_mgr', 'label' => 'Staff Mgr', 'permissions' => $permissions, 'is_staff' => true, 'is_system' => false]);

        return $this->user('mgr@test.com', 'staff_mgr');
    }

    private function project(Student $leader, ?Faculty $guide, string $status, ?Student $partner = null): Project
    {
        $project = Project::create([
            'project_type' => $partner ? 'group' : 'single',
            'name' => 'Library System '.$leader->roll_no,
            'leader_student_id' => $leader->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A library system that tracks books, members and fines for a college.',
            'status' => $status,
        ]);
        ProjectMember::create(['project_id' => $project->id, 'student_id' => $leader->id, 'role_in_project' => 'leader']);
        if ($partner) {
            ProjectMember::create(['project_id' => $project->id, 'student_id' => $partner->id, 'role_in_project' => 'partner']);
        }
        if ($guide) {
            FacultyAssignment::create(['project_id' => $project->id, 'faculty_id' => $guide->id, 'assigned_at' => now()]);
        }

        return $project;
    }

    public function test_staff_cannot_create_a_super_admin(): void
    {
        $this->actingAs($this->staffWith(['admins.manage']))
            ->post(route('admin.admins.store'), [
                'name' => 'Evil', 'email' => 'evil@test.com', 'role' => 'super_admin',
                'password' => 'Password123', 'password_confirmation' => 'Password123',
            ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'evil@test.com']);
    }

    public function test_staff_cannot_edit_a_super_admin_account(): void
    {
        $super = $this->user('root@test.com', 'super_admin');

        $this->actingAs($this->staffWith(['admins.manage']))
            ->put(route('admin.admins.update', $super), [
                'name' => 'Root', 'email' => 'root@test.com', 'role' => 'admin',
                'password' => 'Hacked1234', 'password_confirmation' => 'Hacked1234',
            ])->assertForbidden();

        $this->assertSame('super_admin', $super->fresh()->role);
    }

    public function test_super_admin_can_still_create_a_super_admin(): void
    {
        $this->actingAs($this->user('root@test.com', 'super_admin'))
            ->post(route('admin.admins.store'), [
                'name' => 'Second', 'email' => 'second@test.com', 'role' => 'super_admin',
                'password' => 'Password123', 'password_confirmation' => 'Password123',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'second@test.com', 'role' => 'super_admin']);
    }

    public function test_staff_cannot_change_their_own_role_permissions(): void
    {
        $staff = $this->staffWith(['roles.manage']);
        $role = Role::where('name', 'staff_mgr')->first();

        $this->actingAs($staff)
            ->put(route('admin.roles.update', $role), ['label' => 'Staff Mgr', 'permissions' => ['roles.manage', 'admins.manage']])
            ->assertForbidden();
    }

    public function test_duplicate_role_name_is_a_validation_error(): void
    {
        $super = $this->user('root@test.com', 'super_admin');

        $this->actingAs($super)->post(route('admin.roles.store'), ['label' => 'HOD'])->assertSessionHasNoErrors();
        $this->actingAs($super)->post(route('admin.roles.store'), ['label' => 'HOD'])->assertSessionHasErrors('label');
        $this->actingAs($super)->post(route('admin.roles.store'), ['label' => 'Admin'])->assertSessionHasErrors('label');
    }

    public function test_faculty_with_records_cannot_be_deleted(): void
    {
        $guide = $this->faculty('guide@test.com');
        $this->project($this->student('s1@test.com', 'R1'), $guide, Project::STATUS_SYNOPSIS_APPROVED);

        $this->actingAs($this->user('root@test.com', 'super_admin'))
            ->delete(route('admin.faculties.destroy', $guide))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('faculties', ['id' => $guide->id]);
    }

    public function test_department_in_use_cannot_be_deleted(): void
    {
        $this->student('s1@test.com', 'R1');

        $this->actingAs($this->user('root@test.com', 'super_admin'))
            ->delete(route('admin.departments.destroy', $this->dept))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $this->dept->id]);
    }

    public function test_partner_editing_synopsis_stays_in_the_project(): void
    {
        DegreeLevelSetting::updateOrCreate(['level' => 'graduation'], ['max_group_size' => 2]);
        DegreeLevelSetting::flushCache();

        $leader = $this->student('lead@test.com', 'R1');
        $partner = $this->student('part@test.com', 'R2');
        $project = $this->project($leader, null, Project::STATUS_CORRECTION, $partner);

        $this->actingAs($partner->user)->put(route('student.synopsis.update'), [
            'project_type' => 'single',
            'name' => 'Library System Revised',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A revised library system that tracks books, members and fines for a college.',
        ]);

        $this->assertDatabaseHas('project_members', ['project_id' => $project->id, 'student_id' => $partner->id]);
        $this->assertSame($partner->id, $project->fresh()->leader_student_id);
        $this->assertSame(Project::STATUS_SYNOPSIS_REVIEW, $project->fresh()->status);
    }

    public function test_editing_an_alumnus_keeps_them_graduated(): void
    {
        $alum = $this->student('alum@test.com', 'R1', User::STATUS_GRADUATED);

        $this->actingAs($this->user('root@test.com', 'super_admin'))->put(route('admin.students.update', $alum), [
            'name' => 'Alum', 'email' => 'alum@test.com', 'status' => 'active',
            'department_id' => $this->dept->id, 'roll_no' => 'R1', 'semester' => '3', 'phone' => '9999999999',
        ])->assertSessionHasNoErrors();

        $this->assertSame(User::STATUS_GRADUATED, $alum->user->fresh()->status);
    }

    public function test_blocked_student_is_not_moved_to_alumni(): void
    {
        $blocked = $this->student('b@test.com', 'R1', User::STATUS_BLOCKED);

        $this->actingAs($this->user('root@test.com', 'super_admin'))
            ->post(route('admin.alumni.store'), ['student_ids' => [$blocked->id]]);

        $this->assertSame(User::STATUS_BLOCKED, $blocked->user->fresh()->status);
    }

    public function test_admin_assignment_is_filed_under_the_current_year(): void
    {
        $year = AcademicYear::create(['label' => '2099-00']);
        $year->markCurrent();
        $guide = $this->faculty('g@test.com');

        $this->actingAs($this->user('root@test.com', 'super_admin'))->post(route('admin.assignments.store'), [
            'faculty_id' => $guide->id, 'department_id' => $this->dept->id, 'subject' => 'DBMS',
            'type' => 'Theory', 'title' => 'Normalization',
        ])->assertSessionHasNoErrors();

        $this->assertSame($year->id, Assignment::first()->academic_year_id);
    }

    public function test_assignment_targets_only_current_active_students(): void
    {
        $this->student('a@test.com', 'R1');
        $this->student('alum@test.com', 'R2', User::STATUS_GRADUATED);
        $this->student('blk@test.com', 'R3', User::STATUS_BLOCKED);
        $this->student('pen@test.com', 'R4', User::STATUS_PENDING);
        $this->student('other-sem@test.com', 'R5', User::STATUS_ACTIVE, '1');

        $assignment = Assignment::create(['faculty_id' => $this->faculty('g@test.com')->id, 'department_id' => $this->dept->id, 'semester' => '3', 'title' => 'Hw']);

        $this->assertSame(1, $assignment->targetStudents()->count());
        $this->assertSame([$assignment->id => 1], Assignment::targetCounts([$assignment]));
    }

    public function test_marks_are_not_sent_before_completion(): void
    {
        DegreeLevelSetting::updateOrCreate(['level' => 'graduation'], ['max_group_size' => 1, 'max_marks' => 100]);
        DegreeLevelSetting::flushCache();

        $guide = $this->faculty('g@test.com');
        $student = $this->student('s@test.com', 'R1');
        $project = $this->project($student, $guide, Project::STATUS_FINAL_SUBMITTED);

        $this->actingAs($guide->user)->post(route('faculty.projects.reviewFinal', $project), ['marks' => 78]);
        $this->assertStringNotContainsString('78', Notification::where('user_id', $student->user_id)->latest('id')->first()->message);

        $this->actingAs($guide->user)->post(route('faculty.projects.reviewFinal', $project), ['marks' => 78, 'complete' => 1]);
        $this->assertStringContainsString('78', Notification::where('user_id', $student->user_id)->latest('id')->first()->message);
    }

    public function test_assignment_reupload_removes_old_file_and_is_late_after_due_at(): void
    {
        $guide = $this->faculty('g@test.com');
        $student = $this->student('s@test.com', 'R1');
        $assignment = Assignment::create(['faculty_id' => $guide->id, 'department_id' => $this->dept->id, 'title' => 'Hw', 'due_date' => now()->subDays(5)]);

        Storage::disk('local')->put('assignment_submissions/old.pdf', 'old');
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id, 'student_id' => $student->id,
            'file_path' => 'assignment_submissions/old.pdf', 'submitted_at' => now()->subDays(6), 'status' => 'submitted',
        ]);
        RevisionRequest::create([
            'assignment_submission_id' => $submission->id, 'stage' => RevisionRequest::STAGE_ASSIGNMENT,
            'faculty_id' => $guide->id, 'reason' => 'Wrong file', 'due_at' => now()->subHours(2),
        ]);

        $this->actingAs($student->user)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('new.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $fresh = $submission->fresh();
        Storage::disk('local')->assertMissing('assignment_submissions/old.pdf');
        Storage::disk('local')->assertExists($fresh->file_path);
        $this->assertTrue($fresh->is_late);
    }

    public function test_deleting_an_assignment_removes_submission_files(): void
    {
        $guide = $this->faculty('g@test.com');
        $student = $this->student('s@test.com', 'R1');
        $assignment = Assignment::create(['faculty_id' => $guide->id, 'department_id' => $this->dept->id, 'title' => 'Hw']);
        Storage::disk('local')->put('assignment_submissions/a.pdf', 'x');
        AssignmentSubmission::create(['assignment_id' => $assignment->id, 'student_id' => $student->id, 'file_path' => 'assignment_submissions/a.pdf', 'submitted_at' => now(), 'status' => 'submitted']);

        $this->actingAs($guide->user)->delete(route('faculty.assignments.destroy', $assignment));

        Storage::disk('local')->assertMissing('assignment_submissions/a.pdf');
    }

    public function test_workload_counts_each_unchecked_submission(): void
    {
        $guide = $this->faculty('g@test.com');
        $assignment = Assignment::create(['faculty_id' => $guide->id, 'department_id' => $this->dept->id, 'title' => 'Hw']);
        foreach (['R1', 'R2', 'R3'] as $roll) {
            AssignmentSubmission::create(['assignment_id' => $assignment->id, 'student_id' => $this->student($roll.'@test.com', $roll)->id, 'file_path' => 'x', 'submitted_at' => now(), 'status' => 'submitted']);
        }

        $count = Faculty::withCount(['givenSubmissions as n' => fn ($q) => $q->where('assignment_submissions.status', 'submitted')])->find($guide->id)->n;

        $this->assertSame(3, $count);
        $this->actingAs($this->user('root@test.com', 'super_admin'))->get(route('admin.workload.index'))->assertOk();
    }

    public function test_reviewed_project_with_open_request_shows_reupload_button(): void
    {
        $guide = $this->faculty('g@test.com');
        $student = $this->student('s@test.com', 'R1');
        $project = $this->project($student, $guide, Project::STATUS_FINAL_REVIEWED);
        RevisionRequest::create(['project_id' => $project->id, 'stage' => RevisionRequest::STAGE_FINAL, 'faculty_id' => $guide->id, 'reason' => 'Fix report']);

        $this->actingAs($student->user)->get(route('student.project.show'))
            ->assertOk()
            ->assertSee(route('student.submission.create'));
    }

    public function test_review_record_survives_on_guarded_faculty(): void
    {
        $guide = $this->faculty('g@test.com');
        $project = $this->project($this->student('s@test.com', 'R1'), null, Project::STATUS_SYNOPSIS_APPROVED);
        ProjectReview::create(['project_id' => $project->id, 'faculty_id' => $guide->id, 'stage' => 'synopsis', 'action' => 'approved']);

        $this->actingAs($this->user('root@test.com', 'super_admin'))->delete(route('admin.faculties.destroy', $guide));

        $this->assertDatabaseHas('project_reviews', ['faculty_id' => $guide->id]);
    }
}
