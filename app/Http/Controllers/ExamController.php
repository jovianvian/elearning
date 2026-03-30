<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublishExamResultRequest;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\Role;
use App\Services\ExamAccessService;
use App\Services\ExamEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamAccessService $accessService,
        private readonly ExamEngineService $engineService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();

        $query = Exam::query()
            ->with(['course.subject', 'course.schoolClass', 'creator'])
            ->latest();

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

        $exams = $query->paginate(10);

        return view('exams.index', compact('exams'));
    }

    public function create(): View
    {
        $this->authorizeManageEntry();

        $courses = $this->manageableCourses();
        $questions = Question::query()
            ->where('is_active', true)
            ->with(['subject', 'bank'])
            ->orderByDesc('id')
            ->get();

        return view('exams.create', compact('courses', 'questions'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->authorizeManageEntry();

        $data = $request->validated();
        $course = Course::query()->with('subject')->findOrFail((int) $data['course_id']);
        abort_unless($this->accessService->canManageCourseExam(auth()->user(), $course), 403);

        $questionIds = array_map('intval', $data['question_ids']);
        $questions = Question::query()->whereIn('id', $questionIds)->get()->keyBy('id');
        $this->validateQuestionSubjectAlignment($course, $questionIds, $questions->all());

        $exam = DB::transaction(function () use ($data, $course, $questionIds, $questions): Exam {
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
                    'points' => $question->points,
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
        $questions = Question::query()
            ->where('is_active', true)
            ->with(['subject', 'bank'])
            ->orderByDesc('id')
            ->get();

        return view('exams.edit', compact('exam', 'courses', 'questions'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->load('course');
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $data = $request->validated();
        $course = Course::query()->with('subject')->findOrFail((int) $data['course_id']);
        abort_unless($this->accessService->canManageCourseExam(auth()->user(), $course), 403);

        $questionIds = array_values(array_unique(array_map('intval', $data['question_ids'])));
        $questions = Question::query()->whereIn('id', $questionIds)->get()->keyBy('id');
        $this->validateQuestionSubjectAlignment($course, $questionIds, $questions->all());

        DB::transaction(function () use ($exam, $data, $course, $questionIds, $questions): void {
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
                    'points' => $question->points,
                ]);
            }
        });

        $exam->refresh();
        if ($exam->is_published) {
            $this->engineService->notifyExamAvailable($exam, auth()->user());
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam moved to trash.');
    }

    public function publishResults(PublishExamResultRequest $request, Exam $exam): RedirectResponse
    {
        abort_unless($this->accessService->canManageExam(auth()->user(), $exam), 403);

        $this->engineService->publishResults($exam, auth()->user(), $request->validated('note'));

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
            ->where('is_published', true)
            ->latest();

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('teachers', fn ($q) => $q->where('users.id', $user->id));
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
}
