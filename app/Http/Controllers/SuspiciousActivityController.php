<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SuspiciousActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuspiciousActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::TEACHER), 403);

        $query = SuspiciousActivityLog::query()
            ->with(['attempt.exam.course', 'user'])
            ->latest();

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('attempt.exam.course.teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('activity_type', 'like', "%{$q}%")
                    ->orWhere('note', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('full_name', 'like', "%{$q}%"));
            });
        }

        if ($severity = $request->string('severity')->toString()) {
            if (in_array($severity, ['low', 'medium', 'high'], true)) {
                $query->where('severity', $severity);
            }
        }

        if ($activityType = trim((string) $request->string('activity_type'))) {
            $query->where('activity_type', $activityType);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $activityTypes = SuspiciousActivityLog::query()
            ->select('activity_type')
            ->whereNotNull('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        return view('monitoring.suspicious-logs', compact('logs', 'activityTypes'));
    }
}
