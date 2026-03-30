<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SuspiciousActivityLog;
use Illuminate\View\View;

class SuspiciousActivityController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::TEACHER), 403);

        $query = SuspiciousActivityLog::query()
            ->with(['attempt.exam.course', 'user'])
            ->latest();

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('attempt.exam.course.teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        $logs = $query->paginate(25);

        return view('monitoring.suspicious-logs', compact('logs'));
    }
}

