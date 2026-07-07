<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMiddleware
{
    /**
     * Backstop that keeps pending / blocked accounts out of role dashboards
     * even if their status changes mid-session (e.g. an admin blocks them
     * while they are logged in). Active/legacy-null accounts pass through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            $message = $user->isPending()
                ? 'Your account is waiting for admin approval.'
                : 'Your account has been blocked. Please contact the administrator.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
