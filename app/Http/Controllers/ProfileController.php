<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /** Show the current user's profile (works for every role). */
    public function edit()
    {
        $user = auth()->user()->load(['student.department', 'faculty.department']);

        return view('profile.edit', compact('user'));
    }

    /** Update the current user's account details + role-specific fields. */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'semester' => [$user->isStudent() ? 'required' : 'nullable', 'string', 'max:20'],
            'designation' => [$user->isFaculty() ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Role-specific profile rows. Identity fields (roll_no, department) stay
        // admin-managed and are intentionally not editable here.
        if ($user->isStudent() && $user->student) {
            $user->student->update([
                'phone' => $data['phone'] ?? null,
                'semester' => $data['semester'],
            ]);
        } elseif ($user->isFaculty() && $user->faculty) {
            $user->faculty->update([
                'phone' => $data['phone'] ?? null,
                'designation' => $data['designation'],
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /** Change the current user's password (all roles). */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        // The User model casts 'password' => 'hashed', so this is stored hashed.
        auth()->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password changed successfully.');
    }
}
