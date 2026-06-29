<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with an admin, departments,
     * and a few demo faculty and students for testing.
     */
    public function run(): void
    {
        // ----- Admin -----
        User::updateOrCreate(
            ['email' => 'admin@spss.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // ----- Departments -----
        $departments = collect([
            ['name' => 'Computer Science & Engineering', 'code' => 'CSE'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Electronics & Communication', 'code' => 'ECE'],
        ])->map(fn ($d) => Department::updateOrCreate(['code' => $d['code']], $d));

        $cse = $departments->firstWhere('code', 'CSE');

        // ----- Demo Faculty -----
        $facUser = User::updateOrCreate(
            ['email' => 'faculty@spss.test'],
            ['name' => 'Dr. Anita Rao', 'password' => Hash::make('password'), 'role' => 'faculty']
        );
        Faculty::updateOrCreate(
            ['user_id' => $facUser->id],
            ['department_id' => $cse->id, 'designation' => 'Associate Professor', 'phone' => '9000000001']
        );

        // ----- Demo Students -----
        $studentsData = [
            ['name' => 'Rahul Sharma', 'email' => 'rahul@spss.test', 'roll_no' => 'CSE2026001'],
            ['name' => 'Priya Verma', 'email' => 'priya@spss.test', 'roll_no' => 'CSE2026002'],
            ['name' => 'Aman Gupta', 'email' => 'aman@spss.test', 'roll_no' => 'CSE2026003'],
        ];

        foreach ($studentsData as $s) {
            $u = User::updateOrCreate(
                ['email' => $s['email']],
                ['name' => $s['name'], 'password' => Hash::make('password'), 'role' => 'student']
            );
            Student::updateOrCreate(
                ['user_id' => $u->id],
                ['department_id' => $cse->id, 'roll_no' => $s['roll_no'], 'phone' => '8000000000']
            );
        }
    }
}
