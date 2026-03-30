<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Akses ditolak.');
        }

        // Super Admin can access all role-protected views.
        if ($user->role->code === Role::SUPER_ADMIN) {
            return $next($request);
        }

        $aliases = [
            'guru' => Role::TEACHER,
            'teacher' => Role::TEACHER,
            'siswa' => Role::STUDENT,
            'student' => Role::STUDENT,
            'admin' => Role::ADMIN,
            'super_admin' => Role::SUPER_ADMIN,
            'super-admin' => Role::SUPER_ADMIN,
            'principal' => Role::PRINCIPAL,
        ];

        $normalized = array_map(static fn (string $role): string => $aliases[$role] ?? $role, $roles);

        if ($normalized !== [] && ! in_array($user->role->code, $normalized, true)) {
            abort(403, 'Kamu tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
