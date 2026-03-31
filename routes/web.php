<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ClassStudentAssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamGradingController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestoreCenterController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentExamAttemptController;
use App\Http\Controllers\SuspiciousActivityController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTeacherAssignmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'ensure.password.changed'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/super-admin/dashboard', [DashboardController::class, 'superAdmin'])
        ->middleware('role:super_admin')
        ->name('dashboard.super-admin');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('/principal/dashboard', [DashboardController::class, 'principal'])
        ->middleware('role:principal')
        ->name('dashboard.principal');

    Route::get('/teacher/dashboard', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher')
        ->name('dashboard.teacher');

    Route::get('/student/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('dashboard.student');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{userNotification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function (): void {
        Route::get('/settings', [AppSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update');

        Route::resource('academic-years', AcademicYearController::class)->except(['show']);
        Route::resource('semesters', SemesterController::class)->except(['show']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');
        Route::get('restore-center', [RestoreCenterController::class, 'index'])->name('restore-center.index');
        Route::post('restore-center/{entity}/{id}', [RestoreCenterController::class, 'restore'])->name('restore-center.restore');
    });

    Route::middleware('role:super_admin,admin')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('classes', SchoolClassController::class)
            ->parameters(['classes' => 'school_class'])
            ->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('courses', CourseController::class);
        Route::post('courses/{course}/sync-students', [CourseController::class, 'syncStudents'])->name('courses.sync-students');

        Route::resource('assignments/class-students', ClassStudentAssignmentController::class)
            ->parameters(['class-students' => 'class_student'])
            ->names('assignments.class-students')
            ->except(['show']);
        Route::resource('assignments/subject-teachers', SubjectTeacherAssignmentController::class)
            ->parameters(['subject-teachers' => 'subject_teacher'])
            ->names('assignments.subject-teachers')
            ->except(['show']);
    });

    Route::middleware('role:super_admin,admin,teacher,principal')->group(function (): void {
        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/exams/{exam}/scores', [ReportController::class, 'examScores'])->name('reports.exam-scores');
    });

    Route::middleware('role:super_admin,admin,teacher')->group(function (): void {
        Route::resource('exams', ExamController::class)->except(['index', 'show']);
        Route::post('exams/{exam}/publish-results', [ExamController::class, 'publishResults'])->name('exams.publish-results');
        Route::get('exam-grading', [ExamGradingController::class, 'index'])->name('exam-grading.index');
        Route::get('exam-grading/{attempt}', [ExamGradingController::class, 'show'])->name('exam-grading.show');
        Route::post('exam-grading/{attempt}', [ExamGradingController::class, 'grade'])->name('exam-grading.grade');
        Route::get('exams/{exam}/results', [ExamGradingController::class, 'examResults'])->name('exams.results');

        Route::resource('question-banks', QuestionBankController::class);
        Route::resource('question-imports', QuestionImportController::class)->only(['index', 'create', 'store']);
        Route::get('question-imports/templates/csv', [QuestionImportController::class, 'downloadCsvTemplate'])->name('question-imports.template.csv');
        Route::get('question-imports/templates/aiken', [QuestionImportController::class, 'downloadAikenTemplate'])->name('question-imports.template.aiken');

        Route::get('question-banks/{questionBank}/questions/create', [QuestionController::class, 'create'])->name('question-banks.questions.create');
        Route::post('question-banks/{questionBank}/questions', [QuestionController::class, 'store'])->name('question-banks.questions.store');
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });

    Route::middleware('role:super_admin,teacher')->group(function (): void {
        Route::get('suspicious-activities', [SuspiciousActivityController::class, 'index'])->name('suspicious-activities.index');
    });

    Route::middleware('role:teacher,student,principal')->group(function (): void {
        Route::get('my-courses', [CourseController::class, 'index'])->name('my-courses.index');
    });

    

    Route::middleware('role:student')->prefix('student-exams')->name('student-exams.')->group(function (): void {
        Route::get('/', [StudentExamAttemptController::class, 'myExams'])->name('index');
        Route::post('/{exam}/start', [StudentExamAttemptController::class, 'start'])->name('start');
        Route::get('/attempt/{attempt}', [StudentExamAttemptController::class, 'showAttempt'])->name('attempt.show');
        Route::post('/attempt/{attempt}/save', [StudentExamAttemptController::class, 'saveAttempt'])->name('attempt.save');
        Route::post('/attempt/{attempt}/submit', [StudentExamAttemptController::class, 'submitAttempt'])->name('attempt.submit');
        Route::get('/attempt/{attempt}/result', [StudentExamAttemptController::class, 'result'])->name('attempt.result');
        Route::post('/attempt/{attempt}/events', [StudentExamAttemptController::class, 'logEvent'])->name('attempt.events');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/force-change-password', [AuthController::class, 'showForceChangePassword'])
        ->name('password.force.form');

    Route::post('/force-change-password', [AuthController::class, 'forceChangePassword'])
        ->name('password.force.update');
});
