<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $query = LoginLog::query()->with('user');

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('ip_address', 'like', "%{$q}%")
                    ->orWhere('session_id', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('full_name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('is_success')) {
            $query->where('is_success', (bool) $request->boolean('is_success'));
        }

        $logs = $query->latest('login_at')->paginate(25)->withQueryString();

        return view('monitoring.login-logs', compact('logs'));
    }
}
