<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Allow the request only if the user holds every listed permission.
     * Usage: ->middleware('perm:students.delete')
     */
    public function handle(Request $request, Closure $next, string ...$keys): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($keys as $key) {
            if (! $user->hasPermission($key)) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to perform that action.');
            }
        }

        return $next($request);
    }
}
