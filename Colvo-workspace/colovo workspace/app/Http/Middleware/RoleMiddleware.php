<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        if (in_array(auth()->user()->role, $roles)) {
            return $next($request);
        }

        // Redirect based on role if unauthorized
        $role = auth()->user()->role;
        if ($role === 'superadmin') return redirect()->route('superadmin.dashboard');
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        return redirect()->route('employee.dashboard')->with('error', 'Unauthorized access.');
    }
}
