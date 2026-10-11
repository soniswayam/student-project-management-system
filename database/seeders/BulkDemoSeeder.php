<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectReview;
use App\Models\ProjectSubmission;
use App\Models\RevisionRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Viva;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BulkDemoSeeder extends Seeder
{
    private const DOMAIN = 'bulk.spss.test';

    private const STUDENT_COUNT = 500;

    private Carbon $now;

    private ?int $yearId;

    public function run(): void
    {
        if (User::where('email', 'like', '%@'.self::DOMAIN)->exists()) {
            $this->command->warn('Bulk demo data already exists (@'.self::DOMAIN.'). Nothing done.');

            return;
        }

        $departments = Department::orderBy('id')->get();
        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Run the main seeder first.');

            return;
        }

        mt_srand(2026);
        $this->now = now();
        $this->yearId = AcademicYear::currentId();
        $password = Hash::make('password');
        $adminId = User::where('role', 'super_admin')->value('id');

        DB::transaction(function () use ($departments, $password, $adminId) {
            $faculties = $this->seedFaculty($departments, $password);
            $students = $this->seedStudents($departments, $password, $adminId);
            $projects = $this->seedProjects($students, $faculties);
            $this->seedAssignments($departments, $students, $faculties);
            $this->seedNotifications($projects);

            $this->command->info(sprintf(
                'Bulk data seeded: %d students, %d faculty, %d projects, %d assignments.',
                $students->count(), $faculties->flatten()->count(), $projects->count(),
                Assignment::whereIn('faculty_id', $faculties->flatten()->pluck('id'))->count()
            ));
        });
    }

    private function seedFaculty($departments, string $password)
    {
        $names = [
            'Dr. Hemant Parmar', 'Prof. Kinjal Shah', 'Dr. Bhavesh Patel', 'Prof. Jignasha Desai',
            'Dr. Rakesh Joshi', 'Prof. Foram Mehta', 'Dr. Nilesh Trivedi', 'Prof. Hetal Vyas',
        ];
        $designations = ['Assistant Professor', 'Associate Professor', 'Professor'];

        $byDept = collect();
        foreach ($names as $i => $name) {
            $dept = $departments[$i % $departments->count()];
            $user = User::create([
                'name' => $name,
                'email' => 'faculty'.($i + 1).'@'.self::DOMAIN,
                'password' => $password,
                'role' => 'faculty',
                'status' => User::STATUS_ACTIVE,
            ]);
            $faculty = Faculty::create([
                'user_id' => $user->id,
                'department_id' => $dept->id,
                'designation' => $designations[$i % 3],
                'max_projects' => 30,
                'phone' => '98250'.str_pad((string) (10000 + $i), 5, '0', STR_PAD_LEFT),
            ]);
            $faculty->departments()->sync([$dept->id]);

            $byDept[$dept->id] = ($byDept[$dept->id] ?? collect())->push($faculty);
        }

        return $byDept;
    }

    private function seedStudents($departments, string $password, ?int $adminId)
    {
        $first = ['Aarav', 'Vivaan', 'Aditya', 'Vihaan', 'Arjun', 'Sai', 'Reyansh', 'Krish', 'Ishaan', 'Shaurya',
            'Dhruv', 'Kabir', 'Ritvik', 'Ayaan', 'Parth', 'Harsh', 'Jay', 'Meet', 'Dev', 'Yash',
            'Ananya', 'Diya', 'Aadhya', 'Saanvi', 'Pari', 'Anika', 'Navya', 'Myra', 'Riya', 'Khushi',
            'Isha', 'Kavya', 'Nidhi', 'Pooja', 'Hetvi', 'Janvi', 'Krupa', 'Mansi', 'Dhruvi', 'Vidhi'];
        $last = ['Patel', 'Shah', 'Mehta', 'Desai', 'Joshi', 'Trivedi', 'Vyas', 'Pandya', 'Bhatt', 'Parmar',
            'Chauhan', 'Solanki', 'Rathod', 'Thakkar', 'Modi', 'Dave', 'Raval', 'Soni', 'Prajapati', 'Makwana'];

        $students = collect();
        for ($i = 1; $i <= self::STUDENT_COUNT; $i++) {
            $dept = $departments[($i - 1) % $departments->count()];
            $maxSem = $dept->finalSemester() ?? 6;
            $semester = (string) ((intdiv($i - 1, $departments->count()) % $maxSem) + 1);

            $fn = $first[mt_rand(0, count($first) - 1)];
            $ln = $last[mt_rand(0, count($last) - 1)];

            $roll = mt_rand(1, 100);
            $status = $roll <= 3 ? User::STATUS_PENDING : ($roll === 4 ? User::STATUS_BLOCKED : User::STATUS_ACTIVE);

            $user = User::create([
                'name' => "$fn $ln",
                'email' => strtolower("$fn.$ln.$i").'@'.self::DOMAIN,
                'password' => $password,
                'role' => 'student',
                'status' => $status,
                'status_reason' => $status === User::STATUS_BLOCKED ? 'Fees pending — blocked by office.' : null,
                'approved_at' => $status === User::STATUS_ACTIVE ? $this->now->copy()->subDays(mt_rand(20, 90)) : null,
                'approved_by' => $status === User::STATUS_ACTIVE ? $adminId : null,
            ]);

            $students->push(Student::create([
                'user_id' => $user->id,
                'department_id' => $dept->id,
                'roll_no' => $dept->code.'26B'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'semester' => $semester,
                'phone' => '9'.str_pad((string) (800000000 + $i), 9, '0', STR_PAD_LEFT),
            ])->setRelation('user', $user)->setRelation('department', $dept));
        }

        return $students;
    }

    private function seedProjects($students, $facultiesByDept)
    {
        $titles = ['Online Library Management System', 'E-Commerce Shopping Portal', 'Hospital Management System',
            'Student Attendance Tracker', 'Food Delivery App', 'Event Booking Platform', 'Blood Bank Management',
            'Online Voting System', 'Inventory Management System', 'Job Portal', 'Tourism Booking Website',
            'Fitness Tracker Dashboard', 'Hostel Management System', 'Pharmacy Billing System', 'Car Rental Portal',
            'Online Quiz Application', 'Payroll Management System', 'Real Estate Listing Portal', 'Gym Membership Manager',
            'College Canteen Ordering', 'Bus Pass Renewal System', 'Lost and Found Portal', 'Recipe Sharing Website',
            'Online Examination System', 'Complaint Management System', 'Parking Slot Booking', 'Salon Appointment App',
            'Expense Tracker', 'Courier Tracking System', 'Matrimony Portal', 'Agriculture Market Price Portal',
            'Retail Shop GST Billing', 'Tiffin Service Manager', 'Online Auction System', 'Dairy Milk Collection System'];
        $frontends = ['HTML/CSS/JS', 'Bootstrap', 'React', 'Vue', 'Blade + Bootstrap', 'Angular'];
        $backends = ['Laravel', 'PHP', 'Node.js', 'Django', 'ASP.NET Core'];

        $plan = array_merge(
            array_fill(0, 6, Project::STATUS_SYNOPSIS_REVIEW),
            array_fill(0, 2, Project::STATUS_CORRECTION),
            array_fill(0, 1, Project::STATUS_REJECTED),
            array_fill(0, 4, Project::STATUS_SYNOPSIS_APPROVED),
            array_fill(0, 3, Project::STATUS_FINAL_SUBMITTED),
            array_fill(0, 1, Project::STATUS_FINAL_REVIEWED),
            array_fill(0, 3, Project::STATUS_COMPLETED),
        );

        $eligible = $students
            ->filter(fn ($s) => $s->user->status === User::STATUS_ACTIVE && (int) $s->semester >= 3)
            ->groupBy(fn ($s) => $s->department_id.'-'.$s->semester);

        $projects = collect();
        $n = 0;
        foreach ($eligible as $pool) {
            $pool = $pool->values();
            $useUntil = (int) floor($pool->count() * 0.85);
            $k = 0;
            while ($k < $useUntil) {
                $leader = $pool[$k++];
                $dept = $leader->department;
                $maxGroup = $leader->department->levelSettings()?->max_group_size ?? 1;
                $partner = ($maxGroup > 1 && $k < $useUntil && mt_rand(1, 100) <= 40) ? $pool[$k++] : null;

                $status = $plan[$n % count($plan)];
                $title = $titles[$n % count($titles)].($n >= count($titles) ? ' v'.(intdiv($n, count($titles)) + 1) : '');
                $created = $this->now->copy()->subDays(mt_rand(15, 75));
                $guides = $facultiesByDept[$dept->id];
                $guide = $guides[$n % $guides->count()];
                $n++;

                $isFinal = in_array($status, [Project::STATUS_FINAL_SUBMITTED, Project::STATUS_FINAL_REVIEWED, Project::STATUS_COMPLETED], true);
                $maxMarks = $dept->levelSettings()?->max_marks;
                $marks = ($maxMarks && in_array($status, [Project::STATUS_FINAL_REVIEWED, Project::STATUS_COMPLETED], true))
                    ? (int) round($maxMarks * mt_rand(55, 95) / 100) : null;

                $project = new Project([
                    'project_type' => $partner ? 'group' : 'single',
                    'name' => $title,
                    'leader_student_id' => $leader->id,
                    'department_id' => $dept->id,
                    'academic_year_id' => $this->yearId,
                    'frontend_tech' => $frontends[mt_rand(0, count($frontends) - 1)],
                    'backend_tech' => $backends[mt_rand(0, count($backends) - 1)],
                    'abstract' => "This project \"{$title}\" digitises a manual process for small organisations. "
                        .'It provides role-based login, record management, search and reports, '
                        .'reducing paperwork and errors while giving management a live dashboard.',
                    'status' => $status,
                    'revision' => in_array($status, [Project::STATUS_CORRECTION, Project::STATUS_REJECTED], true) ? 1 : 0,
                    'marks' => $marks,
                    'final_remarks' => in_array($status, [Project::STATUS_FINAL_REVIEWED, Project::STATUS_COMPLETED], true)
                        ? 'Good implementation and documentation. Viva answered well.' : null,
                ]);
                $project->created_at = $created;
                $project->updated_at = $created->copy()->addDays(mt_rand(1, 10));
                $project->save();

                ProjectMember::create(['project_id' => $project->id, 'student_id' => $leader->id, 'role_in_project' => 'leader']);
                if ($partner) {
                    ProjectMember::create(['project_id' => $project->id, 'student_id' => $partner->id, 'role_in_project' => 'partner']);
                }

                $assign = ! ($status === Project::STATUS_SYNOPSIS_REVIEW && $n % 3 === 0);
                if ($assign) {
                    FacultyAssignment::create([
                        'project_id' => $project->id,
                        'faculty_id' => $guide->id,
                        'assigned_at' => $created->copy()->addDay(),
                    ]);
                }

                $this->seedReviewTrail($project, $status, $guide, $created, $marks);

                if ($isFinal) {
                    $this->seedFinalSubmission($project, $created);
                }

                if ($isFinal && $dept->isPostGraduation()) {
                    Viva::create([
                        'project_id' => $project->id,
                        'scheduled_at' => $status === Project::STATUS_COMPLETED
                            ? $this->now->copy()->subDays(mt_rand(1, 10))->setTime(10, 30)
                            : $this->now->copy()->addDays(mt_rand(2, 14))->setTime(11, 0),
                        'venue' => 'Lab '.mt_rand(1, 4),
                        'mode' => Viva::MODE_OFFLINE,
                        'status' => $status === Project::STATUS_COMPLETED ? Viva::STATUS_COMPLETED : Viva::STATUS_SCHEDULED,
                        'created_by' => $guide->user_id,
                    ]);
                }

                if ($status === Project::STATUS_FINAL_SUBMITTED && $n % 5 === 0) {
                    RevisionRequest::create([
                        'project_id' => $project->id,
                        'stage' => RevisionRequest::STAGE_FINAL,
                        'faculty_id' => $guide->id,
                        'reason' => 'ER diagram missing in report; please upload the corrected report.',
                        'due_at' => $this->now->copy()->addDays(5),
                    ]);
                }

                $projects->push($project);
            }
        }

        return $projects;
    }

    private function seedReviewTrail(Project $project, string $status, Faculty $guide, Carbon $created, ?int $marks): void
    {
        $review = function (string $stage, string $action, ?string $comments, int $day, ?int $m = null) use ($project, $guide, $created) {
            $r = new ProjectReview([
                'project_id' => $project->id,
                'faculty_id' => $guide->id,
                'stage' => $stage,
                'action' => $action,
                'comments' => $comments,
                'marks' => $m,
            ]);
            $r->created_at = $r->updated_at = $created->copy()->addDays($day);
            $r->save();
        };

        switch ($status) {
            case Project::STATUS_CORRECTION:
                $review('synopsis', 'correction', 'Scope is too broad. Add module list and DFD level-0.', 3);
                break;
            case Project::STATUS_REJECTED:
                $review('synopsis', 'rejected', 'Topic is already taken by another group. Choose a new topic.', 3);
                break;
            case Project::STATUS_SYNOPSIS_APPROVED:
            case Project::STATUS_FINAL_SUBMITTED:
                $review('synopsis', 'approved', 'Approved. Start development.', 4);
                break;
            case Project::STATUS_FINAL_REVIEWED:
            case Project::STATUS_COMPLETED:
                $review('synopsis', 'approved', 'Approved. Start development.', 4);
                $review('final', 'reviewed', 'Project checked. Working demo shown.', 12, $marks);
                break;
        }
    }

    private function seedFinalSubmission(Project $project, Carbon $created): void
    {
        $folder = "submissions/project_{$project->id}";
        $disk = Storage::disk('local');
        $disk->put("$folder/report.pdf", $this->tinyPdf("Project Report - {$project->name}"));
        $disk->put("$folder/source.zip", $this->tinyZip($project->name));
        $disk->put("$folder/presentation.pptx", 'Demo presentation placeholder for '.$project->name);

        $late = mt_rand(1, 100) <= 15;
        ProjectSubmission::create([
            'project_id' => $project->id,
            'report_path' => "$folder/report.pdf",
            'report_text' => "Project Report {$project->name}. Introduction, system analysis, ER diagram, DFD, modules, testing, conclusion.",
            'source_zip_path' => "$folder/source.zip",
            'ppt_path' => "$folder/presentation.pptx",
            'screenshots' => [],
            'submitted_at' => $created->copy()->addDays(10),
            'is_late' => $late,
            'late_by_minutes' => $late ? mt_rand(30, 600) : null,
        ]);
    }

    private function seedAssignments($departments, $students, $facultiesByDept): void
    {
        $subjects = ['DBMS', 'Web Technology', 'Java Programming', 'Operating System', 'Data Structures',
            'Accounting', 'Marketing Management', 'Python', 'Computer Networks', 'Software Engineering'];
        $folderBase = 'assignment_submissions';
        $disk = Storage::disk('local');

        $byClass = $students->filter(fn ($s) => $s->user->status === User::STATUS_ACTIVE)
            ->groupBy(fn ($s) => $s->department_id.'-'.$s->semester);

        $a = 0;
        foreach ($departments as $dept) {
            $guides = $facultiesByDept[$dept->id];
            foreach (range(1, $dept->finalSemester() ?? 6) as $sem) {
                foreach ([1, 2] as $no) {
                    $subject = $subjects[$a % count($subjects)];
                    $due = $no === 1 ? $this->now->copy()->subDays(mt_rand(3, 20)) : $this->now->copy()->addDays(mt_rand(3, 15));
                    $marked = $a % 3 !== 0;

                    $assignment = Assignment::create([
                        'faculty_id' => $guides[$a % $guides->count()]->id,
                        'department_id' => $dept->id,
                        'semester' => (string) $sem,
                        'academic_year_id' => $this->yearId,
                        'subject' => $subject,
                        'assignment_no' => (string) $no,
                        'type' => Assignment::TYPES[$a % 3],
                        'max_marks' => $marked ? 10 : null,
                        'title' => "$subject — Unit $no Practical Questions",
                        'description' => "Answer all questions of unit $no. Upload a single PDF.",
                        'due_date' => $due->setTime(23, 59),
                    ]);
                    $a++;

                    foreach ($byClass[$dept->id.'-'.$sem] ?? [] as $student) {
                        if (mt_rand(1, 100) > ($no === 1 ? 80 : 40)) {
                            continue;
                        }

                        $path = "$folderBase/assignment_{$assignment->id}/student_{$student->id}.pdf";
                        $disk->put($path, $this->tinyPdf("{$subject} - {$student->roll_no}"));

                        $late = $no === 1 && mt_rand(1, 100) <= 10;
                        $submittedAt = $late ? $due->copy()->addMinutes(mt_rand(20, 300)) : $due->copy()->subDays(mt_rand(1, 3));
                        $checked = $no === 1 && mt_rand(1, 100) <= 65;

                        $submission = AssignmentSubmission::create([
                            'assignment_id' => $assignment->id,
                            'student_id' => $student->id,
                            'file_path' => $path,
                            'remarks' => mt_rand(0, 1) ? 'Submitted all questions.' : null,
                            'submitted_at' => $submittedAt,
                            'is_late' => $late,
                            'late_by_minutes' => $late ? (int) $due->diffInMinutes($submittedAt) : null,
                            'status' => $checked ? 'checked' : 'submitted',
                            'feedback' => $checked ? 'Good. Improve diagram labelling.' : null,
                            'marks' => $checked && $marked ? mt_rand(4, 10) : null,
                            'checked_at' => $checked ? $due->copy()->addDays(2) : null,
                        ]);

                        if (! $checked && $no === 1 && mt_rand(1, 100) <= 8) {
                            RevisionRequest::create([
                                'project_id' => null,
                                'assignment_submission_id' => $submission->id,
                                'stage' => RevisionRequest::STAGE_ASSIGNMENT,
                                'faculty_id' => $assignment->faculty_id,
                                'reason' => 'Wrong file uploaded — please upload the correct PDF.',
                                'due_at' => $this->now->copy()->addDays(3),
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function seedNotifications($projects): void
    {
        foreach ($projects->take(60) as $project) {
            $userId = $project->leader->user_id;
            Notification::create([
                'user_id' => $userId,
                'title' => 'Project status: '.$project->status,
                'message' => "Your project \"{$project->name}\" is now: {$project->status}.",
                'link' => null,
                'read_at' => mt_rand(0, 1) ? $this->now : null,
            ]);
        }
    }

    private function tinyPdf(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 18 Tf 72 720 Td ($text) Tj ET";
        $objs = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n$stream\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $i => $o) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n$o\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objs) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }

        return $pdf.'trailer << /Size '.(count($objs) + 1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    }

    private function tinyZip(string $name): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new \ZipArchive;
        if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true) {
            return '';
        }
        $zip->addFromString('README.txt', "Source code for $name");
        $zip->close();
        $data = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $data;
    }
}
