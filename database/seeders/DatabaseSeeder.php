<?php

namespace Database\Seeders;

use App\Models\CollegeSetting;
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
        $this->call(RoleSeeder::class);
        $this->seedCollegeSettings();

        // ----- Super Admin (full control) -----
        User::updateOrCreate(
            ['email' => 'superadmin@spss.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        // ----- Admin (limited: students, projects, reports) -----
        User::updateOrCreate(
            ['email' => 'admin@spss.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // ----- Departments (college courses) -----
        $departments = collect([
            ['name' => 'BCA', 'code' => 'BCA'],
            ['name' => 'MSC (IT & CA)', 'code' => 'MSCITCA'],
            ['name' => 'BBA', 'code' => 'BBA'],
            ['name' => 'B.Com', 'code' => 'BCOM'],
        ])->map(fn ($d) => Department::updateOrCreate(['code' => $d['code']], $d));

        $msc = $departments->firstWhere('code', 'MSCITCA');

        // ----- Demo Faculty -----
        $facUser = User::updateOrCreate(
            ['email' => 'faculty@spss.test'],
            ['name' => 'Dr. Anita Rao', 'password' => Hash::make('password'), 'role' => 'faculty']
        );
        Faculty::updateOrCreate(
            ['user_id' => $facUser->id],
            ['department_id' => $msc->id, 'designation' => 'Associate Professor', 'phone' => '9000000001']
        );

        // ----- Demo Students (MSC IT & CA, Sem-3) -----
        $studentsData = [
            ['name' => 'Rahul Sharma', 'email' => 'rahul@spss.test', 'roll_no' => 'MSC2026001'],
            ['name' => 'Priya Verma', 'email' => 'priya@spss.test', 'roll_no' => 'MSC2026002'],
            ['name' => 'Aman Gupta', 'email' => 'aman@spss.test', 'roll_no' => 'MSC2026003'],
        ];

        foreach ($studentsData as $s) {
            $u = User::updateOrCreate(
                ['email' => $s['email']],
                ['name' => $s['name'], 'password' => Hash::make('password'), 'role' => 'student']
            );
            Student::updateOrCreate(
                ['user_id' => $u->id],
                ['department_id' => $msc->id, 'roll_no' => $s['roll_no'], 'semester' => '3', 'phone' => '8000000000']
            );
        }
    }

    /** Seed the single college settings row from config defaults. */
    private function seedCollegeSettings(): void
    {
        if (CollegeSetting::count() === 0) {
            CollegeSetting::create([
                'name' => config('college.name'),
                'tagline' => config('college.tagline'),
                'address' => config('college.address'),
                'affiliation' => config('college.affiliation'),
                'email' => config('college.email'),
                'phone' => config('college.phone'),
                'website' => config('college.website'),
            ]);
        }
    }
}
