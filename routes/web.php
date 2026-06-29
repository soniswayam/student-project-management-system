<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Faculty;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
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

    /*
    |----------------------------------------------------------------------
    | Admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('departments', Admin\DepartmentController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('students', Admin\StudentController::class)
            ->except(['show']);

        Route::resource('faculties', Admin\FacultyController::class)
            ->except(['show']);

        Route::get('projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [Admin\ProjectController::class, 'show'])->name('projects.show');
        Route::post('projects/{project}/assign', [Admin\ProjectController::class, 'assignFaculty'])->name('projects.assign');

        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
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
    });

    /*
    |----------------------------------------------------------------------
    | Student
    |----------------------------------------------------------------------
    */
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');

        Route::get('synopsis/create', [Student\SynopsisController::class, 'create'])->name('synopsis.create');
        Route::post('synopsis', [Student\SynopsisController::class, 'store'])->name('synopsis.store');

        Route::get('project', [Student\ProjectController::class, 'show'])->name('project.show');

        Route::get('submission/create', [Student\SubmissionController::class, 'create'])->name('submission.create');
        Route::post('submission', [Student\SubmissionController::class, 'store'])->name('submission.store');
    });
});
