<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\FacultyAssignmentHistory;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectSimilarity;
use App\Models\ProjectSubmission;
use App\Models\RevisionRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Viva;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewScreensSmokeTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    private User $admin;

    private Faculty $guide;

    private Student $student;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();

        CollegeSetting::create(['name' => 'Test College']);
        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);

        $this->admin = User::create([
            'name' => 'Root', 'email' => 'root@test.com', 'password' => 'password', 'role' => 'super_admin',
        ]);

        $guideUser = User::create([
            'name' => 'Dr Guide', 'email' => 'guide@test.com', 'password' => 'password', 'role' => 'faculty',
        ]);
        $this->guide = Faculty::create([
            'user_id' => $guideUser->id, 'department_id' => $this->dept->id,
            'designation' => 'Professor', 'max_projects' => 5,
        ]);

        $studentUser = User::create([
            'name' => 'Student One', 'email' => 's1@test.com', 'password' => 'password', 'role' => 'student',
        ]);
        $this->student = Student::create([
            'user_id' => $studentUser->id, 'department_id' => $this->dept->id,
            'roll_no' => 'R1', 'semester' => '4',
        ]);

        $this->project = Project::create([
            'project_type' => 'single',
            'name' => 'Hostel Portal',
            'leader_student_id' => $this->student->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'A portal for hostel administration and mess records.',
            'status' => Project::STATUS_FINAL_SUBMITTED,
        ]);

        ProjectMember::create([
            'project_id' => $this->project->id, 'student_id' => $this->student->id, 'role_in_project' => 'leader',
        ]);
        FacultyAssignment::create([
            'project_id' => $this->project->id, 'faculty_id' => $this->guide->id, 'assigned_at' => now(),
        ]);
        ProjectSubmission::create([
            'project_id' => $this->project->id,
            'report_path' => 'submissions/report.pdf',
            'source_zip_path' => 'submissions/src.zip',
            'submitted_at' => now(),
        ]);
    }

    private function withExtras(): void
    {
        $other = Project::create([
            'project_type' => 'single',
            'name' => 'Hostel Manager',
            'leader_student_id' => Student::create([
                'user_id' => User::create([
                    'name' => 'S2', 'email' => 's2@test.com', 'password' => 'password', 'role' => 'student',
                ])->id,
                'department_id' => $this->dept->id, 'roll_no' => 'R2', 'semester' => '4',
            ])->id,
            'department_id' => $this->dept->id,
            'frontend_tech' => 'Blade', 'backend_tech' => 'Laravel',
            'abstract' => 'A portal for hostel administration and mess records.',
            'status' => Project::STATUS_SYNOPSIS_REVIEW,
        ]);

        ProjectSimilarity::create([
            'project_id' => $this->project->id,
            'matched_project_id' => $other->id,
            'stage' => ProjectSimilarity::STAGE_SYNOPSIS,
            'score' => 91.5,
            'details' => ['phrases' => ['hostel administration mess records'], 'shared' => 4, 'matched_name' => $other->name],
        ]);

        $viva = Viva::create([
            'project_id' => $this->project->id,
            'scheduled_at' => now()->addWeek(),
            'venue' => 'Lab 3',
            'mode' => Viva::MODE_OFFLINE,
            'created_by' => $this->guide->user_id,
        ]);
        $viva->panelists()->sync([$this->guide->id]);

        FacultyAssignmentHistory::create([
            'project_id' => $this->project->id,
            'from_faculty_id' => null,
            'to_faculty_id' => $this->guide->id,
            'changed_by' => $this->admin->id,
            'changed_at' => now()->subDay(),
        ]);

        RevisionRequest::create([
            'project_id' => $this->project->id,
            'stage' => RevisionRequest::STAGE_FINAL,
            'faculty_id' => $this->guide->id,
            'reason' => 'The testing chapter is missing',
            'due_at' => now()->addDays(3),
        ]);
    }

    public function test_all_new_admin_screens_render(): void
    {
        $this->withExtras();
        $finding = ProjectSimilarity::first();

        $year = AcademicYear::create(['label' => '2019-20']);
        $year->archive();

        foreach ([
            route('admin.academic-years.index'),
            route('admin.archive.index'),
            route('admin.approvals.index'),
            route('admin.similarity.index'),
            route('admin.similarity.show', $finding),
            route('admin.workload.index'),
            route('admin.projects.index'),
            route('admin.projects.show', $this->project),
            route('admin.settings.edit'),
            route('admin.departments.index'),
            route('admin.students.index'),
            route('admin.students.semester-review'),
            route('admin.alumni.index'),
            route('admin.dashboard'),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_the_dashboards_render_at_every_project_status(): void
    {
        foreach ([
            Project::STATUS_SYNOPSIS_PENDING,
            Project::STATUS_SYNOPSIS_REVIEW,
            Project::STATUS_CORRECTION,
            Project::STATUS_REJECTED,
            Project::STATUS_SYNOPSIS_APPROVED,
            Project::STATUS_FINAL_SUBMITTED,
            Project::STATUS_FINAL_REVIEWED,
            Project::STATUS_COMPLETED,
        ] as $status) {
            $this->project->update(['status' => $status]);

            $this->actingAs($this->student->user)->get(route('student.dashboard'))
                ->assertOk()->assertSee('Your next step')->assertSee('stage-rail', false);
            $this->actingAs($this->guide->user)->get(route('faculty.dashboard'))
                ->assertOk()->assertSee('Review queue');
            $this->actingAs($this->admin)->get(route('admin.dashboard'))
                ->assertOk()->assertSee('Where every project stands')->assertSee('college-logo', false);
        }
    }

    public function test_the_faculty_project_page_renders_with_every_new_card(): void
    {
        $this->withExtras();

        $this->actingAs($this->guide->user)
            ->get(route('faculty.projects.show', $this->project))
            ->assertOk()
            ->assertSee('Viva')
            ->assertSee('Request Re-upload')
            ->assertSee('Similarity flagged');
    }

    public function test_the_student_project_page_renders_with_every_new_card(): void
    {
        $this->withExtras();

        $this->actingAs($this->student->user)
            ->get(route('student.project.show'))
            ->assertOk()
            ->assertSee('Viva')
            ->assertSee('asked you to upload this again');
    }

    public function test_the_student_assignment_list_renders_with_marks_and_a_reupload(): void
    {
        $assignment = Assignment::create([
            'faculty_id' => $this->guide->id,
            'department_id' => $this->dept->id,
            'subject' => 'DBMS',
            'type' => 'Theory',
            'title' => 'ER Diagrams',
            'max_marks' => 10,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'file_path' => 'x.pdf',
            'submitted_at' => now(),
            'status' => 'checked',
            'marks' => 8,
            'checked_at' => now(),
        ]);

        RevisionRequest::create([
            'assignment_submission_id' => $submission->id,
            'stage' => RevisionRequest::STAGE_ASSIGNMENT,
            'faculty_id' => $this->guide->id,
            'reason' => 'Wrong file uploaded',
        ]);

        $this->actingAs($this->student->user)
            ->get(route('student.assignments.index'))
            ->assertOk()
            ->assertSee('8 / 10')
            ->assertSee('Wrong file uploaded');

        $this->actingAs($this->guide->user)
            ->get(route('faculty.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('8 / 10');
    }

    public function test_an_unmarked_assignment_shows_no_marks_anywhere(): void
    {
        $assignment = Assignment::create([
            'faculty_id' => $this->guide->id,
            'department_id' => $this->dept->id,
            'subject' => 'DBMS',
            'type' => 'Theory',
            'title' => 'Reading Task',
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'file_path' => 'x.pdf',
            'submitted_at' => now(),
            'status' => 'checked',
            'checked_at' => now(),
        ]);

        $this->actingAs($this->guide->user)
            ->get(route('faculty.assignments.show', $assignment))
            ->assertOk()
            ->assertDontSee('<th class="text-center">Marks</th>', false);

        $this->actingAs($this->student->user)
            ->get(route('student.assignments.index'))
            ->assertOk()
            ->assertDontSee('Marks:');
    }

    public function test_the_archive_page_renders_an_archived_project(): void
    {
        $year = AcademicYear::create(['label' => '2019-20']);
        $this->project->update(['academic_year_id' => $year->id]);
        $year->archive();

        $this->actingAs($this->admin)
            ->get(route('admin.archive.show', $this->project->fresh()))
            ->assertOk()
            ->assertSee('read-only');
    }
}
