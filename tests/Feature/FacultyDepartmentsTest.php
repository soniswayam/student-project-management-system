<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyDepartmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_assign_a_faculty_to_many_departments(): void
    {
        $super = User::create(['name' => 'Root', 'email' => 'root@t.com', 'password' => 'password', 'role' => 'super_admin']);
        $bca = Department::create(['name' => 'BCA', 'code' => 'BCA']);
        $mscit = Department::create(['name' => 'MSc IT', 'code' => 'MSCIT']);
        $mscca = Department::create(['name' => 'MSc CA', 'code' => 'MSCCA']);

        $this->actingAs($super)->post(route('admin.faculties.store'), [
            'name' => 'Prof. Rao',
            'email' => 'rao@t.com',
            'department_ids' => [$bca->id, $mscit->id, $mscca->id],
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.faculties.index'));

        $faculty = Faculty::first();
        $this->assertEqualsCanonicalizing(
            [$bca->id, $mscit->id, $mscca->id],
            $faculty->departments()->pluck('departments.id')->all()
        );
        // First selected department becomes the primary/home department.
        $this->assertSame($bca->id, $faculty->department_id);
    }

    public function test_faculty_editing_syncs_departments(): void
    {
        $super = User::create(['name' => 'Root', 'email' => 'root@t.com', 'password' => 'password', 'role' => 'super_admin']);
        $bca = Department::create(['name' => 'BCA', 'code' => 'BCA']);
        $mscit = Department::create(['name' => 'MSc IT', 'code' => 'MSCIT']);

        $facUser = User::create(['name' => 'P', 'email' => 'p@t.com', 'password' => 'password', 'role' => 'faculty']);
        $faculty = Faculty::create(['user_id' => $facUser->id, 'department_id' => $bca->id]);
        $faculty->departments()->sync([$bca->id]);

        // Re-assign to only MSc IT.
        $this->actingAs($super)->put(route('admin.faculties.update', $faculty), [
            'name' => 'P',
            'email' => 'p@t.com',
            'department_ids' => [$mscit->id],
        ])->assertRedirect(route('admin.faculties.index'));

        $this->assertEqualsCanonicalizing([$mscit->id], $faculty->fresh()->departments()->pluck('departments.id')->all());
        $this->assertSame($mscit->id, $faculty->fresh()->department_id);
    }

    public function test_faculty_can_only_post_assignments_to_own_departments(): void
    {
        $bca = Department::create(['name' => 'BCA', 'code' => 'BCA']);
        $mscit = Department::create(['name' => 'MSc IT', 'code' => 'MSCIT']);

        $facUser = User::create(['name' => 'P', 'email' => 'p@t.com', 'password' => 'password', 'role' => 'faculty']);
        $faculty = Faculty::create(['user_id' => $facUser->id, 'department_id' => $bca->id]);
        $faculty->departments()->sync([$bca->id]); // teaches BCA only

        // Posting to a department they do NOT teach is rejected.
        $this->actingAs($facUser)->post(route('faculty.assignments.store'), [
            'title' => 'A1', 'department_id' => $mscit->id, 'subject' => 'Java', 'type' => 'Theory',
        ])->assertSessionHasErrors('department_id');
        $this->assertDatabaseCount('assignments', 0);

        // Posting to their own department succeeds.
        $this->actingAs($facUser)->post(route('faculty.assignments.store'), [
            'title' => 'A1', 'department_id' => $bca->id, 'subject' => 'Java', 'type' => 'Theory',
        ])->assertRedirect(route('faculty.assignments.index'));
        $this->assertDatabaseHas('assignments', ['title' => 'A1', 'department_id' => $bca->id]);
    }
}
