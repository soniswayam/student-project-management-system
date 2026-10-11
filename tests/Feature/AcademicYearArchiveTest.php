<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearArchiveTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        AcademicYear::query()->update(['is_current' => false]);
        AcademicYear::flushCurrent();

        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);
    }

    private function year(string $label): AcademicYear
    {
        return AcademicYear::create(['label' => $label]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'root@test.com',
            'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function makeStudent(string $email, string $roll): Student
    {
        $user = User::create([
            'name' => 'S'.$roll, 'email' => $email, 'password' => 'password', 'role' => 'student',
        ]);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $this->dept->id,
            'roll_no' => $roll, 'semester' => '4',
        ]);
    }

    private function makeProject(Student $student, ?AcademicYear $year): Project
    {
        return Project::create([
            'project_type' => 'single',
            'name' => 'Project '.$student->roll_no,
            'leader_student_id' => $student->id,
            'department_id' => $this->dept->id,
            'academic_year_id' => $year?->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'An abstract.',
            'status' => Project::STATUS_SYNOPSIS_REVIEW,
        ]);
    }

    public function test_a_new_synopsis_is_stamped_with_the_current_year(): void
    {
        $year = AcademicYear::create(['label' => '2030-31']);
        $year->markCurrent();
        AcademicYear::flushCurrent();

        $student = $this->makeStudent('s1@test.com', 'R1');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Library System',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A system for the library that tracks books and members.',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Library System',
            'academic_year_id' => $year->id,
        ]);
    }

    public function test_only_one_year_can_be_current_at_a_time(): void
    {
        $a = AcademicYear::create(['label' => '2025-26']);
        $b = AcademicYear::create(['label' => '2030-31']);

        $a->markCurrent();
        $b->markCurrent();

        $this->assertFalse($a->fresh()->is_current);
        $this->assertTrue($b->fresh()->is_current);
        $this->assertSame(1, AcademicYear::where('is_current', true)->count());
    }

    public function test_archived_year_projects_leave_the_active_list_but_are_not_deleted(): void
    {
        $old = AcademicYear::create(['label' => '2024-25']);
        $now = AcademicYear::create(['label' => '2030-31']);
        $now->markCurrent();

        $oldProject = $this->makeProject($this->makeStudent('a@test.com', 'R1'), $old);
        $newProject = $this->makeProject($this->makeStudent('b@test.com', 'R2'), $now);

        $old->archive();

        $active = Project::inActiveYear()->pluck('id');
        $this->assertFalse($active->contains($oldProject->id));
        $this->assertTrue($active->contains($newProject->id));

        $this->assertDatabaseHas('projects', ['id' => $oldProject->id]);

        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.projects.index'))
            ->assertOk()
            ->assertDontSee($oldProject->name)
            ->assertSee($newProject->name);

        $this->actingAs($admin)
            ->get(route('admin.archive.index'))
            ->assertOk()
            ->assertSee($oldProject->name);
    }

    public function test_projects_without_a_year_stay_visible(): void
    {
        $orphan = $this->makeProject($this->makeStudent('c@test.com', 'R3'), null);

        $this->assertTrue(Project::inActiveYear()->pluck('id')->contains($orphan->id));
    }

    public function test_the_current_year_cannot_be_archived(): void
    {
        $year = AcademicYear::create(['label' => '2030-31']);
        $year->markCurrent();

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.academic-years.archive', $year))
            ->assertRedirect();

        $this->assertNull($year->fresh()->archived_at);
    }

    public function test_a_year_with_projects_cannot_be_deleted(): void
    {
        $year = AcademicYear::create(['label' => '2024-25']);
        $this->makeProject($this->makeStudent('d@test.com', 'R4'), $year);

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.academic-years.destroy', $year))
            ->assertRedirect();

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }

    public function test_archiving_is_reversible(): void
    {
        $year = AcademicYear::create(['label' => '2024-25']);
        $project = $this->makeProject($this->makeStudent('e@test.com', 'R5'), $year);

        $year->archive();
        $this->assertFalse(Project::inActiveYear()->pluck('id')->contains($project->id));

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.academic-years.unarchive', $year))
            ->assertRedirect();

        $this->assertTrue(Project::inActiveYear()->pluck('id')->contains($project->id));
    }

    public function test_archive_show_refuses_a_project_from_an_open_year(): void
    {
        $year = AcademicYear::create(['label' => '2030-31']);
        $year->markCurrent();
        $project = $this->makeProject($this->makeStudent('f@test.com', 'R6'), $year);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.archive.show', $project))
            ->assertNotFound();
    }
}
