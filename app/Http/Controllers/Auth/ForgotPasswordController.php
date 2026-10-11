<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    private const TOKEN_TTL = 60;

    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        $neutral = 'If an account exists for that address, a password reset link has been sent to it.';

        if (! $user) {
            return back()->with('info', $neutral);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = route('password.reset', ['email' => $user->email, 'token' => $token]);

        try {
            Mail::to($user->email)->send(
                new ResetPasswordMail($resetUrl, $user->name, self::TOKEN_TTL)
            );
        } catch (\Throwable $e) {
            Log::warning('Password reset email failed: '.$e->getMessage());
        }

        $response = back()->with('info', $neutral);

        if (config('mail.default') === 'log') {
            $response->with('reset_url_preview', $resetUrl);
        }

        return $response;
    }

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

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => PasswordRules::rules(),
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

        $user->update(['password' => $data['password']]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please sign in with your new password.');
    }
}
