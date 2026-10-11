<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\DegreeLevelSetting;
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

class DegreeLevelTest extends TestCase
{
    use RefreshDatabase;

    private Department $ug;

    private Department $pg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();
        DegreeLevelSetting::flushCache();

        CollegeSetting::create(['name' => 'Test College', 'duplicate_block_percent' => 100]);

        $this->ug = Department::create([
            'name' => 'B.C.A', 'code' => 'BCA',
            'level' => Department::LEVEL_GRADUATION, 'duration_years' => 3,
        ]);

        $this->pg = Department::create([
            'name' => 'MSC(IT)', 'code' => 'MSCIT',
            'level' => Department::LEVEL_POST_GRADUATION, 'duration_years' => 2,
        ]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'root@test.com', 'password' => 'password', 'role' => 'super_admin',
        ]);
    }

    private function makeStudent(Department $dept, string $roll, string $semester): Student
    {
        $user = User::create([
            'name' => 'S'.$roll, 'email' => strtolower($roll).'@test.com',
            'password' => 'password', 'role' => 'student',
        ]);

        return Student::create([
            'user_id' => $user->id, 'department_id' => $dept->id,
            'roll_no' => $roll, 'semester' => $semester,
        ]);
    }

    public function test_total_semesters_is_derived_from_the_course_length(): void
    {
        $this->assertSame(6, $this->ug->fresh()->total_semesters);
        $this->assertSame(4, $this->pg->fresh()->total_semesters);
        $this->assertSame(6, $this->ug->finalSemester());
        $this->assertSame(4, $this->pg->finalSemester());
    }

    public function test_an_explicit_semester_count_is_not_overwritten(): void
    {
        $odd = Department::create([
            'name' => 'Integrated', 'code' => 'INT',
            'level' => Department::LEVEL_GRADUATION, 'duration_years' => 5, 'total_semesters' => 9,
        ]);

        $this->assertSame(9, $odd->fresh()->total_semesters);
        $this->assertSame(9, $odd->finalSemester());
    }

    public function test_promotion_stops_at_the_final_semester_of_each_course(): void
    {
        $ugMid = $this->makeStudent($this->ug, 'U1', '3');
        $ugFinal = $this->makeStudent($this->ug, 'U2', '6');
        $pgMid = $this->makeStudent($this->pg, 'P1', '3');
        $pgFinal = $this->makeStudent($this->pg, 'P2', '4');

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.students.promote-semester'), [
                'student_ids' => [$ugMid->id, $ugFinal->id, $pgMid->id, $pgFinal->id],
            ])->assertRedirect();

        $this->assertSame('4', $ugMid->fresh()->semester);
        $this->assertSame('4', $pgMid->fresh()->semester);

        $this->assertSame('6', $ugFinal->fresh()->semester);
        $this->assertSame('4', $pgFinal->fresh()->semester);
    }

    public function test_the_review_screen_lists_students_past_their_final_semester(): void
    {
        $stranded = $this->makeStudent($this->pg, 'P1', '5');
        $fine = $this->makeStudent($this->ug, 'U1', '5');

        $this->actingAs($this->superAdmin())
            ->get(route('admin.students.semester-review'))
            ->assertOk()
            ->assertSee('P1')
            ->assertDontSee('U1');

        $this->assertSame('5', $stranded->fresh()->semester);
        $this->assertSame('5', $fine->fresh()->semester);
    }

    public function test_fixing_moves_a_student_back_to_their_final_semester(): void
    {
        $stranded = $this->makeStudent($this->pg, 'P1', '5');

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.students.fix-semesters'), ['student_ids' => [$stranded->id]])
            ->assertRedirect();

        $this->assertSame('4', $stranded->fresh()->semester);
    }

    public function test_fixing_never_moves_a_student_forward(): void
    {
        $fine = $this->makeStudent($this->pg, 'P1', '2');

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.students.fix-semesters'), ['student_ids' => [$fine->id]])
            ->assertRedirect();

        $this->assertSame('2', $fine->fresh()->semester);
    }

    public function test_an_individual_only_level_refuses_a_group_project(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_POST_GRADUATION],
            ['max_group_size' => 1]
        );
        DegreeLevelSetting::flushCache();

        $leader = $this->makeStudent($this->pg, 'P1', '3');
        $this->makeStudent($this->pg, 'P2', '3');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Team Project',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A group attempt at something that should be individual work.',
            'partner_student_id' => Student::where('roll_no', 'P2')->first()->id,
        ])->assertSessionHasErrors('project_type');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_group_project_is_still_fine_where_the_level_allows_it(): void
    {
        $leader = $this->makeStudent($this->ug, 'U1', '3');
        $partner = $this->makeStudent($this->ug, 'U2', '3');

        $this->actingAs($leader->user)->post(route('student.synopsis.store'), [
            'project_type' => 'group',
            'name' => 'Team Project',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A group attempt at a perfectly reasonable undergraduate project.',
            'partner_student_id' => $partner->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', ['name' => 'Team Project']);
    }

    public function test_the_level_deadline_applies_only_when_the_department_has_none(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_POST_GRADUATION],
            ['submission_deadline' => now()->addDays(30)]
        );
        DegreeLevelSetting::flushCache();

        $project = $this->makeProject($this->pg, 'P1');

        $this->assertSame('level', $project->deadlineSource());

        $this->pg->update(['submission_deadline' => now()->addDays(10)]);
        $this->assertSame('department', $project->fresh()->deadlineSource());
    }

    public function test_a_level_with_no_marks_scheme_renders_no_marks_at_all(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_POST_GRADUATION],
            ['max_marks' => null]
        );
        DegreeLevelSetting::flushCache();

        $project = $this->makeProject($this->pg, 'P1');
        $project->update(['marks' => 88, 'status' => Project::STATUS_COMPLETED]);

        $this->assertNull($project->fresh()->marksLabel());
        $this->assertFalse($project->fresh()->isLevelMarked());
    }

    public function test_marks_read_against_their_own_level_maximum(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_POST_GRADUATION],
            ['max_marks' => 200]
        );
        DegreeLevelSetting::flushCache();

        $project = $this->makeProject($this->pg, 'P1');
        $project->update(['marks' => 180]);

        $this->assertSame('180 / 200', $project->fresh()->marksLabel());
    }

    public function test_a_blank_mark_still_renders_nothing_on_a_marked_level(): void
    {
        $project = $this->makeProject($this->ug, 'U1');

        $this->assertTrue($project->isLevelMarked());
        $this->assertNull($project->marksLabel());
    }

    public function test_marks_are_refused_above_the_level_maximum(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_GRADUATION],
            ['max_marks' => 100]
        );
        DegreeLevelSetting::flushCache();

        [$project, $guide] = $this->makeGradedProject();

        $this->actingAs($guide->user)
            ->post(route('faculty.projects.reviewFinal', $project), ['marks' => 150])
            ->assertSessionHasErrors('marks');

        $this->assertNull($project->fresh()->marks);
    }

    public function test_marks_are_refused_entirely_on_an_unmarked_level(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_GRADUATION],
            ['max_marks' => null]
        );
        DegreeLevelSetting::flushCache();

        [$project, $guide] = $this->makeGradedProject();

        $this->actingAs($guide->user)
            ->post(route('faculty.projects.reviewFinal', $project), ['marks' => 50])
            ->assertSessionHasErrors('marks');

        $this->assertNull($project->fresh()->marks);
    }

    public function test_a_project_is_flagged_when_its_level_expects_a_viva(): void
    {
        DegreeLevelSetting::updateOrCreate(
            ['level' => Department::LEVEL_POST_GRADUATION],
            ['viva_required' => true]
        );
        DegreeLevelSetting::flushCache();

        $pgProject = $this->makeProject($this->pg, 'P1');
        $ugProject = $this->makeProject($this->ug, 'U1');

        $this->assertTrue($pgProject->isVivaMissing());
        $this->assertFalse($ugProject->isVivaMissing());
    }

    private function makeProject(Department $dept, string $roll): Project
    {
        $student = $this->makeStudent($dept, $roll, '3');

        $project = Project::create([
            'project_type' => 'single',
            'name' => 'Project '.$roll,
            'leader_student_id' => $student->id,
            'department_id' => $dept->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'An abstract for '.$roll,
            'status' => Project::STATUS_FINAL_SUBMITTED,
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader',
        ]);

        return $project->fresh();
    }

    private function makeGradedProject(): array
    {
        $project = $this->makeProject($this->ug, 'U9');

        $guideUser = User::create([
            'name' => 'Guide', 'email' => 'guide@test.com', 'password' => 'password', 'role' => 'faculty',
        ]);
        $guide = Faculty::create(['user_id' => $guideUser->id, 'department_id' => $this->ug->id]);

        FacultyAssignment::create([
            'project_id' => $project->id, 'faculty_id' => $guide->id, 'assigned_at' => now(),
        ]);

        return [$project->fresh(), $guide];
    }
}
