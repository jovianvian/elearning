<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeExamAttemptRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Role;
use App\Services\ExamAccessService;
use App\Services\ExamEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamGradingController extends Controller
{
    public function __construct(
        private readonly ExamAccessService $accessService,
        private readonly ExamEngineService $engineService
    ) {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);

        $query = ExamAttempt::query()
            ->with(['exam.course.subject', 'student'])
            ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED, ExamAttempt::STATUS_GRADED]);

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('exam.course.teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->whereHas('student', fn ($sq) => $sq->where('full_name', 'like', "%{$q}%"))
                    ->orWhereHas('exam', fn ($eq) => $eq->where('title', 'like', "%{$q}%"));
            });
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->whereHas('exam.course', fn ($cq) => $cq->where('subject_id', $subjectId));
        }

        if ($classId = $request->integer('class_id')) {
            $query->whereHas('exam.course', fn ($cq) => $cq->where('class_id', $classId));
        }

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED, ExamAttempt::STATUS_GRADED], true)) {
                $query->where('status', $status);
            }
        }

        $attempts = $query->latest()->paginate(12)->withQueryString();
        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('exams.grading-index', compact('attempts', 'subjects', 'classes'));
    }

    public function show(ExamAttempt $attempt): View
    {
        $attempt->load(['exam.course.subject', 'student', 'answers.question']);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);

        return view('exams.grading-show', compact('attempt'));
    }

    public function grade(GradeExamAttemptRequest $request, ExamAttempt $attempt): RedirectResponse
    {
        $attempt->load('exam.course');
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);

        $this->engineService->gradeEssayAnswers($attempt, auth()->user(), $request->validated('grades'));

        return back()->with('success', 'Essay grading updated.');
    }

    public function examResults(Exam $exam): View
    {
        $exam->load('course');
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $attempts = $exam->attempts()->with('student')->latest()->paginate(15)->withQueryString();

        return view('exams.results', compact('exam', 'attempts'));
    }
}
