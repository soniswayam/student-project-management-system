<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepartmentSemesterGroupingTest extends TestCase
{
    use RefreshDatabase;

    private Department $mscit;

    private Department $bca;

    private Faculty $faculty;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RoleSeeder::class);

        $this->mscit = Department::create(['name' => 'MSc IT', 'code' => 'MSCIT']);
        $this->bca = Department::create(['name' => 'BCA', 'code' => 'BCA']);

        $user = User::create(['name' => 'Guide', 'email' => 'guide@test.com', 'password' => 'password1', 'role' => 'faculty']);
        $this->faculty = Faculty::create(['user_id' => $user->id, 'department_id' => $this->mscit->id]);
        $this->faculty->departments()->attach([$this->mscit->id, $this->bca->id]);
    }

    private function makeStudent(Department $dept, string $roll, string $semester): Student
    {
        $user = User::create(['name' => "Student {$roll}", 'email' => "{$roll}@test.com", 'password' => 'password1', 'role' => 'student']);

        return Student::create(['user_id' => $user->id, 'department_id' => $dept->id, 'roll_no' => $roll, 'semester' => $semester]);
    }

    private function makeProject(Student $student, string $name): Project
    {
        $project = Project::create([
            'project_type' => 'single', 'name' => $name, 'leader_student_id' => $student->id,
            'department_id' => $student->department_id, 'frontend_tech' => 'Blade', 'backend_tech' => 'Laravel',
            'abstract' => 'Abstract for '.$name, 'status' => Project::STATUS_SYNOPSIS_REVIEW,
        ]);
        ProjectMember::create(['project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader']);
        FacultyAssignment::create(['project_id' => $project->id, 'faculty_id' => $this->faculty->id, 'assigned_at' => now()]);

        return $project;
    }

    private function superAdmin(): User
    {
        return User::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'password1', 'role' => 'super_admin']);
    }

    public function test_faculty_project_list_is_grouped_by_department_and_semester(): void
    {
        $this->makeProject($this->makeStudent($this->mscit, 'M3', '3'), 'Library System');
        $this->makeProject($this->makeStudent($this->mscit, 'M1', '1'), 'Quiz App');
        $this->makeProject($this->makeStudent($this->bca, 'B5', '5'), 'Hostel Portal');

        $this->actingAs($this->faculty->user)->get(route('faculty.projects.index', ['all' => 1]))
            ->assertOk()
            ->assertSeeInOrder(['BCA · Sem 5', 'Hostel Portal', 'MSc IT · Sem 1', 'Quiz App', 'MSc IT · Sem 3', 'Library System']);
    }

    public function test_faculty_projects_page_first_shows_the_select_a_class_dropdowns(): void
    {
        $this->makeProject($this->makeStudent($this->mscit, 'M3', '3'), 'Library System');
        $this->makeProject($this->makeStudent($this->bca, 'B5', '5'), 'Hostel Portal');

        $this->actingAs($this->faculty->user)->get(route('faculty.projects.index'))
            ->assertOk()
            ->assertSee('Select a class')
            ->assertSee('id="pickDept"', false)
            ->assertSee('id="pickSem"', false)
            ->assertSee('MSc IT — 1 project')
            ->assertSee('BCA — 1 project')
            ->assertSee('Semester 5 — 1 project')
            ->assertDontSee('Library System');
    }

    public function test_choosing_a_class_in_the_picker_opens_that_class(): void
    {
        $this->makeProject($this->makeStudent($this->mscit, 'M3', '3'), 'Library System');
        $this->makeProject($this->makeStudent($this->mscit, 'M4', '4'), 'Quiz App');

        $this->actingAs($this->faculty->user)
            ->get(route('faculty.projects.index', ['department_id' => $this->mscit->id, 'semester' => '3']))
            ->assertOk()
            ->assertDontSee('Select a class')
            ->assertSee('Library System')
            ->assertDontSee('Quiz App')
            ->assertSee('Change class');
    }

    public function test_faculty_with_no_projects_sees_the_empty_list_not_the_picker(): void
    {
        $this->actingAs($this->faculty->user)->get(route('faculty.projects.index'))
            ->assertOk()
            ->assertDontSee('Select a class')
            ->assertSee('No projects assigned to you yet.');
    }

    public function test_faculty_can_filter_projects_by_department_and_semester(): void
    {
        $this->makeProject($this->makeStudent($this->mscit, 'M3', '3'), 'Library System');
        $this->makeProject($this->makeStudent($this->mscit, 'M1', '1'), 'Quiz App');
        $this->makeProject($this->makeStudent($this->bca, 'B3', '3'), 'Hostel Portal');

        $this->actingAs($this->faculty->user)
            ->get(route('faculty.projects.index', ['department_id' => $this->mscit->id, 'semester' => 3]))
            ->assertOk()
            ->assertSee('Library System')
            ->assertDontSee('Quiz App')
            ->assertDontSee('Hostel Portal');
    }

    public function test_admin_and_super_admin_see_grouped_projects_with_a_semester_filter(): void
    {
        $this->makeProject($this->makeStudent($this->mscit, 'M3', '3'), 'Library System');
        $this->makeProject($this->makeStudent($this->mscit, 'M1', '1'), 'Quiz App');

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password1', 'role' => 'admin']);

        foreach ([$admin, $this->superAdmin()] as $user) {
            $this->actingAs($user)->get(route('admin.projects.index'))
                ->assertOk()
                ->assertSeeInOrder(['MSc IT · Sem 1', 'Quiz App', 'MSc IT · Sem 3', 'Library System']);

            $this->actingAs($user)->get(route('admin.projects.index', ['semester' => 1]))
                ->assertOk()
                ->assertSee('Quiz App')
                ->assertDontSee('Library System');
        }
    }

    public function test_faculty_creates_an_assignment_for_one_semester_and_only_that_semester_is_notified(): void
    {
        $sem3 = $this->makeStudent($this->mscit, 'M3', '3');
        $sem1 = $this->makeStudent($this->mscit, 'M1', '1');

        $this->actingAs($this->faculty->user)->post(route('faculty.assignments.store'), [
            'title' => 'DBMS Practical',
            'department_id' => $this->mscit->id,
            'semester' => 3,
            'subject' => 'DBMS',
            'type' => 'Lab',
        ])->assertRedirect(route('faculty.assignments.index'));

        $this->assertDatabaseHas('assignments', ['title' => 'DBMS Practical', 'semester' => '3']);
        $this->assertSame(1, Notification::where('user_id', $sem3->user_id)->count());
        $this->assertSame(0, Notification::where('user_id', $sem1->user_id)->count());
    }

    public function test_assignment_without_semester_still_goes_to_the_whole_department(): void
    {
        $sem3 = $this->makeStudent($this->mscit, 'M3', '3');
        $sem1 = $this->makeStudent($this->mscit, 'M1', '1');

        $this->actingAs($this->faculty->user)->post(route('faculty.assignments.store'), [
            'title' => 'General Notice Task', 'department_id' => $this->mscit->id,
            'subject' => 'General', 'type' => 'Theory',
        ]);

        $this->assertDatabaseHas('assignments', ['title' => 'General Notice Task', 'semester' => null]);
        $this->assertSame(1, Notification::where('user_id', $sem3->user_id)->count());
        $this->assertSame(1, Notification::where('user_id', $sem1->user_id)->count());
    }

    public function test_faculty_assignment_list_is_grouped_and_filterable(): void
    {
        $base = ['faculty_id' => $this->faculty->id, 'subject' => 'X', 'type' => 'Theory'];
        Assignment::create($base + ['department_id' => $this->mscit->id, 'semester' => '3', 'title' => 'Sem Three Work']);
        Assignment::create($base + ['department_id' => $this->mscit->id, 'semester' => '1', 'title' => 'Sem One Work']);
        Assignment::create($base + ['department_id' => $this->bca->id, 'semester' => null, 'title' => 'BCA Everyone Work']);

        $this->actingAs($this->faculty->user)->get(route('faculty.assignments.index', ['all' => 1]))
            ->assertOk()
            ->assertSeeInOrder(['BCA · All semesters', 'BCA Everyone Work', 'MSc IT · Sem 1', 'Sem One Work', 'MSc IT · Sem 3', 'Sem Three Work']);

        $this->actingAs($this->faculty->user)->get(route('faculty.assignments.index', ['semester' => 3]))
            ->assertSee('Sem Three Work')
            ->assertDontSee('Sem One Work');

        $this->actingAs($this->faculty->user)->get(route('faculty.assignments.index', ['department_id' => $this->bca->id, 'semester' => 'common']))
            ->assertSee('BCA Everyone Work')
            ->assertDontSee('Sem Three Work');
    }

    public function test_faculty_assignments_page_first_shows_the_select_a_class_dropdowns(): void
    {
        $base = ['faculty_id' => $this->faculty->id, 'subject' => 'X', 'type' => 'Theory'];
        Assignment::create($base + ['department_id' => $this->mscit->id, 'semester' => '3', 'title' => 'Sem Three Work']);
        Assignment::create($base + ['department_id' => $this->bca->id, 'semester' => null, 'title' => 'BCA Everyone Work']);

        $this->actingAs($this->faculty->user)->get(route('faculty.assignments.index'))
            ->assertOk()
            ->assertSee('Select a class')
            ->assertSee('MSc IT — 1 assignment')
            ->assertSee('Common to all semesters — 1 assignment')
            ->assertDontSee('Sem Three Work');
    }

    public function test_admin_assignment_list_is_grouped_and_filterable(): void
    {
        $base = ['faculty_id' => $this->faculty->id, 'subject' => 'X', 'type' => 'Theory'];
        Assignment::create($base + ['department_id' => $this->mscit->id, 'semester' => '3', 'title' => 'Sem Three Work']);
        Assignment::create($base + ['department_id' => $this->mscit->id, 'semester' => '1', 'title' => 'Sem One Work']);

        $root = $this->superAdmin();

        $this->actingAs($root)->get(route('admin.assignments.index'))
            ->assertOk()
            ->assertSeeInOrder(['MSc IT · Sem 1', 'Sem One Work', 'MSc IT · Sem 3', 'Sem Three Work']);

        $this->actingAs($root)->get(route('admin.assignments.index', ['semester' => 1]))
            ->assertSee('Sem One Work')
            ->assertDontSee('Sem Three Work');
    }

    public function test_admin_can_set_the_semester_when_creating_an_assignment(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.assignments.store'), [
            'faculty_id' => $this->faculty->id, 'department_id' => $this->bca->id, 'semester' => 5,
            'subject' => 'Networks', 'type' => 'Theory', 'title' => 'Subnetting Sheet',
        ])->assertRedirect(route('admin.assignments.index'));

        $this->assertDatabaseHas('assignments', ['title' => 'Subnetting Sheet', 'semester' => '5']);
    }

    public function test_student_sees_and_submits_only_their_semesters_assignments(): void
    {
        $student = $this->makeStudent($this->mscit, 'M3', '3');
        $base = ['faculty_id' => $this->faculty->id, 'department_id' => $this->mscit->id, 'subject' => 'X', 'type' => 'Theory', 'due_date' => now()->addDay()];
        Assignment::create($base + ['semester' => '3', 'title' => 'Mine Sem Three']);
        Assignment::create($base + ['semester' => null, 'title' => 'For Everyone']);
        $other = Assignment::create($base + ['semester' => '1', 'title' => 'Other Sem One']);

        $this->actingAs($student->user)->get(route('student.assignments.index'))
            ->assertOk()
            ->assertSee('Mine Sem Three')
            ->assertSee('For Everyone')
            ->assertDontSee('Other Sem One');

        $this->actingAs($student->user)->post(route('student.assignments.submit', $other), [
            'file' => UploadedFile::fake()->create('w.pdf', 10, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_invalid_semester_value_is_rejected(): void
    {
        $this->actingAs($this->faculty->user)->post(route('faculty.assignments.store'), [
            'title' => 'Bad', 'department_id' => $this->mscit->id, 'semester' => 99,
            'subject' => 'X', 'type' => 'Theory',
        ])->assertSessionHasErrors('semester');

        $this->assertDatabaseCount('assignments', 0);
    }
}
