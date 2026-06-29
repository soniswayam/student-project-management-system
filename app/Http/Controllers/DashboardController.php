<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /** Route the logged-in user to the right dashboard for their role. */
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'faculty' => redirect()->route('faculty.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
