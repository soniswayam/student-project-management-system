<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
        // Super admin bypasses every permission check.
        $this->admin = User::create([
            'name' => 'Root', 'email' => 'root@test.com',
            'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Student',
            'email' => 'new@test.com',
            'roll_no' => 'R100',
            'department_id' => $this->dept->id,
            'semester' => '3',
            'phone' => '9999999999',
            'status' => 'active',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }

    private function makeStudent(string $email, string $roll, string $status = 'active'): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password', 'role' => 'student', 'status' => $status]);

        return Student::create(['user_id' => $user->id, 'department_id' => $this->dept->id, 'roll_no' => $roll, 'semester' => '1']);
    }

    public function test_admin_can_add_a_student_with_hashed_password_and_role(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload())
            ->assertRedirect(route('admin.students.index'));

        $user = User::where('email', 'new@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('student', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertDatabaseHas('students', ['user_id' => $user->id, 'roll_no' => 'R100', 'semester' => '3']);
    }

    public function test_duplicate_email_and_roll_are_blocked(): void
    {
        $this->makeStudent('taken@test.com', 'R1');

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload(['email' => 'taken@test.com']))
            ->assertSessionHasErrors('email');

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $this->validPayload(['roll_no' => 'R1']))
            ->assertSessionHasErrors('roll_no');
    }

    public function test_password_update_is_optional(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $original = $student->user->password;

        // Update without password -> keeps old hash.
        $this->actingAs($this->admin)->put(route('admin.students.update', $student), [
            'name' => 'Renamed', 'email' => 's@test.com', 'roll_no' => 'R1',
            'department_id' => $this->dept->id, 'semester' => '4', 'status' => 'active',
        ])->assertRedirect(route('admin.students.index'));

        $this->assertEquals($original, $student->user->fresh()->password);
        $this->assertEquals('Renamed', $student->user->fresh()->name);
        $this->assertEquals('4', $student->fresh()->semester);
    }

    public function test_admin_can_block_and_approve_students(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');

        $this->actingAs($this->admin)
            ->patch(route('admin.students.status', $student), ['status' => 'blocked']);
        $this->assertEquals('blocked', $student->user->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.students.status', $student), ['status' => 'active']);
        $this->assertEquals('active', $student->user->fresh()->status);
    }

    public function test_pending_and_blocked_students_cannot_log_in(): void
    {
        $this->makeStudent('pending@test.com', 'R1', 'pending');
        $this->makeStudent('blocked@test.com', 'R2', 'blocked');

        $this->post(route('login'), ['email' => 'pending@test.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'blocked@test.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_student_can_log_in(): void
    {
        $this->makeStudent('ok@test.com', 'R1', 'active');

        $this->post(route('login'), ['email' => 'ok@test.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_self_registration_creates_a_pending_student_and_does_not_log_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Self', 'email' => 'self@test.com', 'roll_no' => 'R9',
            'department_id' => $this->dept->id, 'semester' => '2',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'self@test.com', 'role' => 'student', 'status' => 'pending']);
    }

    public function test_student_linked_to_project_cannot_be_deleted(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $project = Project::create([
            'name' => 'P', 'department_id' => $this->dept->id, 'project_type' => 'single',
            'leader_student_id' => $student->id, 'status' => Project::STATUS_SYNOPSIS_REVIEW,
            'abstract' => 'x', 'frontend_tech' => 'R', 'backend_tech' => 'L',
        ]);
        ProjectMember::create(['project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader']);

        $this->actingAs($this->admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }

    public function test_unlinked_student_can_be_deleted(): void
    {
        $student = $this->makeStudent('s@test.com', 'R1');
        $userId = $student->user_id;

        $this->actingAs($this->admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_bulk_import_creates_valid_rows_and_reports_failures(): void
    {
        // Row 2 valid, row 3 invalid (bad department), row 4 duplicate email of row 2.
        $csv = implode("\n", [
            'name,email,roll_no,department,semester,phone,password',
            'Alice,alice@test.com,IMP1,CSE,3,900,secret123',
            'Bob,bob@test.com,IMP2,NOPE,3,900,secret123',
            'Alice2,alice@test.com,IMP3,CSE,3,900,secret123',
        ]);
        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.students.import'), ['file' => $file])
            ->assertRedirect(route('admin.students.import.form'))
            ->assertSessionHas('success');

        // Only Alice imported.
        $this->assertDatabaseHas('users', ['email' => 'alice@test.com', 'role' => 'student', 'status' => 'active']);
        $this->assertDatabaseMissing('users', ['email' => 'bob@test.com']);
        $this->assertDatabaseCount('students', 1);

        $failures = session('import_failures');
        $this->assertCount(2, $failures);
    }
}
