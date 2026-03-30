<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\LoginLog;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SuspiciousActivityLog;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $role = auth()->user()?->role?->code;

        return match ($role) {
            Role::SUPER_ADMIN => redirect()->route('dashboard.super-admin'),
            Role::ADMIN => redirect()->route('dashboard.admin'),
            Role::PRINCIPAL => redirect()->route('dashboard.principal'),
            Role::TEACHER => redirect()->route('dashboard.teacher'),
            Role::STUDENT => redirect()->route('dashboard.student'),
            default => redirect()->route('login'),
        };
    }

    public function superAdmin(): View
    {
        $settings = AppSetting::query()->with(['activeAcademicYear', 'activeSemester'])->first();

        return view('dashboard.super-admin', [
            'stats' => [
                'users' => User::count(),
                'courses' => Course::count(),
                'exams' => Exam::count(),
                'suspicious' => SuspiciousActivityLog::count(),
                'deleted_items' =>
                    User::onlyTrashed()->count() +
                    SchoolClass::onlyTrashed()->count() +
                    Subject::onlyTrashed()->count() +
                    Course::onlyTrashed()->count() +
                    QuestionBank::onlyTrashed()->count() +
                    Question::onlyTrashed()->count() +
                    Exam::onlyTrashed()->count(),
                'audit_logs' => ActivityLog::count(),
                'login_logs' => LoginLog::count(),
                'active_sessions' => LoginLog::whereNotNull('login_at')->whereNull('logout_at')->count(),
                'app_name' => $settings?->app_name ?? '-',
                'school_name' => $settings?->school_name ?? '-',
                'active_year' => $settings?->activeAcademicYear?->name ?? '-',
                'active_semester' => $settings?->activeSemester?->name ?? '-',
            ],
        ]);
    }

    public function admin(): View
    {
        $usersBase = User::query();

        return view('dashboard.admin', [
            'stats' => [
                'users' => $usersBase->count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'blocked' => User::whereNotNull('blocked_at')->count(),
                'classes' => SchoolClass::count(),
                'courses' => Course::count(),
                'active_exams' => Exam::query()
                    ->whereNotNull('start_at')
                    ->whereNotNull('end_at')
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
                    ->count(),
                'today_logins' => LoginLog::whereDate('login_at', now()->toDateString())->count(),
                'today_attempts' => ExamAttempt::whereDate('started_at', now()->toDateString())->count(),
            ],
        ]);
    }

    public function principal(): View
    {
        $avgPerClass = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('school_classes', 'school_classes.id', '=', 'courses.class_id')
            ->select('school_classes.name as class_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'))
            ->groupBy('school_classes.name')
            ->orderByDesc('avg_score')
            ->limit(5)
            ->get();

        $avgPerSubject = ExamAttempt::query()
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('subjects', 'subjects.id', '=', 'courses.subject_id')
            ->select('subjects.name_id as subject_name', DB::raw('AVG(exam_attempts.final_score) as avg_score'))
            ->groupBy('subjects.name_id')
            ->orderByDesc('avg_score')
            ->limit(5)
            ->get();

        return view('dashboard.principal', [
            'stats' => [
                'students' => User::whereHas('role', fn ($q) => $q->where('code', Role::STUDENT))->count(),
                'teachers' => User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->count(),
                'courses' => Course::count(),
                'attempts' => ExamAttempt::count(),
                'avg_score' => round((float) ExamAttempt::avg('final_score'), 2),
                'active_exams' => Exam::query()
                    ->where('is_published', true)
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
                    ->count(),
                'suspicious_summary' => SuspiciousActivityLog::count(),
                'class_performance' => $avgPerClass,
                'subject_performance' => $avgPerSubject,
            ],
        ]);
    }

    public function teacher(): View
    {
        $user = auth()->user();

        return view('dashboard.teacher', [
            'stats' => [
                'courses' => $user->assignedCourses()->count(),
                'scheduled_exams' => Exam::whereIn('course_id', $user->assignedCourses()->pluck('courses.id'))
                    ->whereIn('status', ['scheduled', 'active'])
                    ->count(),
                'needs_grading' => ExamAttempt::whereHas('exam.course.teachers', fn ($q) => $q->where('users.id', $user->id))
                    ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED])
                    ->count(),
                'question_banks' => QuestionBank::whereIn('subject_id', $user->taughtSubjects()->pluck('subject_id'))->count(),
                'total_attempts' => ExamAttempt::whereHas('exam.course.teachers', fn ($q) => $q->where('users.id', $user->id))->count(),
            ],
        ]);
    }

    public function student(): View
    {
        $user = auth()->user();

        $notifications = UserNotification::query()
            ->with('notification')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.student', [
            'stats' => [
                'courses' => $user->studentCourses()->count(),
                'pending_exams' => Exam::whereIn('course_id', $user->studentCourses()->pluck('courses.id'))
                    ->whereIn('status', ['scheduled', 'active'])
                    ->count(),
                'published_results' => ExamAttempt::where('student_id', $user->id)->where('is_published', true)->count(),
                'notifications' => $notifications,
                'profile' => [
                    'name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
}
