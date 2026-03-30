<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Role;
use Illuminate\View\View;

class LoginLogController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $logs = LoginLog::query()
            ->with('user')
            ->latest('login_at')
            ->paginate(25);

        return view('monitoring.login-logs', compact('logs'));
    }
}

