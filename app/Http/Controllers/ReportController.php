<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\SuspiciousActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL, Role::TEACHER), 403);

        $managedExamIds = null;
        if ($user->hasRole(Role::TEACHER)) {
            $managedExamIds = Exam::query()
                ->whereHas('course.teachers', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id');
        }

        $examRecapQuery = Exam::query();
        if ($managedExamIds !== null) {
            $examRecapQuery->whereIn('id', $managedExamIds);
        }

        $examRecap = $examRecapQuery
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $classResultsQuery = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('school_classes', 'school_classes.id', '=', 'courses.class_id')
            ->select('school_classes.name as class_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'), DB::raw('COUNT(exam_attempts.id) as attempts'));
        if ($managedExamIds !== null) {
            $classResultsQuery->whereIn('exam_attempts.exam_id', $managedExamIds);
        }

        $classResults = $classResultsQuery
            ->groupBy('school_classes.name')
            ->orderBy('school_classes.name')
            ->get();

        $subjectResultsQuery = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('subjects', 'subjects.id', '=', 'courses.subject_id')
            ->select('subjects.name_id as subject_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'), DB::raw('COUNT(exam_attempts.id) as attempts'));
        if ($managedExamIds !== null) {
            $subjectResultsQuery->whereIn('exam_attempts.exam_id', $managedExamIds);
        }

        $subjectResults = $subjectResultsQuery
            ->groupBy('subjects.name_id')
            ->orderBy('subjects.name_id')
            ->get();

        $loginSummaryQuery = LoginLog::query()
            ->selectRaw('DATE(login_at) as login_date, COUNT(*) as total')
            ->whereNotNull('login_at');
        if ($user->hasRole(Role::TEACHER)) {
            $loginSummaryQuery->where('user_id', $user->id);
        }

        $loginSummary = $loginSummaryQuery
            ->groupBy(DB::raw('DATE(login_at)'))
            ->orderByDesc('login_date')
            ->limit(7)
            ->get();

        $suspiciousSummaryQuery = SuspiciousActivityLog::query();
        if ($managedExamIds !== null) {
            $suspiciousSummaryQuery->whereHas('attempt', fn ($q) => $q->whereIn('exam_id', $managedExamIds));
        }

        $suspiciousSummary = $suspiciousSummaryQuery
            ->select('activity_type', DB::raw('COUNT(*) as total'))
            ->groupBy('activity_type')
            ->orderByDesc('total')
            ->get();

        $examListQuery = Exam::query();
        if ($managedExamIds !== null) {
            $examListQuery->whereIn('id', $managedExamIds);
        }
        $examList = $examListQuery->latest()->limit(50)->get(['id', 'title']);

        $attemptsBaseQuery = ExamAttempt::query();
        if ($managedExamIds !== null) {
            $attemptsBaseQuery->whereIn('exam_id', $managedExamIds);
        }

        $totalAttempts = (clone $attemptsBaseQuery)->count();
        $averageFinalScore = (float) ((clone $attemptsBaseQuery)->avg('final_score') ?? 0);

        $highSeverityQuery = SuspiciousActivityLog::query()->where('severity', 'high');
        if ($managedExamIds !== null) {
            $highSeverityQuery->whereHas('attempt', fn ($q) => $q->whereIn('exam_id', $managedExamIds));
        }
        $highSeverityCount = $highSeverityQuery->count();

        $todayLoginQuery = LoginLog::query()->whereDate('login_at', Carbon::today());
        if ($user->hasRole(Role::TEACHER)) {
            $todayLoginQuery->where('user_id', $user->id);
        }
        $todayLoginCount = $todayLoginQuery->count();

        return view('reports.index', compact(
            'examRecap',
            'classResults',
            'subjectResults',
            'loginSummary',
            'suspiciousSummary',
            'examList',
            'totalAttempts',
            'averageFinalScore',
            'highSeverityCount',
            'todayLoginCount'
        ));
    }

    public function examScores(Request $request, Exam $exam): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL, Role::TEACHER), 403);

        if ($user->hasRole(Role::TEACHER)) {
            $allowed = $exam->course()->whereHas('teachers', fn ($q) => $q->where('users.id', $user->id))->exists();
            abort_unless($allowed, 403);
        }

        $query = ExamAttempt::query()
            ->with('student')
            ->where('exam_id', $exam->id);

        if ($q = trim((string) $request->string('q'))) {
            $query->whereHas('student', function ($sq) use ($q): void {
                $sq->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $attempts = $query->latest()->paginate(20)->withQueryString();

        return view('reports.exam-scores', compact('exam', 'attempts'));
    }
}
