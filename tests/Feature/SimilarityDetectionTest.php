<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectSimilarity;
use App\Models\Student;
use App\Models\User;
use App\Support\SimilarityService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarityDetectionTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        AcademicYear::flushCurrent();

        $this->dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);

        CollegeSetting::create([
            'name' => 'Test College',
            'duplicate_warn_percent' => 60,
            'duplicate_block_percent' => 85,
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

    private function makeProject(string $name, string $abstract, ?AcademicYear $year = null): Project
    {
        static $n = 0;
        $n++;
        $student = $this->makeStudent("owner{$n}@test.com", "OWN{$n}");

        return Project::create([
            'project_type' => 'single',
            'name' => $name,
            'leader_student_id' => $student->id,
            'department_id' => $this->dept->id,
            'academic_year_id' => $year?->id,
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => $abstract,
            'status' => Project::STATUS_SYNOPSIS_REVIEW,
        ]);
    }

    public function test_identical_text_scores_at_the_top(): void
    {
        $text = 'An online portal that lets hostel wardens record room allocation and mess attendance for every resident.';

        $result = SimilarityService::compare($text, $text);

        $this->assertSame(100.0, $result['score']);
    }

    public function test_unrelated_text_scores_near_zero(): void
    {
        $a = 'An online portal for hostel wardens to record room allocation and mess attendance.';
        $b = 'A mobile game where players grow crops, trade livestock and compete in weekly harvest tournaments.';

        $result = SimilarityService::compare($a, $b);

        $this->assertLessThan(15, $result['score']);
    }

    public function test_reworded_copy_still_scores_high(): void
    {
        $a = 'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens.';
        $b = 'Our hostel management portal records room allocation, mess attendance and monthly fee receipts for the warden.';

        $result = SimilarityService::compare($a, $b);

        $this->assertGreaterThan(60, $result['score']);
        $this->assertNotEmpty($result['phrases']);
    }

    public function test_a_short_abstract_pasted_into_a_longer_one_is_caught(): void
    {
        $short = 'Records room allocation, mess attendance and monthly fee receipts for hostel wardens.';
        $long = 'This document describes our approach. '
            .'Records room allocation, mess attendance and monthly fee receipts for hostel wardens. '
            .'We then evaluated the outcome across three separate trial deployments and gathered feedback.';

        $result = SimilarityService::compare($short, $long);

        $this->assertGreaterThan(60, $result['score']);
    }

    public function test_empty_text_scores_zero_rather_than_erroring(): void
    {
        $this->assertSame(0.0, SimilarityService::compare(null, 'anything at all here')['score']);
        $this->assertSame(0.0, SimilarityService::compare('', '')['score']);
    }

    public function test_a_near_identical_synopsis_is_refused_server_side(): void
    {
        $this->makeProject(
            'Hostel Management Portal',
            'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens across every block.'
        );

        $student = $this->makeStudent('copycat@test.com', 'R99');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Hostel Management Portal',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens across every block.',
        ])->assertRedirect();

        $this->assertSame(1, Project::count());
        $this->assertDatabaseMissing('projects', ['leader_student_id' => $student->id]);
    }

    public function test_an_original_synopsis_goes_through_and_is_scored(): void
    {
        $this->makeProject(
            'Hostel Management Portal',
            'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens.'
        );

        $student = $this->makeStudent('original@test.com', 'R98');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Weather Forecast Visualiser',
            'frontend_tech' => 'Vue',
            'backend_tech' => 'Node',
            'abstract' => 'A dashboard charting rainfall, humidity and wind readings collected from district weather stations.',
        ])->assertRedirect(route('student.project.show'));

        $this->assertDatabaseHas('projects', ['name' => 'Weather Forecast Visualiser']);
    }

    public function test_archived_years_are_still_compared_against(): void
    {
        $oldYear = AcademicYear::create(['label' => '2020-21']);

        $this->makeProject(
            'Hostel Management Portal',
            'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens across every block.',
            $oldYear
        );

        $oldYear->archive();

        $student = $this->makeStudent('copycat@test.com', 'R97');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Hostel Management Portal',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens across every block.',
        ])->assertRedirect();

        $this->assertDatabaseMissing('projects', ['leader_student_id' => $student->id]);
    }

    public function test_findings_are_recorded_for_a_warning_level_match(): void
    {
        $original = $this->makeProject(
            'Library Book Tracker',
            'Tracks book issue, return dates and overdue fines for the college library counter staff.'
        );

        $student = $this->makeStudent('similar@test.com', 'R96');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Library Book Tracker',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'Tracks book issue, return dates and overdue fines for library counter staff, plus a reservation queue and SMS alerts for members.',
        ]);

        $new = Project::where('leader_student_id', $student->id)->first();
        $this->assertNotNull($new, 'The synopsis should have been accepted.');

        $this->assertDatabaseHas('project_similarities', [
            'project_id' => $new->id,
            'matched_project_id' => $original->id,
            'stage' => ProjectSimilarity::STAGE_SYNOPSIS,
        ]);
    }

    public function test_blocking_can_be_switched_off_by_raising_the_threshold(): void
    {
        CollegeSetting::first()->update(['duplicate_block_percent' => 100]);
        CollegeSetting::current();

        $this->makeProject(
            'Hostel Management Portal',
            'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens.'
        );

        $student = $this->makeStudent('copycat@test.com', 'R95');

        $this->actingAs($student->user)->post(route('student.synopsis.store'), [
            'project_type' => 'single',
            'name' => 'Hostel Management Portal',
            'frontend_tech' => 'Blade',
            'backend_tech' => 'Laravel',
            'abstract' => 'The hostel management portal records room allocation, mess attendance and monthly fee receipts for wardens.',
        ]);

        $this->assertDatabaseHas('projects', ['leader_student_id' => $student->id]);
    }

    public function test_the_similarity_report_is_staff_only(): void
    {
        $student = $this->makeStudent('s@test.com', 'R94');

        $this->actingAs($student->user)
            ->get(route('admin.similarity.index'))
            ->assertRedirect(route('dashboard'));
    }
}
