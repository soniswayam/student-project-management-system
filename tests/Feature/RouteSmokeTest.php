<?php

namespace Tests\Feature;

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

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_key_routes_load_for_the_correct_role(): void
    {
        $this->seed(RoleSeeder::class);

        $dept = Department::create(['name' => 'CSE', 'code' => 'CSE']);

        $super = User::create(['name' => 'Super', 'email' => 'super@t.com', 'password' => 'password', 'role' => 'super_admin']);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@t.com', 'password' => 'password', 'role' => 'admin']);
        $facUser = User::create(['name' => 'Fac', 'email' => 'fac@t.com', 'password' => 'password', 'role' => 'faculty']);
        $faculty = Faculty::create(['user_id' => $facUser->id, 'department_id' => $dept->id]);
        $stuUser = User::create(['name' => 'Stu', 'email' => 'stu@t.com', 'password' => 'password', 'role' => 'student']);
        $student = Student::create(['user_id' => $stuUser->id, 'department_id' => $dept->id, 'roll_no' => 'R1']);

        $project = Project::create([
            'project_type' => 'single', 'name' => 'P', 'leader_student_id' => $student->id,
            'department_id' => $dept->id, 'frontend_tech' => 'JS', 'backend_tech' => 'Laravel',
            'abstract' => str_repeat('idea ', 10), 'status' => Project::STATUS_COMPLETED, 'marks' => 90,
        ]);
        ProjectMember::create(['project_id' => $project->id, 'student_id' => $student->id, 'role_in_project' => 'leader']);
        FacultyAssignment::create(['project_id' => $project->id, 'faculty_id' => $faculty->id, 'assigned_at' => now()]);

        $checks = [
            // Super admin — full access incl. structural pages.
            [$super, 'GET', route('admin.dashboard')],
            [$super, 'GET', route('admin.faculties.create')],
            [$super, 'GET', route('admin.departments.index')],
            [$super, 'GET', route('admin.admins.index')],
            [$super, 'GET', route('admin.roles.index')],
            [$super, 'GET', route('admin.roles.create')],
            [$super, 'GET', route('admin.settings.edit')],
            [$super, 'GET', route('admin.projects.synopsis', $project)],
            // Admin — permitted operational pages.
            [$admin, 'GET', route('admin.dashboard')],
            [$admin, 'GET', route('admin.students.index')],
            [$admin, 'GET', route('admin.students.create')],
            [$admin, 'GET', route('admin.faculties.index')],
            [$admin, 'GET', route('admin.projects.index')],
            [$admin, 'GET', route('admin.projects.show', $project)],
            [$admin, 'GET', route('admin.reports.index')],
            [$admin, 'GET', route('admin.reports.export')],
            [$admin, 'GET', route('admin.students.export')],
            [$admin, 'GET', route('admin.projects.export')],
            [$admin, 'GET', route('admin.projects.pdf', $project)],
            [$admin, 'GET', route('admin.projects.certificate', $project)],
            // Faculty.
            [$facUser, 'GET', route('faculty.dashboard')],
            [$facUser, 'GET', route('faculty.projects.index')],
            [$facUser, 'GET', route('faculty.projects.show', $project)],
            // Student.
            [$stuUser, 'GET', route('student.dashboard')],
            [$stuUser, 'GET', route('student.project.show')],
            [$stuUser, 'GET', route('student.project.certificate')],
            [$stuUser, 'GET', route('student.synopsis.download')],
        ];

        $failures = [];
        foreach ($checks as [$user, $method, $url]) {
            $res = $this->actingAs($user)->call($method, $url);
            if ($res->getStatusCode() >= 400) {
                $failures[] = "{$user->role} {$url} => {$res->getStatusCode()}";
            }
        }

        $this->assertSame([], $failures, "Routes that failed:\n".implode("\n", $failures));
    }
}
