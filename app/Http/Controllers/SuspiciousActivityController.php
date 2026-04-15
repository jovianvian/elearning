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
            ->orderByDesc('last_detected_at')
            ->latest('id');

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

        if ($examId = $request->integer('exam_id')) {
            $query->whereHas('attempt', fn ($aq) => $aq->where('exam_id', $examId));
        }

        if ($studentId = $request->integer('student_id')) {
            $query->where('user_id', $studentId);
        }

        if ($from = $request->date('from_date')) {
            $query->where(function ($w) use ($from): void {
                $w->whereDate('last_detected_at', '>=', $from->toDateString())
                    ->orWhere(function ($fallback) use ($from): void {
                        $fallback->whereNull('last_detected_at')
                            ->whereDate('created_at', '>=', $from->toDateString());
                    });
            });
        }

        if ($to = $request->date('to_date')) {
            $query->where(function ($w) use ($to): void {
                $w->whereDate('last_detected_at', '<=', $to->toDateString())
                    ->orWhere(function ($fallback) use ($to): void {
                        $fallback->whereNull('last_detected_at')
                            ->whereDate('created_at', '<=', $to->toDateString());
                    });
            });
        }

        $minEvents = max(1, (int) $request->integer('min_events', 1));
        if ($minEvents > 1) {
            $query->whereRaw('COALESCE(event_count, 1) >= ?', [$minEvents]);
        }

        if ($request->boolean('multi_tab_only')) {
            $query->whereIn('activity_type', ['duplicate_session', 'multiple_tabs_detected']);
        }

        $logs = $query->paginate(25)->withQueryString();

        $filterQuery = SuspiciousActivityLog::query();
        if ($user->hasRole(Role::TEACHER)) {
            $filterQuery->whereHas('attempt.exam.course.teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        $activityTypes = (clone $filterQuery)
            ->select('activity_type')
            ->whereNotNull('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        $exams = (clone $filterQuery)
            ->join('exam_attempts', 'exam_attempts.id', '=', 'suspicious_activity_logs.exam_attempt_id')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->select('exams.id', 'exams.title')
            ->distinct()
            ->orderBy('exams.title')
            ->get();

        $students = (clone $filterQuery)
            ->join('users', 'users.id', '=', 'suspicious_activity_logs.user_id')
            ->select('users.id', 'users.full_name')
            ->distinct()
            ->orderBy('users.full_name')
            ->get();

        return view('monitoring.suspicious-logs', compact('logs', 'activityTypes', 'exams', 'students'));
    }
}
