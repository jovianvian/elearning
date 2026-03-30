<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $logs = ActivityLog::query()
            ->latest()
            ->paginate(25);

        return view('monitoring.audit-logs', compact('logs'));
    }
}

