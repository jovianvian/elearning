<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && $user->hasRole(Role::STUDENT)) {
            if (! $request->routeIs('password.force.form', 'password.force.update', 'logout')) {
                return redirect()->route('password.force.form');
            }
        }

        return $next($request);
    }
}
