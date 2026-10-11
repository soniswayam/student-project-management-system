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
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->seedCollegeSettings();

        User::updateOrCreate(
            ['email' => 'superadmin@ncc.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@ncc.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $departments = collect([
            ['name' => 'BCA', 'code' => 'BCA'],
            ['name' => 'MSC (IT & CA)', 'code' => 'MSCITCA'],
            ['name' => 'BBA', 'code' => 'BBA'],
            ['name' => 'B.Com', 'code' => 'BCOM'],
        ])->map(fn ($d) => Department::updateOrCreate(['code' => $d['code']], $d));

        $msc = $departments->firstWhere('code', 'MSCITCA');

        $facUser = User::updateOrCreate(
            ['email' => 'faculty@spss.test'],
            ['name' => 'Dr. Anita Rao', 'password' => Hash::make('password'), 'role' => 'faculty']
        );
        Faculty::updateOrCreate(
            ['user_id' => $facUser->id],
            ['department_id' => $msc->id, 'designation' => 'Associate Professor', 'phone' => '9000000001']
        );

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
