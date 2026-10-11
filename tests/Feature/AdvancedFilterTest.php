<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedFilterTest extends TestCase
{
    use RefreshDatabase;

    private Department $cse;

    private Department $bba;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();

        $this->cse = Department::create(['name' => 'CSE', 'code' => 'CSE']);
        $this->bba = Department::create(['name' => 'BBA', 'code' => 'BBA']);
        CollegeSetting::create(['name' => 'Test College']);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'root@test.com', 'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function makeProject(string $name, Department $dept, ?Faculty $guide = null): Project
    {
        static $n = 0;
        $n++;

        $user = User::create([
            'name' => "S{$n}", 'email' => "s{$n}@test.com", 'password' => 'password', 'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $user->id, 'department_id' => $dept->id, 'roll_no' => "R{$n}", 'semester' => '4',
        ]);

        $project = Project::create([
            'project_type' => 'single',
            'name' => $name,
            'leader_student_id' => $student->id,
            'department_id' => $dept->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'Abstract for '.$name,
            'status' => Project::STATUS_SYNOPSIS_REVIEW,
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader',
        ]);

        if ($guide) {
            FacultyAssignment::create([
                'project_id' => $project->id, 'faculty_id' => $guide->id, 'assigned_at' => now(),
            ]);
        }

        return $project;
    }

    private function makeFaculty(): Faculty
    {
        $user = User::create([
            'name' => 'Dr Guide', 'email' => 'guide@test.com', 'password' => 'password', 'role' => 'faculty',
        ]);

        return Faculty::create(['user_id' => $user->id, 'department_id' => $this->cse->id]);
    }

    public function test_department_filter_narrows_the_list(): void
    {
        $this->makeProject('CSE Portal', $this->cse);
        $this->makeProject('BBA Tracker', $this->bba);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['department_id' => $this->cse->id]))
            ->assertOk()
            ->assertSee('CSE Portal')
            ->assertDontSee('BBA Tracker');
    }

    public function test_unassigned_is_its_own_guide_filter(): void
    {
        $guide = $this->makeFaculty();
        $this->makeProject('Has Guide', $this->cse, $guide);
        $this->makeProject('No Guide', $this->cse);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['faculty_id' => 'unassigned']))
            ->assertOk()
            ->assertSee('No Guide')
            ->assertDontSee('Has Guide');
    }

    public function test_guide_filter_matches_that_guide_only(): void
    {
        $guide = $this->makeFaculty();
        $this->makeProject('Has Guide', $this->cse, $guide);
        $this->makeProject('No Guide', $this->cse);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['faculty_id' => $guide->id]))
            ->assertOk()
            ->assertSee('Has Guide')
            ->assertDontSee('No Guide');
    }

    public function test_search_matches_the_student_name_and_roll_number_too(): void
    {
        $project = $this->makeProject('Findable Portal', $this->cse);
        $roll = $project->leader->roll_no;

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['search' => $roll]))
            ->assertOk()
            ->assertSee('Findable Portal');
    }

    public function test_no_viva_filter_finds_projects_without_a_slot(): void
    {
        $this->makeProject('Needs Viva', $this->cse);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['flag' => 'no_viva']))
            ->assertOk()
            ->assertSee('Needs Viva');
    }

    public function test_the_export_honours_the_filters(): void
    {
        $this->makeProject('CSE Portal', $this->cse);
        $this->makeProject('BBA Tracker', $this->bba);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.export', ['department_id' => $this->cse->id]));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_filters_survive_pagination(): void
    {
        $this->makeProject('CSE Portal', $this->cse);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.projects.index', ['department_id' => $this->cse->id, 'status' => Project::STATUS_SYNOPSIS_REVIEW]))
            ->assertOk()
            ->assertSee('department_id='.$this->cse->id, false);
    }
}
