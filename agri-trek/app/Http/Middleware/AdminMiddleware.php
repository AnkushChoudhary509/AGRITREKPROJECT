<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string $role = 'admin')
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account is deactivated.');
        }

        // 'admin' middleware allows admin + expert
        // 'admin:admin' allows only admin
        if ($role === 'admin' && !$user->isExpert()) {
            abort(403, 'Expert or Admin privileges required.');
        }

        if ($role === 'strict_admin' && !$user->isAdmin()) {
            abort(403, 'Administrator privileges required.');
        }

        return $next($request);
    }
}
