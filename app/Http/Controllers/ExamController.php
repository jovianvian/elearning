<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublishExamResultRequest;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ExamAccessService;
use App\Services\ExamEngineService;
use App\Services\QuestionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamAccessService $accessService,
        private readonly ExamEngineService $engineService,
        private readonly QuestionAccessService $questionAccessService
    ) {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Exam::query()
            ->with(['course.subject', 'course.schoolClass', 'creator']);

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('course.teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        if ($user->hasRole(Role::STUDENT)) {
            $query->whereHas('course.students', fn ($q) => $q->where('users.id', $user->id))
                ->where('is_published', true);
        }

        if ($user->hasRole(Role::PRINCIPAL)) {
            // Principal read-only can view all exams.
        }

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$q}%"));
            });
        }

        if ($courseId = $request->integer('course_id')) {
            $query->where('course_id', $courseId);
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->whereHas('course', fn ($cq) => $cq->where('subject_id', $subjectId));
        }

        if ($classId = $request->integer('class_id')) {
            $query->whereHas('course', fn ($cq) => $cq->where('class_id', $classId));
        }

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['draft', 'scheduled', 'active', 'closed', 'graded', 'archived'], true)) {
                $query->where('status', $status);
            }
        }

        $exams = $query->orderBy('title')->paginate(10)->withQueryString();

        $courses = $this->manageableCourses();
        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('exams.index', compact('exams', 'courses', 'subjects', 'classes'));
    }

    public function create(): View
    {
        $this->authorizeManageEntry();

        $courses = $this->manageableCourses();
        $questionBanks = $this->accessibleQuestionBanks();
        $questions = Question::query()
            ->where('is_active', true)
            ->with(['subject', 'bank'])
            ->whereIn('question_bank_id', $questionBanks->pluck('id'))
            ->orderByDesc('id')
            ->get();

        return view('exams.create', compact('courses', 'questions', 'questionBanks'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->authorizeManageEntry();

        $data = $request->validated();
        $course = Course::query()->with('subject')->findOrFail((int) $data['course_id']);
        abort_unless($this->accessService->canManageCourseExam(auth()->user(), $course), 403);
        $selectedBankId = isset($data['question_bank_id']) && $data['question_bank_id'] !== null
            ? (int) $data['question_bank_id']
            : null;
        if ($selectedBankId !== null) {
            $bank = QuestionBank::query()->findOrFail($selectedBankId);
            abort_unless($this->questionAccessService->canViewBank(auth()->user(), $bank), 403);
        }

        $questionIds = array_map('intval', $data['question_ids']);
        $questions = Question::query()->whereIn('id', $questionIds)->get()->keyBy('id');
        $this->validateQuestionSubjectAlignment($course, $questionIds, $questions->all());
        $this->validateSelectedBankAlignment($selectedBankId, $questions->all());
        $pointMap = $this->buildQuestionPointMap($data, $questionIds, $questions);

        $exam = DB::transaction(function () use ($data, $course, $questionIds, $questions, $pointMap): Exam {
            $exam = Exam::create([
                'course_id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
                'exam_type' => $data['exam_type'],
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
                'duration_minutes' => $data['duration_minutes'],
                'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
                'shuffle_options' => (bool) ($data['shuffle_options'] ?? false),
                'auto_submit' => (bool) ($data['auto_submit'] ?? true),
                'show_result_after_submit' => (bool) ($data['show_result_after_submit'] ?? false),
                'show_answer_key' => (bool) ($data['show_answer_key'] ?? false),
                'show_explanation' => (bool) ($data['show_explanation'] ?? false),
                'max_attempts' => (int) ($data['max_attempts'] ?? 1),
                'required_paid_month' => isset($data['required_paid_month']) && $data['required_paid_month'] !== '' ? (int) $data['required_paid_month'] : null,
                'target_score' => (float) ($data['target_score'] ?? 100),
                'objective_weight_percent' => (float) ($data['objective_weight_percent'] ?? 60),
                'essay_weight_percent' => (float) ($data['essay_weight_percent'] ?? 40),
                'status' => $data['status'],
                'is_published' => (bool) ($data['is_published'] ?? false),
            ]);

            $order = 1;
            foreach ($questionIds as $questionId) {
                $question = $questions->get($questionId);
                if (! $question) {
                    continue;
                }

                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'question_order' => $order++,
                    'points' => $pointMap[$questionId] ?? (float) $question->points,
                ]);
            }
            return $exam;
        });

        if ($exam->is_published) {
            $this->engineService->notifyExamAvailable($exam, auth()->user());
        }

        return redirect()->route('exams.index')->with('success', 'Exam created.');
    }

    public function show(Exam $exam): View
    {
        $exam->load(['course.subject', 'course.schoolClass', 'examQuestions.question', 'attempts.student']);
        abort_unless($this->accessService->canViewExam(auth()->user(), $exam), 403);

        $attempts = $exam->attempts()->with('student')->latest()->paginate(10);

        return view('exams.show', compact('exam', 'attempts'));
    }

    public function edit(Exam $exam): View
    {
        $exam->load('course', 'examQuestions');
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $courses = $this->manageableCourses();
        $questionBanks = $this->accessibleQuestionBanks();
        $questions = Question::query()
            ->where('is_active', true)
            ->with(['subject', 'bank'])
            ->whereIn('question_bank_id', $questionBanks->pluck('id'))
            ->orderByDesc('id')
            ->get();

        return view('exams.edit', compact('exam', 'courses', 'questions', 'questionBanks'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->load('course');
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $data = $request->validated();
        $course = Course::query()->with('subject')->findOrFail((int) $data['course_id']);
        abort_unless($this->accessService->canManageCourseExam(auth()->user(), $course), 403);
        $selectedBankId = isset($data['question_bank_id']) && $data['question_bank_id'] !== null
            ? (int) $data['question_bank_id']
            : null;
        if ($selectedBankId !== null) {
            $bank = QuestionBank::query()->findOrFail($selectedBankId);
            abort_unless($this->questionAccessService->canViewBank(auth()->user(), $bank), 403);
        }

        $questionIds = array_values(array_unique(array_map('intval', $data['question_ids'])));
        $questions = Question::query()->whereIn('id', $questionIds)->get()->keyBy('id');
        $this->validateQuestionSubjectAlignment($course, $questionIds, $questions->all());
        $this->validateSelectedBankAlignment($selectedBankId, $questions->all());
        $pointMap = $this->buildQuestionPointMap($data, $questionIds, $questions);

        DB::transaction(function () use ($exam, $data, $course, $questionIds, $questions, $pointMap): void {
            $exam->update([
                'course_id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'exam_type' => $data['exam_type'],
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
                'duration_minutes' => $data['duration_minutes'],
                'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
                'shuffle_options' => (bool) ($data['shuffle_options'] ?? false),
                'auto_submit' => (bool) ($data['auto_submit'] ?? true),
                'show_result_after_submit' => (bool) ($data['show_result_after_submit'] ?? false),
                'show_answer_key' => (bool) ($data['show_answer_key'] ?? false),
                'show_explanation' => (bool) ($data['show_explanation'] ?? false),
                'max_attempts' => (int) ($data['max_attempts'] ?? 1),
                'required_paid_month' => isset($data['required_paid_month']) && $data['required_paid_month'] !== '' ? (int) $data['required_paid_month'] : null,
                'target_score' => (float) ($data['target_score'] ?? 100),
                'objective_weight_percent' => (float) ($data['objective_weight_percent'] ?? 60),
                'essay_weight_percent' => (float) ($data['essay_weight_percent'] ?? 40),
                'status' => $data['status'],
                'is_published' => (bool) ($data['is_published'] ?? false),
            ]);

            $exam->examQuestions()->delete();
            $order = 1;
            foreach ($questionIds as $questionId) {
                $question = $questions->get($questionId);
                if (! $question) {
                    continue;
                }

                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'question_order' => $order++,
                    'points' => $pointMap[$questionId] ?? (float) $question->points,
                ]);
            }
        });

        $exam->refresh();
        if ($exam->is_published) {
            $this->engineService->notifyExamAvailable($exam, auth()->user());
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse|JsonResponse
    {
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);
        $exam->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Exam moved to trash.',
            ]);
        }

        return redirect()->route('exams.index')->with('success', 'Exam moved to trash.');
    }

    public function publishResults(PublishExamResultRequest $request, Exam $exam): RedirectResponse|JsonResponse
    {
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $this->engineService->publishResults($exam, auth()->user(), $request->validated('note'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Exam results published to students.',
            ]);
        }

        return back()->with('success', 'Exam results published to students.');
    }

    private function authorizeManageEntry(): void
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
    }

    private function manageableCourses()
    {
        $user = auth()->user();
        $query = Course::query()
            ->with(['subject', 'schoolClass', 'academicYear', 'semester'])
            ->latest();

        if ($user->hasRole(Role::TEACHER)) {
            $query->where(function ($outer) use ($user): void {
                $outer->whereHas('teachers', fn ($q) => $q->where('users.id', $user->id))
                    ->orWhereExists(function ($sub) use ($user): void {
                        $sub->select(DB::raw(1))
                            ->from('subject_teachers')
                            ->whereColumn('subject_teachers.subject_id', 'courses.subject_id')
                            ->where('subject_teachers.teacher_id', $user->id)
                            ->where('subject_teachers.is_active', 1);
                    });
            });
        }

        return $query->get();
    }

    private function validateQuestionSubjectAlignment(Course $course, array $questionIds, array $questions): void
    {
        if (count($questionIds) !== count($questions)) {
            throw ValidationException::withMessages([
                'question_ids' => 'Some selected questions are invalid.',
            ]);
        }

        foreach ($questions as $question) {
            if ((int) $question->subject_id !== (int) $course->subject_id) {
                throw ValidationException::withMessages([
                    'question_ids' => 'All questions must belong to the same subject as selected course.',
                ]);
            }
        }
    }

    private function validateSelectedBankAlignment(?int $selectedBankId, array $questions): void
    {
        if ($selectedBankId === null) {
            return;
        }

        foreach ($questions as $question) {
            if ((int) $question->question_bank_id !== $selectedBankId) {
                throw ValidationException::withMessages([
                    'question_ids' => 'Selected questions must come from the selected question bank.',
                ]);
            }
        }
    }

    private function accessibleQuestionBanks()
    {
        $query = QuestionBank::query()
            ->with('subject')
            ->withCount(['questions' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('id');

        return $this->questionAccessService
            ->scopeAccessibleBanks($query, auth()->user())
            ->get();
    }

    /**
     * @param  array<int, int>  $questionIds
     * @param  \Illuminate\Support\Collection<int, \App\Models\Question>  $questions
     * @return array<int, float>
     */
    private function buildQuestionPointMap(array $data, array $questionIds, $questions): array
    {
        $scoringMode = (string) ($data['scoring_mode'] ?? 'auto');
        if ($scoringMode === 'manual') {
            $manualPoints = $data['question_points'] ?? [];
            $map = [];
            foreach ($questionIds as $questionId) {
                $point = $manualPoints[$questionId] ?? $manualPoints[(string) $questionId] ?? null;
                if ($point !== null && $point !== '' && is_numeric($point)) {
                    $map[$questionId] = max(0.1, round((float) $point, 2));
                }
            }
            if ($map !== []) {
                return $map;
            }
        }

        $examType = (string) ($data['exam_type'] ?? 'mixed');
        $targetScore = max(1.0, (float) ($data['target_score'] ?? 100));
        $objectiveWeight = max(0.0, min(100.0, (float) ($data['objective_weight_percent'] ?? 60)));
        $essayWeight = max(0.0, min(100.0, (float) ($data['essay_weight_percent'] ?? 40)));

        $objectiveIds = [];
        $essayIds = [];
        foreach ($questionIds as $questionId) {
            $question = $questions->get($questionId);
            if (! $question) {
                continue;
            }

            if ($question->type === Question::TYPE_ESSAY) {
                $essayIds[] = $questionId;
            } else {
                $objectiveIds[] = $questionId;
            }
        }

        if (in_array($examType, [Exam::TYPE_OBJECTIVE, Exam::TYPE_OBJECTIVE_SINGLE_CHOICE, Exam::TYPE_OBJECTIVE_MULTI_RESPONSE, Exam::TYPE_OBJECTIVE_SHORT_ANSWER], true)) {
            $objectiveWeight = 100.0;
            $essayWeight = 0.0;
        } elseif ($examType === Exam::TYPE_ESSAY) {
            $objectiveWeight = 0.0;
            $essayWeight = 100.0;
        } else {
            if (count($objectiveIds) === 0) {
                $objectiveWeight = 0.0;
                $essayWeight = 100.0;
            } elseif (count($essayIds) === 0) {
                $objectiveWeight = 100.0;
                $essayWeight = 0.0;
            }
        }

        $objectiveTotal = round($targetScore * ($objectiveWeight / 100), 2);
        $essayTotal = round($targetScore * ($essayWeight / 100), 2);
        $totalAllocated = round($objectiveTotal + $essayTotal, 2);
        if ($totalAllocated !== round($targetScore, 2)) {
            $objectiveTotal = round($objectiveTotal + ($targetScore - $totalAllocated), 2);
        }

        $pointMap = [];
        $objectivePoints = $this->distributeEqualPoints($objectiveIds, $objectiveTotal);
        foreach ($objectivePoints as $questionId => $points) {
            $pointMap[$questionId] = $points;
        }
        $essayPoints = $this->distributeEqualPoints($essayIds, $essayTotal);
        foreach ($essayPoints as $questionId => $points) {
            $pointMap[$questionId] = $points;
        }

        return $pointMap;
    }

    /**
     * @param  array<int, int>  $questionIds
     * @return array<int, float>
     */
    private function distributeEqualPoints(array $questionIds, float $total): array
    {
        $count = count($questionIds);
        if ($count === 0) {
            return [];
        }

        $base = floor(($total / $count) * 100) / 100;
        $points = array_fill(0, $count, $base);
        $current = round(array_sum($points), 2);
        $delta = round($total - $current, 2);
        if ($delta !== 0.0) {
            $points[$count - 1] = round($points[$count - 1] + $delta, 2);
        }

        $map = [];
        foreach ($questionIds as $idx => $questionId) {
            $map[$questionId] = max(0.0, round((float) $points[$idx], 2));
        }

        return $map;
    }
}
