<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogExamEventRequest;
use App\Http\Requests\SaveExamAnswersRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Role;
use App\Services\ExamAccessService;
use App\Services\ExamEngineService;
use App\Services\ExamMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentExamAttemptController extends Controller
{
    public function __construct(
        private readonly ExamAccessService $accessService,
        private readonly ExamEngineService $engineService,
        private readonly ExamMonitoringService $monitoringService
    ) {
    }

    public function myExams(): View
    {
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);

        $studentId = auth()->id();
        $exams = Exam::query()
            ->with(['course.subject', 'course.schoolClass'])
            ->where('is_published', true)
            ->whereHas('course.students', fn ($q) => $q->where('users.id', $studentId))
            ->latest()
            ->paginate(10);

        return view('student-exams.index', compact('exams'));
    }

    public function start(Exam $exam): RedirectResponse
    {
        $student = auth()->user();
        abort_unless($this->accessService->canTakeExam($student, $exam), 403);

        try {
            $attempt = $this->engineService->startOrResumeAttempt($exam, $student);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student-exams.attempt.show', $attempt);
    }

    public function showAttempt(ExamAttempt $attempt): View|RedirectResponse
    {
        $attempt->load(['exam.course.subject', 'answers.question.options', 'answers.selectedOption']);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);

        $this->engineService->autoSubmitIfExpired($attempt);
        $attempt->refresh()->load(['exam.course.subject', 'answers.question.options', 'answers.selectedOption']);

        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('student-exams.attempt.result', $attempt);
        }

        $deadline = $this->engineService->attemptDeadline($attempt);
        $questions = $attempt->answers
            ->sortBy(fn ($answer) => $this->questionOrder($attempt, (int) $answer->question_id))
            ->values();

        return view('student-exams.attempt', compact('attempt', 'questions', 'deadline'));
    }

    public function saveAttempt(SaveExamAnswersRequest $request, ExamAttempt $attempt): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);

        $this->engineService->saveAnswers($attempt, $request->validated('answers'));

        return back()->with('success', 'Answers saved.');
    }

    public function submitAttempt(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);

        $this->engineService->saveAnswers($attempt, $request->input('answers', []));
        $this->engineService->submitAttempt($attempt, false);

        return redirect()->route('student-exams.attempt.result', $attempt)
            ->with('success', 'Exam submitted successfully.');
    }

    public function result(ExamAttempt $attempt): View
    {
        $attempt->load(['exam', 'answers.question', 'answers.selectedOption']);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);

        if (! $attempt->is_published) {
            return view('student-exams.result-hidden', compact('attempt'));
        }

        return view('student-exams.result', compact('attempt'));
    }

    public function logEvent(LogExamEventRequest $request, ExamAttempt $attempt): JsonResponse
    {
        abort_unless(auth()->user()->hasRole(Role::STUDENT), 403);
        abort_unless($this->accessService->canViewAttempt(auth()->user(), $attempt), 403);

        $eventType = $request->validated('event_type');
        $this->monitoringService->logEvent($attempt, $eventType, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['ok' => true]);
    }

    private function questionOrder(ExamAttempt $attempt, int $questionId): int
    {
        $key = "attempt_question_order_{$attempt->id}";
        $orderMap = session($key, []);

        if (! isset($orderMap[$questionId])) {
            $questionIds = $attempt->answers->pluck('question_id')->map(static fn ($id) => (int) $id)->all();

            if ($attempt->exam->shuffle_questions) {
                shuffle($questionIds);
            }

            $generated = [];
            foreach ($questionIds as $idx => $id) {
                $generated[$id] = $idx + 1;
            }

            $orderMap = $generated;
            session([$key => $orderMap]);
        }

        return (int) ($orderMap[$questionId] ?? 9999);
    }
}

