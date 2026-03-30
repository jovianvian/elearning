<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\SuspiciousActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL, Role::TEACHER), 403);

        $examRecap = Exam::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $classResults = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('school_classes', 'school_classes.id', '=', 'courses.class_id')
            ->select('school_classes.name as class_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'), DB::raw('COUNT(exam_attempts.id) as attempts'))
            ->groupBy('school_classes.name')
            ->orderBy('school_classes.name')
            ->get();

        $subjectResults = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('subjects', 'subjects.id', '=', 'courses.subject_id')
            ->select('subjects.name_id as subject_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'), DB::raw('COUNT(exam_attempts.id) as attempts'))
            ->groupBy('subjects.name_id')
            ->orderBy('subjects.name_id')
            ->get();

        $loginSummary = LoginLog::query()
            ->selectRaw('DATE(login_at) as login_date, COUNT(*) as total')
            ->whereNotNull('login_at')
            ->groupBy(DB::raw('DATE(login_at)'))
            ->orderByDesc('login_date')
            ->limit(7)
            ->get();

        $suspiciousSummary = SuspiciousActivityLog::query()
            ->select('activity_type', DB::raw('COUNT(*) as total'))
            ->groupBy('activity_type')
            ->orderByDesc('total')
            ->get();

        $examList = Exam::query()->latest()->limit(50)->get(['id', 'title']);

        return view('reports.index', compact(
            'examRecap',
            'classResults',
            'subjectResults',
            'loginSummary',
            'suspiciousSummary',
            'examList'
        ));
    }

    public function examScores(Exam $exam): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL, Role::TEACHER), 403);

        if ($user->hasRole(Role::TEACHER)) {
            $allowed = $exam->course()->whereHas('teachers', fn ($q) => $q->where('users.id', $user->id))->exists();
            abort_unless($allowed, 403);
        }

        $attempts = ExamAttempt::query()
            ->with('student')
            ->where('exam_id', $exam->id)
            ->latest()
            ->paginate(20);

        return view('reports.exam-scores', compact('exam', 'attempts'));
    }
}

