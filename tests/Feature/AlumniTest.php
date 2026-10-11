<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumniTest extends TestCase
{
    use RefreshDatabase;

    private Department $ug;

    private Department $pg;

    private AcademicYear $batch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();

        CollegeSetting::create(['name' => 'Test College', 'duplicate_block_percent' => 100]);

        $this->ug = Department::create([
            'name' => 'B.C.A', 'code' => 'BCA',
            'level' => Department::LEVEL_GRADUATION, 'duration_years' => 3,
        ]);
        $this->pg = Department::create([
            'name' => 'MSC(IT)', 'code' => 'MSCIT',
            'level' => Department::LEVEL_POST_GRADUATION, 'duration_years' => 2,
        ]);

        AcademicYear::query()->update(['is_current' => false]);
        $this->batch = AcademicYear::create(['label' => '2030-31']);
        $this->batch->markCurrent();
        AcademicYear::flushCurrent();
    }

    private ?User $admin = null;

    private function superAdmin(): User
    {
        return $this->admin ??= User::create([
            'name' => 'Root', 'email' => 'root@test.com', 'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function makeStudent(Department $dept, string $roll, string $semester): Student
    {
        $user = User::create([
            'name' => 'S'.$roll, 'email' => strtolower($roll).'@test.com',
            'password' => 'password', 'role' => 'student',
        ]);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $dept->id,
            'roll_no' => $roll, 'semester' => $semester,
        ]);
    }

    private function passOut(Student $student): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.alumni.store'), [
            'student_ids' => [$student->id],
            'passed_out_year_id' => $this->batch->id,
        ]);
    }

    public function test_moving_a_student_to_alumni_stamps_the_batch_and_notifies_them(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');

        $this->actingAs($this->superAdmin())->post(route('admin.alumni.store'), [
            'student_ids' => [$student->id],
            'passed_out_year_id' => $this->batch->id,
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame(User::STATUS_GRADUATED, $student->user->status);
        $this->assertSame($this->batch->id, $student->passed_out_year_id);
        $this->assertNotNull($student->passed_out_at);
        $this->assertSame('2030-31', $student->batchLabel());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->user_id,
            'title' => 'You have passed out',
        ]);
    }

    public function test_the_batch_defaults_to_the_current_academic_year(): void
    {
        $student = $this->makeStudent($this->pg, 'PGSTUD01', '4');

        $this->actingAs($this->superAdmin())
            ->post(route('admin.alumni.store'), ['student_ids' => [$student->id]])
            ->assertRedirect();

        $this->assertSame($this->batch->id, $student->fresh()->passed_out_year_id);
    }

    public function test_both_ug_and_pg_students_can_pass_out(): void
    {
        $ug = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $pg = $this->makeStudent($this->pg, 'PGSTUD01', '4');

        $this->actingAs($this->superAdmin())->post(route('admin.alumni.store'), [
            'student_ids' => [$ug->id, $pg->id],
        ])->assertRedirect();

        $this->assertTrue($ug->fresh()->isAlumni());
        $this->assertTrue($pg->fresh()->isAlumni());
    }

    public function test_an_existing_alumnus_is_skipped_rather_than_restamped(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $this->passOut($student);

        $firstStamp = $student->fresh()->passed_out_at;

        $other = AcademicYear::create(['label' => '2031-32']);
        $this->actingAs($this->superAdmin())->post(route('admin.alumni.store'), [
            'student_ids' => [$student->id],
            'passed_out_year_id' => $other->id,
        ])->assertRedirect();

        $this->assertSame($this->batch->id, $student->fresh()->passed_out_year_id);
        $this->assertEquals($firstStamp, $student->fresh()->passed_out_at);
    }

    public function test_a_student_with_no_completed_project_can_still_pass_out(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');

        $this->passOut($student);

        $this->assertTrue($student->fresh()->isAlumni());
        $this->assertFalse($student->fresh()->hasCompletedProject());
    }

    public function test_alumni_leave_the_students_list_and_appear_in_the_register(): void
    {
        $studying = $this->makeStudent($this->ug, 'STUDYING01', '4');
        $leaving = $this->makeStudent($this->ug, 'LEAVING02', '6');

        $this->passOut($leaving);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee($studying->roll_no)
            ->assertDontSee($leaving->roll_no);

        $this->actingAs($admin)->get(route('admin.alumni.index'))
            ->assertOk()
            ->assertSee($leaving->roll_no)
            ->assertDontSee($studying->roll_no);
    }

    public function test_alumni_can_still_be_found_on_the_students_list_by_filtering(): void
    {
        $leaving = $this->makeStudent($this->ug, 'LEAVING02', '6');
        $this->passOut($leaving);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.students.index', ['status' => User::STATUS_GRADUATED]))
            ->assertOk()
            ->assertSee($leaving->roll_no);
    }

    public function test_the_batch_dropdown_filters_the_register(): void
    {
        $a = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $b = $this->makeStudent($this->ug, 'LEAVING02', '6');

        $this->passOut($a);

        $older = AcademicYear::create(['label' => '2029-30']);
        $this->actingAs($this->superAdmin())->post(route('admin.alumni.store'), [
            'student_ids' => [$b->id],
            'passed_out_year_id' => $older->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.alumni.index', ['batch' => $older->id]))
            ->assertOk()
            ->assertSee($b->roll_no)
            ->assertDontSee($a->roll_no);
    }

    public function test_restoring_puts_a_student_back_to_studying(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $this->passOut($student);

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.alumni.restore', $student))
            ->assertRedirect();

        $student->refresh();
        $this->assertFalse($student->isAlumni());
        $this->assertSame(User::STATUS_ACTIVE, $student->user->status);
        $this->assertNull($student->passed_out_year_id);
    }

    public function test_an_alumnus_can_still_sign_in(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $this->passOut($student);

        $this->post(route('login'), ['email' => 'u1@test.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_an_alumnus_can_still_read_their_project(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $this->makeProjectFor($student);
        $this->passOut($student);

        $this->actingAs($student->user->fresh())
            ->get(route('student.project.show'))
            ->assertOk()
            ->assertSee('You have passed out');
    }

    public function test_an_alumnus_cannot_submit_anything_new(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '6');
        $this->makeProjectFor($student);
        $this->passOut($student);

        $user = $student->user->fresh();

        $this->actingAs($user)->get(route('student.synopsis.create'))
            ->assertRedirect(route('student.project.show'));

        $this->actingAs($user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'A Second Project',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'Trying to start new work after having already passed out.',
        ])->assertRedirect(route('student.project.show'));

        $this->assertDatabaseMissing('projects', ['name' => 'A Second Project']);

        $this->actingAs($user)->get(route('student.submission.create'))
            ->assertRedirect(route('student.project.show'));
    }

    public function test_a_studying_student_is_unaffected_by_the_alumni_guard(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '4');

        $this->actingAs($student->user)
            ->get(route('student.synopsis.create'))
            ->assertOk();
    }

    public function test_a_student_cannot_reach_the_alumni_register(): void
    {
        $student = $this->makeStudent($this->ug, 'STUDYING01', '4');

        $this->actingAs($student->user)
            ->get(route('admin.alumni.index'))
            ->assertRedirect(route('dashboard'));
    }

    private function makeProjectFor(Student $student): Project
    {
        $project = Project::create([
            'project_type' => 'single',
            'name' => 'Project '.$student->roll_no,
            'leader_student_id' => $student->id,
            'department_id' => $student->department_id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'An abstract for '.$student->roll_no,
            'status' => Project::STATUS_COMPLETED,
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader',
        ]);

        return $project;
    }
}
