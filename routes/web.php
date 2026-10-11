<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Faculty;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student;
use App\Http\Controllers\SubmissionFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password', [ForgotPasswordController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('active')->group(function () {
        Route::get('files/project-submissions/{submission}/screenshot/{index}', [SubmissionFileController::class, 'screenshot'])
            ->whereNumber('index')
            ->name('files.project-screenshot');
        Route::get('files/project-submissions/{submission}/{type}', [SubmissionFileController::class, 'project'])
            ->whereIn('type', ['report', 'source_zip', 'ppt'])
            ->name('files.project-submission');
        Route::get('files/assignment-submissions/{submission}', [SubmissionFileController::class, 'assignment'])
            ->name('files.assignment-submission');
    });

    Route::middleware(['staff', 'active'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('students/export', [Admin\StudentController::class, 'export'])->middleware('perm:students.view')->name('students.export');
        Route::get('students/import', [Admin\StudentController::class, 'importForm'])->middleware('perm:students.create')->name('students.import.form');
        Route::post('students/import', [Admin\StudentController::class, 'import'])->middleware('perm:students.create')->name('students.import');
        Route::patch('students/promote-semester', [Admin\StudentController::class, 'promoteSemester'])->middleware('perm:students.edit')->name('students.promote-semester');
        Route::get('students/semester-review', [Admin\StudentController::class, 'semesterReview'])->middleware('perm:students.edit')->name('students.semester-review');
        Route::patch('students/semester-review', [Admin\StudentController::class, 'fixSemesters'])->middleware('perm:students.edit')->name('students.fix-semesters');
        Route::get('students', [Admin\StudentController::class, 'index'])->middleware('perm:students.view')->name('students.index');
        Route::get('students/create', [Admin\StudentController::class, 'create'])->middleware('perm:students.create')->name('students.create');
        Route::post('students', [Admin\StudentController::class, 'store'])->middleware('perm:students.create')->name('students.store');
        Route::get('students/{student}', [Admin\StudentController::class, 'show'])->middleware('perm:students.view')->name('students.show');
        Route::get('students/{student}/edit', [Admin\StudentController::class, 'edit'])->middleware('perm:students.edit')->name('students.edit');
        Route::put('students/{student}', [Admin\StudentController::class, 'update'])->middleware('perm:students.edit')->name('students.update');
        Route::patch('students/{student}/status', [Admin\StudentController::class, 'updateStatus'])->middleware('perm:students.edit')->name('students.status');
        Route::delete('students/{student}', [Admin\StudentController::class, 'destroy'])->middleware('perm:students.delete')->name('students.destroy');

        Route::get('alumni', [Admin\AlumniController::class, 'index'])->middleware('perm:alumni.view')->name('alumni.index');
        Route::get('alumni/export', [Admin\AlumniController::class, 'export'])->middleware('perm:alumni.view')->name('alumni.export');
        Route::post('alumni', [Admin\AlumniController::class, 'store'])->middleware('perm:alumni.manage')->name('alumni.store');
        Route::patch('alumni/{student}/restore', [Admin\AlumniController::class, 'restore'])->middleware('perm:alumni.manage')->name('alumni.restore');

        Route::get('faculties', [Admin\FacultyController::class, 'index'])->middleware('perm:faculty.view')->name('faculties.index');
        Route::get('faculties/create', [Admin\FacultyController::class, 'create'])->middleware('perm:faculty.manage')->name('faculties.create');
        Route::post('faculties', [Admin\FacultyController::class, 'store'])->middleware('perm:faculty.manage')->name('faculties.store');
        Route::get('faculties/{faculty}/edit', [Admin\FacultyController::class, 'edit'])->middleware('perm:faculty.manage')->name('faculties.edit');
        Route::put('faculties/{faculty}', [Admin\FacultyController::class, 'update'])->middleware('perm:faculty.manage')->name('faculties.update');
        Route::delete('faculties/{faculty}', [Admin\FacultyController::class, 'destroy'])->middleware('perm:faculty.manage')->name('faculties.destroy');

        Route::get('departments', [Admin\DepartmentController::class, 'index'])->middleware('perm:departments.view')->name('departments.index');
        Route::post('departments', [Admin\DepartmentController::class, 'store'])->middleware('perm:departments.manage')->name('departments.store');
        Route::put('departments/{department}', [Admin\DepartmentController::class, 'update'])->middleware('perm:departments.manage')->name('departments.update');
        Route::delete('departments/{department}', [Admin\DepartmentController::class, 'destroy'])->middleware('perm:departments.manage')->name('departments.destroy');

        Route::get('projects', [Admin\ProjectController::class, 'index'])->middleware('perm:projects.view')->name('projects.index');
        Route::get('projects/export', [Admin\ProjectController::class, 'export'])->middleware('perm:projects.export')->name('projects.export');
        Route::post('projects/bulk-assign', [Admin\ProjectController::class, 'bulkAssign'])->middleware('perm:projects.assign')->name('projects.bulkAssign');
        Route::get('projects/{project}', [Admin\ProjectController::class, 'show'])->middleware('perm:projects.view')->name('projects.show');
        Route::get('projects/{project}/pdf', [Admin\ProjectController::class, 'downloadPdf'])->middleware('perm:projects.view')->name('projects.pdf');
        Route::get('projects/{project}/certificate', [Admin\ProjectController::class, 'certificate'])->middleware('perm:projects.view')->name('projects.certificate');
        Route::get('projects/{project}/synopsis', [Admin\ProjectController::class, 'downloadSynopsis'])->middleware('perm:projects.view')->name('projects.synopsis');
        Route::post('projects/{project}/assign', [Admin\ProjectController::class, 'assignFaculty'])->middleware('perm:projects.assign')->name('projects.assign');
        Route::patch('projects/{project}/deadline', [Admin\ProjectController::class, 'updateDeadline'])->middleware('perm:projects.assign')->name('projects.deadline');

        Route::get('assignments', [Admin\AssignmentController::class, 'index'])->middleware('perm:assignments.view')->name('assignments.index');
        Route::get('assignments/export', [Admin\AssignmentController::class, 'export'])->middleware('perm:assignments.view')->name('assignments.export');
        Route::get('assignments/export-pdf', [Admin\AssignmentController::class, 'exportPdf'])->middleware('perm:assignments.view')->name('assignments.exportPdf');
        Route::get('assignments/distribution', [Admin\AssignmentController::class, 'distribution'])->middleware('perm:assignments.view')->name('assignments.distribution');
        Route::get('assignments/distribution/excel', [Admin\AssignmentController::class, 'distributionExcel'])->middleware('perm:assignments.view')->name('assignments.distribution.excel');
        Route::get('assignments/distribution/pdf', [Admin\AssignmentController::class, 'distributionPdf'])->middleware('perm:assignments.view')->name('assignments.distribution.pdf');
        Route::get('assignments/create', [Admin\AssignmentController::class, 'create'])->middleware('perm:assignments.manage')->name('assignments.create');
        Route::post('assignments', [Admin\AssignmentController::class, 'store'])->middleware('perm:assignments.manage')->name('assignments.store');
        Route::get('assignments/{assignment}', [Admin\AssignmentController::class, 'show'])->middleware('perm:assignments.view')->name('assignments.show');
        Route::get('assignments/{assignment}/edit', [Admin\AssignmentController::class, 'edit'])->middleware('perm:assignments.manage')->name('assignments.edit');
        Route::put('assignments/{assignment}', [Admin\AssignmentController::class, 'update'])->middleware('perm:assignments.manage')->name('assignments.update');
        Route::delete('assignments/{assignment}', [Admin\AssignmentController::class, 'destroy'])->middleware('perm:assignments.manage')->name('assignments.destroy');

        Route::get('reports', [Admin\ReportController::class, 'index'])->middleware('perm:reports.view')->name('reports.index');
        Route::get('reports/export', [Admin\ReportController::class, 'export'])->middleware('perm:reports.view')->name('reports.export');

        Route::get('similarity', [Admin\SimilarityController::class, 'index'])->middleware('perm:similarity.view')->name('similarity.index');
        Route::get('similarity/export', [Admin\SimilarityController::class, 'export'])->middleware('perm:similarity.view')->name('similarity.export');
        Route::get('similarity/{similarity}', [Admin\SimilarityController::class, 'show'])->middleware('perm:similarity.view')->name('similarity.show');

        Route::get('workload', [Admin\WorkloadController::class, 'index'])->middleware('perm:workload.view')->name('workload.index');
        Route::get('workload/export', [Admin\WorkloadController::class, 'export'])->middleware('perm:workload.view')->name('workload.export');
        Route::patch('workload/{faculty}/capacity', [Admin\WorkloadController::class, 'updateCapacity'])->middleware('perm:faculty.manage')->name('workload.capacity');

        Route::get('academic-years', [Admin\AcademicYearController::class, 'index'])->middleware('perm:academic_years.manage')->name('academic-years.index');
        Route::post('academic-years', [Admin\AcademicYearController::class, 'store'])->middleware('perm:academic_years.manage')->name('academic-years.store');
        Route::put('academic-years/{academicYear}', [Admin\AcademicYearController::class, 'update'])->middleware('perm:academic_years.manage')->name('academic-years.update');
        Route::patch('academic-years/{academicYear}/current', [Admin\AcademicYearController::class, 'makeCurrent'])->middleware('perm:academic_years.manage')->name('academic-years.current');
        Route::patch('academic-years/{academicYear}/archive', [Admin\AcademicYearController::class, 'archive'])->middleware('perm:academic_years.manage')->name('academic-years.archive');
        Route::patch('academic-years/{academicYear}/unarchive', [Admin\AcademicYearController::class, 'unarchive'])->middleware('perm:academic_years.manage')->name('academic-years.unarchive');
        Route::delete('academic-years/{academicYear}', [Admin\AcademicYearController::class, 'destroy'])->middleware('perm:academic_years.manage')->name('academic-years.destroy');

        Route::get('archive', [Admin\ArchiveController::class, 'index'])->middleware('perm:archive.view')->name('archive.index');
        Route::get('archive/export', [Admin\ArchiveController::class, 'export'])->middleware('perm:archive.view')->name('archive.export');
        Route::get('archive/{project}', [Admin\ArchiveController::class, 'show'])->middleware('perm:archive.view')->name('archive.show');

        Route::get('approvals', [Admin\ApprovalController::class, 'index'])->middleware('perm:approvals.manage')->name('approvals.index');
        Route::post('approvals/bulk-approve', [Admin\ApprovalController::class, 'bulkApprove'])->middleware('perm:approvals.manage')->name('approvals.bulk');
        Route::patch('approvals/{user}/approve', [Admin\ApprovalController::class, 'approve'])->middleware('perm:approvals.manage')->name('approvals.approve');
        Route::patch('approvals/{user}/reject', [Admin\ApprovalController::class, 'reject'])->middleware('perm:approvals.manage')->name('approvals.reject');

        Route::get('access-control', [Admin\AccessControlController::class, 'index'])->name('access.index');

        Route::resource('admins', Admin\AdminUserController::class)->except(['show'])->middleware('perm:admins.manage');

        Route::resource('roles', Admin\RoleController::class)->except(['show'])->middleware('perm:roles.manage');

        Route::get('settings', [Admin\SettingsController::class, 'edit'])->middleware('perm:settings.manage')->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->middleware('perm:settings.manage')->name('settings.update');
        Route::put('settings/levels', [Admin\SettingsController::class, 'updateLevels'])->middleware('perm:settings.manage')->name('settings.levels');
    });

    Route::middleware(['role:faculty', 'active'])->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('dashboard', [Faculty\DashboardController::class, 'index'])->name('dashboard');

        Route::get('projects', [Faculty\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [Faculty\ProjectController::class, 'show'])->name('projects.show');

        Route::post('projects/{project}/review-synopsis', [Faculty\ReviewController::class, 'reviewSynopsis'])->name('projects.reviewSynopsis');
        Route::post('projects/{project}/review-final', [Faculty\ReviewController::class, 'reviewFinal'])->name('projects.reviewFinal');

        Route::post('projects/{project}/viva', [Faculty\VivaController::class, 'store'])->name('projects.viva.store');
        Route::patch('projects/{project}/viva/status', [Faculty\VivaController::class, 'updateStatus'])->name('projects.viva.status');
        Route::delete('projects/{project}/viva', [Faculty\VivaController::class, 'destroy'])->name('projects.viva.destroy');

        Route::post('projects/{project}/request-reupload', [Faculty\RevisionRequestController::class, 'requestFinal'])->name('projects.requestReupload');
        Route::post('assignments/{assignment}/submissions/{submission}/request-reupload', [Faculty\RevisionRequestController::class, 'requestAssignment'])->name('assignments.requestReupload');
        Route::patch('revision-requests/{revisionRequest}/cancel', [Faculty\RevisionRequestController::class, 'cancel'])->name('revisionRequests.cancel');

        Route::get('assignments', [Faculty\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/create', [Faculty\AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('assignments', [Faculty\AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('assignments/{assignment}', [Faculty\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submissions/{submission}/check', [Faculty\AssignmentController::class, 'check'])->name('assignments.check');
        Route::delete('assignments/{assignment}', [Faculty\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

    Route::middleware(['role:student', 'active'])->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');

        Route::get('synopsis/download', [Student\ProjectController::class, 'downloadSynopsis'])->name('synopsis.download');
        Route::get('project', [Student\ProjectController::class, 'show'])->name('project.show');
        Route::get('project/certificate', [Student\ProjectController::class, 'certificate'])->name('project.certificate');
        Route::get('assignments', [Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/export', [Student\AssignmentController::class, 'exportExcel'])->name('assignments.export');
        Route::get('assignments/export-pdf', [Student\AssignmentController::class, 'exportPdf'])->name('assignments.exportPdf');

        Route::middleware('not-alumni')->group(function () {
            Route::get('synopsis/create', [Student\SynopsisController::class, 'create'])->name('synopsis.create');
            Route::post('synopsis', [Student\SynopsisController::class, 'store'])->name('synopsis.store');
            Route::get('synopsis/edit', [Student\SynopsisController::class, 'edit'])->name('synopsis.edit');
            Route::put('synopsis', [Student\SynopsisController::class, 'update'])->name('synopsis.update');

            Route::get('submission/create', [Student\SubmissionController::class, 'create'])->name('submission.create');
            Route::post('submission', [Student\SubmissionController::class, 'store'])->name('submission.store');

            Route::post('assignments/{assignment}/submit', [Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        });
    });
});
