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

    public function test_profile_update_changes_account_and_role_fields(): void
    {
        $student = $this->makeStudent();
        $originalSemester = $student->semester;

        $this->actingAs($student->user)->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@test.com',
            'semester' => '6',
            'phone' => '999',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $student->user_id, 'name' => 'New Name', 'email' => 'new@test.com']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'phone' => '999']);

        $this->assertDatabaseHas('students', ['id' => $student->id, 'semester' => $originalSemester]);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student->user)->put(route('profile.password'), [
            'current_password' => 'wrong',
            'password' => 'brandnew1',
            'password_confirmation' => 'brandnew1',
        ])->assertSessionHasErrors('current_password');

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
