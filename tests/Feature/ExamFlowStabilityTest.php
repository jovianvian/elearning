<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamSessionLog;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExamFlowStabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBaseRoles();
    }

    public function test_student_start_is_idempotent_and_creates_single_in_progress_attempt(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870011']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2501001']);
        $exam = $this->createPublishedExam($teacher, $student);

        $first = $this->actingAs($student)->post(route('student-exams.start', $exam));
        $first->assertRedirect();

        $second = $this->actingAs($student)->post(route('student-exams.start', $exam));
        $second->assertRedirect();

        $attempts = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->get();

        $this->assertCount(1, $attempts);
        $this->assertSame(ExamAttempt::STATUS_IN_PROGRESS, $attempts->first()->status);
    }

    public function test_student_cannot_open_other_students_attempt(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870022']);
        $studentA = $this->makeUser(Role::STUDENT, ['nis' => '2502001']);
        $studentB = $this->makeUser(Role::STUDENT, ['nis' => '2502002']);
        $exam = $this->createPublishedExam($teacher, $studentA);

        $this->createExamMembership($exam->course, $studentB);

        $attemptA = app(ExamEngineService::class)->startOrResumeAttempt($exam, $studentA);

        $this->actingAs($studentB)
            ->get(route('student-exams.attempt.show', $attemptA))
            ->assertForbidden();
    }

    public function test_expired_attempt_auto_submits_on_show(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870033']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2503001']);
        $exam = $this->createPublishedExam($teacher, $student, [
            'duration_minutes' => 1,
            'end_at' => now()->addHour(),
        ]);

        $attempt = app(ExamEngineService::class)->startOrResumeAttempt($exam, $student);
        $attempt->update(['started_at' => now()->subMinutes(5)]);

        $this->actingAs($student)
            ->get(route('student-exams.attempt.show', $attempt))
            ->assertRedirect(route('student-exams.attempt.result', $attempt, false));

        $attempt->refresh();
        $this->assertSame(ExamAttempt::STATUS_AUTO_SUBMITTED, $attempt->status);
        $this->assertNotNull($attempt->auto_submitted_at);
    }

    public function test_teacher_reports_are_scoped_to_their_owned_exams(): void
    {
        $teacherA = $this->makeUser(Role::TEACHER, ['nip' => '19870044', 'username' => 'teacher.a']);
        $teacherB = $this->makeUser(Role::TEACHER, ['nip' => '19870055', 'username' => 'teacher.b']);
        $studentA = $this->makeUser(Role::STUDENT, ['nis' => '2504001']);
        $studentB = $this->makeUser(Role::STUDENT, ['nis' => '2504002']);

        $examA = $this->createPublishedExam($teacherA, $studentA, ['title' => 'Exam A']);
        $examB = $this->createPublishedExam($teacherB, $studentB, ['title' => 'Exam B']);

        $this->createSubmittedAttempt($examA, $studentA, 82);
        $this->createSubmittedAttempt($examB, $studentB, 66);

        $this->actingAs($teacherA)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('examList', function ($examList) use ($examA, $examB): bool {
                $ids = $examList->pluck('id')->all();

                return in_array($examA->id, $ids, true) && ! in_array($examB->id, $ids, true);
            })
            ->assertViewHas('totalAttempts', 1);
    }

    public function test_grading_marks_attempt_as_graded_when_only_essay_is_pending(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870066']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2505001']);
        $exam = $this->createPublishedExam($teacher, $student, ['title' => 'Mixed Exam']);
        $attempt = app(ExamEngineService::class)->startOrResumeAttempt($exam, $student);

        $shortAnswerQuestion = Question::query()->create([
            'question_bank_id' => $this->createQuestionBank($exam, $teacher)->id,
            'subject_id' => $exam->course->subject_id,
            'created_by' => $teacher->id,
            'type' => Question::TYPE_SHORT_ANSWER,
            'question_text' => 'Ibukota Indonesia?',
            'points' => 10,
            'short_answer_key' => 'jakarta',
            'is_active' => true,
        ]);
        $essayQuestion = Question::query()->create([
            'question_bank_id' => $this->createQuestionBank($exam, $teacher)->id,
            'subject_id' => $exam->course->subject_id,
            'created_by' => $teacher->id,
            'type' => Question::TYPE_ESSAY,
            'question_text' => 'Jelaskan Pancasila.',
            'points' => 20,
            'is_active' => true,
        ]);

        ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_id' => $shortAnswerQuestion->id,
            'question_order' => 2,
            'points' => 10,
        ]);
        ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_id' => $essayQuestion->id,
            'question_order' => 3,
            'points' => 20,
        ]);

        $attempt->answers()->create([
            'question_id' => $shortAnswerQuestion->id,
            'answer_text' => 'Jakarta',
            'is_correct' => true,
            'score' => 10,
        ]);
        $essayAnswer = $attempt->answers()->create([
            'question_id' => $essayQuestion->id,
            'answer_text' => 'Isi jawaban essay',
            'score' => 0,
        ]);

        $attempt->update([
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'score_objective' => 10,
            'final_score' => 10,
        ]);

        app(ExamEngineService::class)->gradeSubjectiveAnswers($attempt->fresh(), $teacher, [
            $essayAnswer->id => [
                'score' => 18,
                'teacher_feedback' => 'Baik.',
            ],
        ]);

        $attempt->refresh();
        $this->assertSame(ExamAttempt::STATUS_GRADED, $attempt->status);
        $this->assertSame('28.00', number_format((float) $attempt->final_score, 2, '.', ''));
    }

    public function test_duplicate_submit_is_backend_idempotent_and_does_not_duplicate_submit_logs(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870077']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2506001']);
        $exam = $this->createPublishedExam($teacher, $student);
        $attempt = app(ExamEngineService::class)->startOrResumeAttempt($exam, $student);

        $answer = $attempt->answers()->with('question.options')->firstOrFail();
        $correctOption = $answer->question->options()->where('is_correct', true)->firstOrFail();
        $payload = [
            'answers' => [
                $answer->question_id => [
                    'selected_option_id' => $correctOption->id,
                ],
            ],
        ];

        $this->actingAs($student)
            ->post(route('student-exams.attempt.submit', $attempt), $payload)
            ->assertRedirect(route('student-exams.attempt.result', $attempt, false));

        $this->actingAs($student)
            ->post(route('student-exams.attempt.submit', $attempt), $payload)
            ->assertRedirect(route('student-exams.attempt.result', $attempt, false));

        $attempt->refresh();
        $this->assertSame(ExamAttempt::STATUS_SUBMITTED, $attempt->status);

        $submitLogs = ExamSessionLog::query()
            ->where('exam_attempt_id', $attempt->id)
            ->whereIn('event_type', ['exam_submit', 'auto_submit'])
            ->count();

        $this->assertSame(1, $submitLogs);
    }

    public function test_save_after_submit_does_not_change_answer_payload(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870088']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2507001']);
        $exam = $this->createPublishedExam($teacher, $student);
        $attempt = app(ExamEngineService::class)->startOrResumeAttempt($exam, $student);

        $answer = $attempt->answers()->with('question.options')->firstOrFail();
        $correctOption = $answer->question->options()->where('is_correct', true)->firstOrFail();
        $wrongOption = $answer->question->options()->where('is_correct', false)->firstOrFail();

        $this->actingAs($student)
            ->post(route('student-exams.attempt.submit', $attempt), [
                'answers' => [
                    $answer->question_id => ['selected_option_id' => $correctOption->id],
                ],
            ])
            ->assertRedirect(route('student-exams.attempt.result', $attempt, false));

        $before = $answer->fresh();
        $this->actingAs($student)
            ->post(route('student-exams.attempt.save', $attempt), [
                'answers' => [
                    $answer->question_id => ['selected_option_id' => $wrongOption->id],
                ],
            ])
            ->assertRedirect();

        $after = $answer->fresh();
        $this->assertSame((int) $before->selected_option_id, (int) $after->selected_option_id);
        $this->assertSame((float) $before->score, (float) $after->score);
    }

    public function test_submit_when_deadline_passed_keeps_single_auto_submitted_state(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870099']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2508001']);
        $exam = $this->createPublishedExam($teacher, $student, [
            'duration_minutes' => 1,
            'end_at' => now()->addHour(),
        ]);
        $attempt = app(ExamEngineService::class)->startOrResumeAttempt($exam, $student);
        $attempt->update(['started_at' => now()->subMinutes(3)]);

        $answer = $attempt->answers()->with('question.options')->firstOrFail();
        $correctOption = $answer->question->options()->where('is_correct', true)->firstOrFail();

        $this->actingAs($student)
            ->post(route('student-exams.attempt.submit', $attempt), [
                'answers' => [
                    $answer->question_id => ['selected_option_id' => $correctOption->id],
                ],
            ])
            ->assertRedirect(route('student-exams.attempt.result', $attempt, false));

        $attempt->refresh();
        $this->assertSame(ExamAttempt::STATUS_AUTO_SUBMITTED, $attempt->status);

        $submitLogs = ExamSessionLog::query()
            ->where('exam_attempt_id', $attempt->id)
            ->whereIn('event_type', ['exam_submit', 'auto_submit'])
            ->pluck('event_type')
            ->all();

        $this->assertSame(['auto_submit'], $submitLogs);
    }

    public function test_teacher_cannot_access_exam_scores_of_unowned_exam_even_with_query_filters(): void
    {
        $teacherA = $this->makeUser(Role::TEACHER, ['nip' => '19870110']);
        $teacherB = $this->makeUser(Role::TEACHER, ['nip' => '19870111']);
        $studentA = $this->makeUser(Role::STUDENT, ['nis' => '2509001']);
        $studentB = $this->makeUser(Role::STUDENT, ['nis' => '2509002']);

        $examA = $this->createPublishedExam($teacherA, $studentA, ['title' => 'Teacher A Exam']);
        $examB = $this->createPublishedExam($teacherB, $studentB, ['title' => 'Teacher B Exam']);
        $this->createSubmittedAttempt($examA, $studentA, 90);
        $this->createSubmittedAttempt($examB, $studentB, 70);

        $this->actingAs($teacherA)
            ->get(route('reports.exam-scores', $examB).'?status=submitted&q=Teacher')
            ->assertForbidden();
    }

    private function createBaseRoles(): void
    {
        foreach ([
            [Role::SUPER_ADMIN, 'Super Admin'],
            [Role::ADMIN, 'Admin'],
            [Role::PRINCIPAL, 'Principal'],
            [Role::TEACHER, 'Teacher'],
            [Role::STUDENT, 'Student'],
        ] as [$code, $name]) {
            DB::table('roles')->insert([
                'name' => $name,
                'display_name' => $name,
                'code' => $code,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeUser(string $roleCode, array $overrides = []): User
    {
        $role = Role::query()->where('code', $roleCode)->firstOrFail();

        $defaults = [
            'role_id' => $role->id,
            'full_name' => ucfirst(str_replace('_', ' ', $roleCode)).' User '.uniqid(),
            'username' => $roleCode.'_'.uniqid(),
            'email' => $roleCode.'_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'must_change_password' => false,
        ];

        if ($roleCode === Role::STUDENT && ! isset($overrides['nis'])) {
            $defaults['nis'] = (string) random_int(100000, 999999);
        }

        if ($roleCode === Role::TEACHER && ! isset($overrides['nip'])) {
            $defaults['nip'] = (string) random_int(10000000, 99999999);
        }

        return User::query()->create(array_merge($defaults, $overrides));
    }

    /**
     * @param  array<string, mixed>  $examOverrides
     */
    private function createPublishedExam(User $teacher, User $student, array $examOverrides = []): Exam
    {
        $academicYear = AcademicYear::query()->create([
            'name' => '2025/2026 '.uniqid(),
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::query()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'code' => 'GANJIL_'.uniqid(),
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'name' => '7A '.uniqid(),
            'code' => '7A_'.uniqid(),
            'grade_level' => 7,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $subject = Subject::query()->create([
            'name_id' => 'Matematika '.uniqid(),
            'name_en' => 'Mathematics',
            'code' => 'MATH_'.uniqid(),
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'title' => 'Math 7A '.uniqid(),
            'slug' => 'math-'.uniqid(),
            'description' => 'Course',
            'is_published' => true,
            'created_by' => $teacher->id,
        ]);

        $course->teachers()->attach($teacher->id, ['is_main_teacher' => true]);
        $this->createExamMembership($course, $student);

        $exam = Exam::query()->create(array_merge([
            'course_id' => $course->id,
            'title' => 'Unit Test '.uniqid(),
            'description' => 'Exam',
            'created_by' => $teacher->id,
            'exam_type' => Exam::TYPE_OBJECTIVE,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'auto_submit' => true,
            'show_result_after_submit' => false,
            'show_answer_key' => false,
            'show_explanation' => false,
            'max_attempts' => 1,
            'status' => Exam::STATUS_ACTIVE,
            'is_published' => true,
        ], $examOverrides));

        $bank = QuestionBank::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bank '.uniqid(),
            'description' => null,
            'visibility' => QuestionBank::VISIBILITY_SUBJECT_SHARED,
            'created_by' => $teacher->id,
        ]);

        $question = Question::query()->create([
            'question_bank_id' => $bank->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => Question::TYPE_MULTIPLE_CHOICE,
            'question_text' => '2 + 2 = ?',
            'points' => 10,
            'is_active' => true,
        ]);

        $correct = QuestionOption::query()->create([
            'question_id' => $question->id,
            'option_key' => 'A',
            'option_text' => '4',
            'is_correct' => true,
        ]);

        QuestionOption::query()->create([
            'question_id' => $question->id,
            'option_key' => 'B',
            'option_text' => '5',
            'is_correct' => false,
        ]);

        ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_id' => $question->id,
            'question_order' => 1,
            'points' => 10,
        ]);

        return $exam->fresh(['course', 'examQuestions', 'course.subject']);
    }

    private function createExamMembership(Course $course, User $student): void
    {
        $course->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()],
        ]);
    }

    private function createSubmittedAttempt(Exam $exam, User $student, float $score): ExamAttempt
    {
        return ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now()->subMinutes(40),
            'submitted_at' => now()->subMinutes(10),
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'score_objective' => $score,
            'score_essay' => 0,
            'final_score' => $score,
            'is_published' => false,
        ]);
    }

    private function createQuestionBank(Exam $exam, User $teacher): QuestionBank
    {
        return QuestionBank::query()->create([
            'subject_id' => $exam->course->subject_id,
            'title' => 'Bank '.uniqid(),
            'visibility' => QuestionBank::VISIBILITY_SUBJECT_SHARED,
            'created_by' => $teacher->id,
        ]);
    }
}
