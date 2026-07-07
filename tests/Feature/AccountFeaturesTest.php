<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function makeStudent(string $email = 'stud@test.com'): Student
    {
        $dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
        $user = User::create(['name' => 'Stud', 'email' => $email, 'password' => 'password', 'role' => 'student']);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $dept->id,
            'roll_no' => 'R1', 'semester' => '4', 'phone' => '111',
        ]);
    }

    public function test_forgot_password_issues_token_and_resets(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'oldpass1', 'role' => 'faculty']);

        // Step 1: request a reset — should issue a token and forward to the reset form.
        $res = $this->post(route('password.email'), ['email' => $user->email]);
        $res->assertRedirect();
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        // Pull the one-time token out of the redirect target.
        parse_str((string) parse_url($res->headers->get('Location'), PHP_URL_QUERY), $q);
        $this->assertNotEmpty($q['token']);

        // Step 2: submit the new password with that token.
        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $q['token'],
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpass1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_forgot_password_rejects_unknown_email(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@test.com'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_reset_rejects_invalid_token(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'oldpass1', 'role' => 'faculty']);
        $this->post(route('password.email'), ['email' => $user->email]);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => 'a-wrong-token',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('oldpass1', $user->fresh()->password));
    }

    public function test_profile_update_changes_account_and_role_fields(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student->user)->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@test.com',
            'semester' => '6',
            'phone' => '999',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $student->user_id, 'name' => 'New Name', 'email' => 'new@test.com']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'semester' => '6', 'phone' => '999']);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $student = $this->makeStudent();

        // Wrong current password is rejected.
        $this->actingAs($student->user)->put(route('profile.password'), [
            'current_password' => 'wrong',
            'password' => 'brandnew1',
            'password_confirmation' => 'brandnew1',
        ])->assertSessionHasErrors('current_password');

        // Correct current password succeeds.
        $this->actingAs($student->user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'brandnew1',
            'password_confirmation' => 'brandnew1',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('brandnew1', $student->user->fresh()->password));
    }

    public function test_guest_cannot_open_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
