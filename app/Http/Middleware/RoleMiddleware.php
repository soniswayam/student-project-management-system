<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Allow the request only if the authenticated user has one of the given roles.
     * Usage in routes: ->middleware('role:admin') or 'role:admin,faculty'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            // Send the user to their own dashboard instead of dead-ending on a 403.
            return redirect()
                ->route('dashboard')
                ->with('error', 'You are not authorized to access that page.');
        }

        return $next($request);
    }
}
