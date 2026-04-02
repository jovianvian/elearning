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

        $currentRole = $this->normalizeRoleCode($user->role->code);

        // Super Admin can access all role-protected views.
        if ($currentRole === Role::SUPER_ADMIN) {
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
            'superadmin' => Role::SUPER_ADMIN,
            'principal' => Role::PRINCIPAL,
        ];

        $normalized = array_map(function (string $role) use ($aliases): string {
            $key = strtolower(str_replace('-', '_', trim($role)));
            return $aliases[$key] ?? $key;
        }, $roles);

        if ($normalized !== [] && ! in_array($currentRole, $normalized, true)) {
            abort(403, 'Kamu tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }

    private function normalizeRoleCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $normalized = strtolower(str_replace('-', '_', trim($code)));
        if ($normalized === 'superadmin') {
            return Role::SUPER_ADMIN;
        }

        return $normalized;
    }
}
