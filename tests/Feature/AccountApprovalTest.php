<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountApprovalTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'root@test.com',
            'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function pendingStudent(string $email, string $roll): Student
    {
        $user = User::create([
            'name' => 'Pending '.$roll, 'email' => $email, 'password' => 'password',
            'role' => 'student', 'status' => User::STATUS_PENDING,
        ]);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $this->dept->id,
            'roll_no' => $roll, 'semester' => '1',
        ]);
    }

    public function test_registration_creates_a_pending_account_that_cannot_sign_in(): void
    {
        $this->post(route('register'), [
            'name' => 'New Student',
            'email' => 'new@test.com',
            'roll_no' => 'R99',
            'department_id' => $this->dept->id,
            'semester' => '1',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@test.com',
            'status' => User::STATUS_PENDING,
        ]);

        $this->post(route('login'), ['email' => 'new@test.com', 'password' => 'password1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_approving_activates_the_account_and_records_who_did_it(): void
    {
        $student = $this->pendingStudent('p1@test.com', 'R1');
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.approvals.approve', $student->user))
            ->assertRedirect();

        $user = $student->user->fresh();
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->approved_at);
        $this->assertSame($admin->id, $user->approved_by);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Account approved',
        ]);

        $this->post(route('login'), ['email' => 'p1@test.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_bulk_approve_activates_every_selected_account(): void
    {
        $a = $this->pendingStudent('p1@test.com', 'R1');
        $b = $this->pendingStudent('p2@test.com', 'R2');

        $this->actingAs($this->superAdmin())
            ->post(route('admin.approvals.bulk'), [
                'user_ids' => [$a->user_id, $b->user_id],
            ])->assertRedirect();

        $this->assertSame(User::STATUS_ACTIVE, $a->user->fresh()->status);
        $this->assertSame(User::STATUS_ACTIVE, $b->user->fresh()->status);
    }

    public function test_rejecting_blocks_the_account_and_keeps_the_reason(): void
    {
        $student = $this->pendingStudent('p1@test.com', 'R1');

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.approvals.reject', $student->user), [
                'reason' => 'Roll number does not match our records',
            ])->assertRedirect();

        $user = $student->user->fresh();
        $this->assertSame(User::STATUS_BLOCKED, $user->status);
        $this->assertSame('Roll number does not match our records', $user->status_reason);

        $this->assertDatabaseHas('students', ['roll_no' => 'R1']);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $student = $this->pendingStudent('p1@test.com', 'R1');

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.approvals.reject', $student->user), [])
            ->assertSessionHasErrors('reason');

        $this->assertSame(User::STATUS_PENDING, $student->user->fresh()->status);
    }

    public function test_an_already_decided_account_is_not_approved_twice(): void
    {
        $student = $this->pendingStudent('p1@test.com', 'R1');
        $student->user->update(['status' => User::STATUS_BLOCKED]);

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.approvals.approve', $student->user))
            ->assertRedirect();

        $this->assertSame(User::STATUS_BLOCKED, $student->user->fresh()->status);
    }

    public function test_a_student_cannot_reach_the_approvals_queue(): void
    {
        $student = $this->pendingStudent('p1@test.com', 'R1');
        $student->user->update(['status' => User::STATUS_ACTIVE]);

        $this->actingAs($student->user)
            ->get(route('admin.approvals.index'))
            ->assertRedirect(route('dashboard'));
    }
}
