<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role.'@t.com',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    public function test_admin_can_access_permitted_areas(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.students.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.faculties.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_admin_is_blocked_from_actions_it_lacks_permission_for(): void
    {
        $admin = $this->user('admin');
        $dashboard = route('dashboard');

        // Admin lacks: admins.manage, roles.manage, settings.manage, faculty.manage.
        $this->actingAs($admin)->get(route('admin.admins.index'))->assertRedirect($dashboard);
        $this->actingAs($admin)->get(route('admin.roles.index'))->assertRedirect($dashboard);
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertRedirect($dashboard);
        $this->actingAs($admin)->get(route('admin.faculties.create'))->assertRedirect($dashboard);

        // Admin lacks departments.manage.
        $this->actingAs($admin)->post(route('admin.departments.store'), ['name' => 'X', 'code' => 'X'])
            ->assertRedirect($dashboard);
        $this->assertDatabaseMissing('departments', ['code' => 'X']);
    }

    public function test_admin_cannot_delete_a_student(): void
    {
        $admin = $this->user('admin');
        $dept = Department::create(['name' => 'BCA', 'code' => 'BCA']);
        $stuUser = User::create(['name' => 'S', 'email' => 's@t.com', 'password' => 'password', 'role' => 'student']);
        $student = Student::create(['user_id' => $stuUser->id, 'department_id' => $dept->id, 'roll_no' => 'R1']);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }

    public function test_super_admin_can_manage_everything(): void
    {
        $super = $this->user('super_admin');

        $this->actingAs($super)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($super)->get(route('admin.settings.edit'))->assertOk();
        $this->actingAs($super)->get(route('admin.admins.index'))->assertOk();

        $this->actingAs($super)->post(route('admin.departments.store'), ['name' => 'MSC', 'code' => 'MSC']);
        $this->assertDatabaseHas('departments', ['code' => 'MSC']);
    }

    public function test_access_control_page_combines_staff_and_roles(): void
    {
        $super = $this->user('super_admin');

        $res = $this->actingAs($super)->get(route('admin.access.index'));
        $res->assertOk();
        $res->assertSee('Staff & Roles');
        $res->assertSee('Staff');
        $res->assertSee('Roles');

        // A plain admin holds neither admins.manage nor roles.manage → forbidden.
        $this->actingAs($this->user('admin'))->get(route('admin.access.index'))->assertForbidden();
    }

    public function test_custom_role_sees_only_its_permitted_pages(): void
    {
        // A "Coordinator" role that can only view students.
        Role::create([
            'name' => 'coordinator',
            'label' => 'Coordinator',
            'permissions' => ['students.view'],
            'is_staff' => true,
            'is_system' => false,
        ]);
        $coordinator = $this->user('coordinator');

        // Allowed.
        $this->actingAs($coordinator)->get(route('admin.students.index'))->assertOk();

        // Not allowed — no projects.view, no students.create.
        $this->actingAs($coordinator)->get(route('admin.projects.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($coordinator)->get(route('admin.students.create'))->assertRedirect(route('dashboard'));
    }

    public function test_non_staff_role_cannot_reach_admin_area(): void
    {
        $student = $this->user('student');

        $this->actingAs($student)->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
    }
}
