<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSelectionTest extends TestCase
{
    use RefreshDatabase;

    private Department $cse;

    private Department $bba;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->cse = Department::create(['name' => 'CSE', 'code' => 'CSE']);
        $this->bba = Department::create(['name' => 'BBA', 'code' => 'BBA']);
    }

    private function makeStudent(Department $dept, string $email, string $roll): Student
    {
        $user = User::create(['name' => $email, 'email' => $email, 'password' => 'password1', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $dept->id, 'roll_no' => $roll]);
    }

    private function payload(int $partnerId): array
    {
        return [
            'project_type' => 'group',
            'name' => 'Group Project',
            'partner_student_id' => $partnerId,
            'frontend_tech' => 'React',
            'backend_tech' => 'Laravel',
            'abstract' => str_repeat('a group project idea ', 3),
        ];
    }

    public function test_available_partners_are_limited_to_the_same_department(): void
    {
        $leader = $this->makeStudent($this->cse, 'leader@test.com', 'R1');
        $sameDept = $this->makeStudent($this->cse, 'same@test.com', 'R2');
        $otherDept = $this->makeStudent($this->bba, 'other@test.com', 'R3');

        $partners = Student::availablePartnersFor($leader);

        $this->assertTrue($partners->contains('id', $sameDept->id));
        $this->assertFalse($partners->contains('id', $otherDept->id));
        $this->assertFalse($partners->contains('id', $leader->id));
    }

    public function test_partner_from_another_department_is_rejected(): void
    {
        $leader = $this->makeStudent($this->cse, 'leader@test.com', 'R1');
        $outsider = $this->makeStudent($this->bba, 'outsider@test.com', 'R2');

        $this->actingAs($leader->user)
            ->post(route('student.synopsis.store'), $this->payload($outsider->id))
            ->assertSessionHasErrors('partner_student_id');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_partner_from_the_same_department_is_accepted(): void
    {
        $leader = $this->makeStudent($this->cse, 'leader@test.com', 'R1');
        $partner = $this->makeStudent($this->cse, 'partner@test.com', 'R2');

        $this->actingAs($leader->user)
            ->post(route('student.synopsis.store'), $this->payload($partner->id))
            ->assertRedirect(route('student.project.show'));

        $this->assertDatabaseHas('projects', ['name' => 'Group Project']);
        $this->assertDatabaseCount('project_members', 2);
    }
}
