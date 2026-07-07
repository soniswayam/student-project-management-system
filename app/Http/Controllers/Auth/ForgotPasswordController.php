<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /** How long a reset token stays valid (minutes). */
    private const TOKEN_TTL = 60;

    /** Show the "forgot password" form. */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Verify the email and issue a one-time reset token.
     *
     * On a real deployment this would email a reset link. Because this project
     * runs on localhost with MAIL_MAILER=log, we forward the user straight to
     * the reset form with a valid token so the flow works end-to-end offline.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'We could not find an account with that email address.'])
                ->onlyInput('email');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        return redirect()
            ->route('password.reset', ['email' => $user->email, 'token' => $token])
            ->with('info', 'Identity confirmed. Please set a new password below.');
    }

    /** Show the reset form (email + token carried in the query string). */
    public function edit(Request $request)
    {
        if (! $request->query('token') || ! $request->query('email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $request->query('token'),
        ]);
    }

    /** Validate the token and store the new password. */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $row || ! Hash::check($data['token'], $row->token)) {
            return back()->withErrors(['email' => 'This reset link is invalid. Please request a new one.']);
        }

        if (Carbon::parse($row->created_at)->addMinutes(self::TOKEN_TTL)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => 'This reset link has expired. Please request a new one.']);
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Account not found.']);
        }

        // The User model casts 'password' => 'hashed', so this is stored hashed.
        $user->update(['password' => $data['password']]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please sign in with your new password.');
    }
}
