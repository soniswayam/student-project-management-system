<?php

namespace App\Http\Controllers;

use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->load(['student.department', 'faculty.department']);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'designation' => [$user->isFaculty() ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($user->isStudent() && $user->student) {
            $user->student->update([
                'phone' => $data['phone'] ?? null,
            ]);
        } elseif ($user->isFaculty() && $user->faculty) {
            $user->faculty->update([
                'phone' => $data['phone'] ?? null,
                'designation' => $data['designation'],
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => PasswordRules::rules(),
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        auth()->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password changed successfully.');
    }
}
