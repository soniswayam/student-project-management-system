<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Faculty;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Forgot / reset password.
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('reset-password', [ForgotPasswordController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'update'])->name('password.update');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Role-aware dashboard redirect.
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications (shared by all roles).
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // Profile (shared by all roles: admin, super_admin, faculty, student).
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    /*
    |----------------------------------------------------------------------
    | Admin
    |----------------------------------------------------------------------
    */
    /*
    | Admin area — open to any STAFF role; each action is gated by a specific
    | permission (perm:<key>). Super admins bypass all permission checks.
    */
    Route::middleware('staff')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Students. Literal-segment routes come before {student} so words like
        // "export" / "import" / "create" are never captured as a model id.
        Route::get('students/export', [Admin\StudentController::class, 'export'])->middleware('perm:students.view')->name('students.export');
        Route::get('students/import', [Admin\StudentController::class, 'importForm'])->middleware('perm:students.create')->name('students.import.form');
        Route::post('students/import', [Admin\StudentController::class, 'import'])->middleware('perm:students.create')->name('students.import');
        Route::patch('students/promote-semester', [Admin\StudentController::class, 'promoteSemester'])->middleware('perm:students.edit')->name('students.promote-semester');
        Route::get('students', [Admin\StudentController::class, 'index'])->middleware('perm:students.view')->name('students.index');
        Route::get('students/create', [Admin\StudentController::class, 'create'])->middleware('perm:students.create')->name('students.create');
        Route::post('students', [Admin\StudentController::class, 'store'])->middleware('perm:students.create')->name('students.store');
        Route::get('students/{student}', [Admin\StudentController::class, 'show'])->middleware('perm:students.view')->name('students.show');
        Route::get('students/{student}/edit', [Admin\StudentController::class, 'edit'])->middleware('perm:students.edit')->name('students.edit');
        Route::put('students/{student}', [Admin\StudentController::class, 'update'])->middleware('perm:students.edit')->name('students.update');
        Route::patch('students/{student}/status', [Admin\StudentController::class, 'updateStatus'])->middleware('perm:students.edit')->name('students.status');
        Route::delete('students/{student}', [Admin\StudentController::class, 'destroy'])->middleware('perm:students.delete')->name('students.destroy');

        // Faculty.
        Route::get('faculties', [Admin\FacultyController::class, 'index'])->middleware('perm:faculty.view')->name('faculties.index');
        Route::get('faculties/create', [Admin\FacultyController::class, 'create'])->middleware('perm:faculty.manage')->name('faculties.create');
        Route::post('faculties', [Admin\FacultyController::class, 'store'])->middleware('perm:faculty.manage')->name('faculties.store');
        Route::get('faculties/{faculty}/edit', [Admin\FacultyController::class, 'edit'])->middleware('perm:faculty.manage')->name('faculties.edit');
        Route::put('faculties/{faculty}', [Admin\FacultyController::class, 'update'])->middleware('perm:faculty.manage')->name('faculties.update');
        Route::delete('faculties/{faculty}', [Admin\FacultyController::class, 'destroy'])->middleware('perm:faculty.manage')->name('faculties.destroy');

        // Departments.
        Route::get('departments', [Admin\DepartmentController::class, 'index'])->middleware('perm:departments.view')->name('departments.index');
        Route::post('departments', [Admin\DepartmentController::class, 'store'])->middleware('perm:departments.manage')->name('departments.store');
        Route::put('departments/{department}', [Admin\DepartmentController::class, 'update'])->middleware('perm:departments.manage')->name('departments.update');
        Route::delete('departments/{department}', [Admin\DepartmentController::class, 'destroy'])->middleware('perm:departments.manage')->name('departments.destroy');

        // Projects.
        Route::get('projects', [Admin\ProjectController::class, 'index'])->middleware('perm:projects.view')->name('projects.index');
        Route::get('projects/export', [Admin\ProjectController::class, 'export'])->middleware('perm:projects.export')->name('projects.export');
        Route::get('projects/{project}', [Admin\ProjectController::class, 'show'])->middleware('perm:projects.view')->name('projects.show');
        Route::get('projects/{project}/pdf', [Admin\ProjectController::class, 'downloadPdf'])->middleware('perm:projects.view')->name('projects.pdf');
        Route::get('projects/{project}/certificate', [Admin\ProjectController::class, 'certificate'])->middleware('perm:projects.view')->name('projects.certificate');
        Route::get('projects/{project}/synopsis', [Admin\ProjectController::class, 'downloadSynopsis'])->middleware('perm:projects.view')->name('projects.synopsis');
        Route::post('projects/{project}/assign', [Admin\ProjectController::class, 'assignFaculty'])->middleware('perm:projects.assign')->name('projects.assign');

        // Assignments (read-only view + exports).
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

        // Reports.
        Route::get('reports', [Admin\ReportController::class, 'index'])->middleware('perm:reports.view')->name('reports.index');
        Route::get('reports/export', [Admin\ReportController::class, 'export'])->middleware('perm:reports.view')->name('reports.export');

        // Access Control — combined Staff + Roles tabbed page (needs either permission).
        Route::get('access-control', [Admin\AccessControlController::class, 'index'])->name('access.index');

        // Manage admin/staff accounts.
        Route::resource('admins', Admin\AdminUserController::class)->except(['show'])->middleware('perm:admins.manage');

        // Manage roles & permissions.
        Route::resource('roles', Admin\RoleController::class)->except(['show'])->middleware('perm:roles.manage');

        // College settings (single record).
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->middleware('perm:settings.manage')->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->middleware('perm:settings.manage')->name('settings.update');
    });

    /*
        |----------------------------------------------------------------------
        | Faculty
        |----------------------------------------------------------------------
        */
    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('dashboard', [Faculty\DashboardController::class, 'index'])->name('dashboard');

        Route::get('projects', [Faculty\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [Faculty\ProjectController::class, 'show'])->name('projects.show');

        Route::post('projects/{project}/review-synopsis', [Faculty\ReviewController::class, 'reviewSynopsis'])->name('projects.reviewSynopsis');
        Route::post('projects/{project}/review-final', [Faculty\ReviewController::class, 'reviewFinal'])->name('projects.reviewFinal');

        // Assignments (homework) created by the faculty for a department.
        Route::get('assignments', [Faculty\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/create', [Faculty\AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('assignments', [Faculty\AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('assignments/{assignment}', [Faculty\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submissions/{submission}/check', [Faculty\AssignmentController::class, 'check'])->name('assignments.check');
        Route::delete('assignments/{assignment}', [Faculty\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Student
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:student', 'active'])->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');

        Route::get('synopsis/create', [Student\SynopsisController::class, 'create'])->name('synopsis.create');
        Route::post('synopsis', [Student\SynopsisController::class, 'store'])->name('synopsis.store');
        Route::get('synopsis/download', [Student\ProjectController::class, 'downloadSynopsis'])->name('synopsis.download');

        Route::get('project', [Student\ProjectController::class, 'show'])->name('project.show');
        Route::get('project/certificate', [Student\ProjectController::class, 'certificate'])->name('project.certificate');

        Route::get('submission/create', [Student\SubmissionController::class, 'create'])->name('submission.create');
        Route::post('submission', [Student\SubmissionController::class, 'store'])->name('submission.store');

        // Assignments posted for the student's department.
        Route::get('assignments', [Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/export', [Student\AssignmentController::class, 'exportExcel'])->name('assignments.export');
        Route::get('assignments/export-pdf', [Student\AssignmentController::class, 'exportPdf'])->name('assignments.exportPdf');
        Route::post('assignments/{assignment}/submit', [Student\AssignmentController::class, 'submit'])->name('assignments.submit');
    });
});
