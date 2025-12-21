<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (e.g., 'Pimpinan', 'Admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user's role is in the allowed roles
        if (!in_array($user->role, $roles)) {
            // If AJAX/Inertia request, return 403
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                abort(403, 'Anda tidak memiliki akses ke fitur ini.');
            }

            // Otherwise redirect with error message
            return redirect()
                ->back()
                ->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
