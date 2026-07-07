<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Allow the request only if the authenticated user has a staff role
     * (i.e. may access the admin area). Otherwise send them to their own
     * dashboard instead of dead-ending on a 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isStaff()) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to access that page.');
        }

        return $next($request);
    }
}
