<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectReview;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Bulk demo data for testing: 25 students (all departments),
     * 5 faculty, an extra admin, and several projects in various states.
     *
     * Run with:  php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $departments = Department::all();
        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Run the main seeder first.');

            return;
        }

        // ----- Extra Admin -----
        User::firstOrCreate(
            ['email' => 'admin2@demo.test'],
            ['name' => 'Ravi Mehta', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        // ----- 5 Faculty (spread across departments) -----
        $faculties = collect();
        $facultyNames = ['Dr. Suresh Nair', 'Prof. Meena Iyer', 'Dr. Vikram Singh', 'Prof. Neha Joshi', 'Dr. Arjun Menon'];
        foreach ($facultyNames as $i => $name) {
            $u = User::firstOrCreate(
                ['email' => 'faculty'.($i + 1).'@demo.test'],
                ['name' => $name, 'password' => Hash::make('password'), 'role' => 'faculty']
            );
            $primary = $departments[$i % $departments->count()];
            $faculty = Faculty::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'department_id' => $primary->id,
                    'designation' => fake()->randomElement(['Assistant Professor', 'Associate Professor', 'Professor']),
                    'phone' => '90000000'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                ]
            );

            // Demonstrate the many-to-many: each faculty teaches 2 courses/departments.
            $second = $departments[($i + 1) % $departments->count()];
            $faculty->departments()->sync(array_unique([$primary->id, $second->id]));

            $faculties->push($faculty);
        }

        // ----- 25 Students (distributed across all departments) -----
        // Fixed, realistic names so demo data never shows random Faker names.
        $studentNames = [
            'Sneha Patel', 'Karan Mehta', 'Riya Shah', 'Arjun Nair', 'Neha Joshi',
            'Rohit Desai', 'Pooja Iyer', 'Vikram Singh', 'Ananya Reddy', 'Nikhil Kulkarni',
            'Isha Agarwal', 'Manish Chauhan', 'Divya Pillai', 'Harsh Trivedi', 'Meera Bhatt',
            'Yash Thakkar', 'Sana Sheikh', 'Deepak Yadav', 'Aarti Deshmukh', 'Gaurav Malhotra',
            'Tanvi Kapoor', 'Rohan Bose', 'Shreya Ghosh', 'Aditya Kumar', 'Nisha Rana',
        ];
        $students = collect();
        for ($i = 1; $i <= 25; $i++) {
            $dept = $departments[($i - 1) % $departments->count()];
            $u = User::firstOrCreate(
                ['email' => 'student'.$i.'@demo.test'],
                ['name' => $studentNames[$i - 1] ?? ('Student '.$i), 'password' => Hash::make('password'), 'role' => 'student']
            );
            $students->push(Student::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'department_id' => $dept->id,
                    'roll_no' => $dept->code.'2026'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'phone' => '80000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                ]
            ));
        }

        // ----- Projects in various states -----
        $titles = [
            'Online Library Management System', 'E-Commerce Shopping Portal', 'Hospital Management System',
            'Student Attendance Tracker', 'Food Delivery App', 'Event Booking Platform',
            'Blood Bank Management', 'Online Voting System', 'Inventory Management System',
            'Job Portal', 'Tourism Booking Website', 'Fitness Tracker Dashboard',
        ];
        $frontends = ['HTML/CSS/JS', 'Bootstrap', 'React', 'Vue', 'Blade + Bootstrap'];
        $backends = ['Laravel', 'PHP', 'Node.js', 'Django'];
        $statuses = [
            Project::STATUS_SYNOPSIS_REVIEW,
            Project::STATUS_SYNOPSIS_APPROVED,
            Project::STATUS_CORRECTION,
            Project::STATUS_FINAL_SUBMITTED,
            Project::STATUS_COMPLETED,
            Project::STATUS_COMPLETED,
        ];

        // Only students without a project can lead one.
        $available = $students->filter(fn ($s) => ! $s->hasProject())->values();

        foreach ($titles as $idx => $title) {
            if ($idx >= $available->count()) {
                break;
            }
            $leader = $available[$idx];
            $status = $statuses[$idx % count($statuses)];
            $isCompleted = in_array($status, [Project::STATUS_COMPLETED, Project::STATUS_FINAL_REVIEWED], true);

            $project = Project::create([
                'project_type' => 'single',
                'name' => $title,
                'leader_student_id' => $leader->id,
                'department_id' => $leader->department_id,
                'frontend_tech' => fake()->randomElement($frontends),
                'backend_tech' => fake()->randomElement($backends),
                'abstract' => fake()->paragraph(4),
                'status' => $status,
                'marks' => $isCompleted ? rand(60, 95) : null,
                'final_remarks' => $isCompleted ? 'Well executed project with good documentation.' : null,
            ]);

            ProjectMember::create([
                'project_id' => $project->id,
                'student_id' => $leader->id,
                'role_in_project' => 'leader',
            ]);

            // Assign a faculty to most projects (leave the first synopsis-review one unassigned).
            if ($status !== Project::STATUS_SYNOPSIS_REVIEW) {
                $faculty = $faculties[$idx % $faculties->count()];
                FacultyAssignment::create([
                    'project_id' => $project->id,
                    'faculty_id' => $faculty->id,
                    'assigned_at' => now(),
                ]);

                if ($isCompleted) {
                    ProjectReview::create([
                        'project_id' => $project->id,
                        'faculty_id' => $faculty->id,
                        'stage' => 'final',
                        'action' => 'reviewed',
                        'comments' => 'Approved. Good work.',
                        'marks' => $project->marks,
                    ]);
                }
            }
        }

        $this->command->info('Demo data seeded: 25 students, 5 faculty, 1 extra admin, '.count($titles).' projects.');
    }
}
