<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamPublicationLog;
use App\Models\CourseStudent;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SystemNotification;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamEngineService
{
    public function __construct(private readonly ExamMonitoringService $monitoringService)
    {
    }

    public function startOrResumeAttempt(Exam $exam, User $student): ExamAttempt
    {
        if (! $exam->is_published) {
            throw new RuntimeException('Exam is not published yet.');
        }

        $now = Carbon::now();
        if ($exam->start_at && $now->lessThan($exam->start_at)) {
            throw new RuntimeException('Exam is not open yet.');
        }

        if ($exam->end_at && $now->greaterThan($exam->end_at)) {
            throw new RuntimeException('Exam has closed.');
        }

        $active = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttempt::STATUS_IN_PROGRESS)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();

        if ($active) {
            $this->autoSubmitIfExpired($active);
            if ($active->status === ExamAttempt::STATUS_IN_PROGRESS) {
                return $active;
            }
        }

        $completedCount = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED, ExamAttempt::STATUS_GRADED])
            ->count();

        $maxAttempts = max(1, (int) $exam->max_attempts);
        if ($completedCount >= $maxAttempts) {
            throw new RuntimeException('Maximum attempts reached.');
        }

        return DB::transaction(function () use ($exam, $student, $now): ExamAttempt {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => $now,
                'status' => ExamAttempt::STATUS_IN_PROGRESS,
                'score_objective' => 0,
                'score_essay' => 0,
                'final_score' => 0,
                'is_published' => false,
            ]);

            $questions = $exam->examQuestions()->get();
            foreach ($questions as $examQuestion) {
                ExamAttemptAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $examQuestion->question_id,
                    'score' => 0,
                ]);
            }

            $this->monitoringService->logEvent($attempt, 'exam_start');

            return $attempt;
        });
    }

    public function saveAnswers(ExamAttempt $attempt, array $payload): void
    {
        $this->autoSubmitIfExpired($attempt);
        $attempt->refresh();

        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        DB::transaction(function () use ($attempt, $payload): void {
            foreach ($payload as $questionId => $answerData) {
                $answer = ExamAttemptAnswer::query()
                    ->where('exam_attempt_id', $attempt->id)
                    ->where('question_id', (int) $questionId)
                    ->first();

                if (! $answer) {
                    continue;
                }

                $question = Question::query()->find((int) $questionId);
                if (! $question) {
                    continue;
                }

                if ($question->type === Question::TYPE_MULTIPLE_CHOICE) {
                    $selectedOptionId = (int) ($answerData['selected_option_id'] ?? 0);
                    $option = QuestionOption::query()
                        ->where('id', $selectedOptionId)
                        ->where('question_id', $question->id)
                        ->first();

                    $isCorrect = $option?->is_correct ?? false;
                    $answer->update([
                        'selected_option_id' => $option?->id,
                        'answer_text' => null,
                        'is_correct' => $isCorrect,
                        'score' => $isCorrect ? (float) $question->points : 0,
                    ]);
                } elseif ($question->type === Question::TYPE_SHORT_ANSWER) {
                    $text = trim((string) ($answerData['answer_text'] ?? ''));
                    $normalized = $this->normalizeText($text);
                    $isCorrect = $normalized !== null && $normalized === $question->short_answer_key;
                    $answer->update([
                        'selected_option_id' => null,
                        'answer_text' => $text === '' ? null : $text,
                        'is_correct' => $isCorrect,
                        'score' => $isCorrect ? (float) $question->points : 0,
                    ]);
                } else {
                    $text = trim((string) ($answerData['answer_text'] ?? ''));
                    $answer->update([
                        'selected_option_id' => null,
                        'answer_text' => $text === '' ? null : $text,
                        'is_correct' => null,
                    ]);
                }
            }
        });
    }

    public function submitAttempt(ExamAttempt $attempt, bool $auto = false): ExamAttempt
    {
        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $auto): ExamAttempt {
            $attempt->loadMissing(['exam', 'answers.question']);

            $scoreObjective = 0.0;
            $scoreEssay = 0.0;

            foreach ($attempt->answers as $answer) {
                $questionType = $answer->question?->type;
                if (in_array($questionType, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_SHORT_ANSWER], true)) {
                    $scoreObjective += (float) $answer->score;
                } elseif ($questionType === Question::TYPE_ESSAY) {
                    $scoreEssay += (float) $answer->score;
                }
            }

            $now = Carbon::now();
            $attempt->update([
                'submitted_at' => $auto ? $attempt->submitted_at : $now,
                'auto_submitted_at' => $auto ? $now : null,
                'status' => $auto ? ExamAttempt::STATUS_AUTO_SUBMITTED : ExamAttempt::STATUS_SUBMITTED,
                'score_objective' => $scoreObjective,
                'score_essay' => $scoreEssay,
                'final_score' => $scoreObjective + $scoreEssay,
                'is_published' => (bool) $attempt->exam?->show_result_after_submit,
            ]);

            $this->monitoringService->logEvent($attempt->fresh(), $auto ? 'auto_submit' : 'exam_submit');

            return $attempt->fresh();
        });
    }

    public function autoSubmitIfExpired(ExamAttempt $attempt): void
    {
        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        $deadline = $this->attemptDeadline($attempt);
        if ($deadline && Carbon::now()->greaterThanOrEqualTo($deadline)) {
            $this->submitAttempt($attempt, true);
        }
    }

    public function attemptDeadline(ExamAttempt $attempt): ?Carbon
    {
        $attempt->loadMissing('exam');

        $start = $attempt->started_at;
        if (! $start) {
            return $attempt->exam->end_at ? Carbon::parse($attempt->exam->end_at) : null;
        }

        $durationDeadline = $start->copy()->addMinutes((int) $attempt->exam->duration_minutes);
        if (! $attempt->exam->end_at) {
            return $durationDeadline;
        }

        return Carbon::parse($attempt->exam->end_at)->lessThan($durationDeadline)
            ? Carbon::parse($attempt->exam->end_at)
            : $durationDeadline;
    }

    public function gradeEssayAnswers(ExamAttempt $attempt, User $teacher, array $gradingPayload): ExamAttempt
    {
        return DB::transaction(function () use ($attempt, $teacher, $gradingPayload): ExamAttempt {
            foreach ($gradingPayload as $answerId => $grading) {
                $answer = ExamAttemptAnswer::query()
                    ->where('id', (int) $answerId)
                    ->where('exam_attempt_id', $attempt->id)
                    ->first();

                if (! $answer || $answer->question?->type !== Question::TYPE_ESSAY) {
                    continue;
                }

                $answer->update([
                    'score' => (float) ($grading['score'] ?? 0),
                    'teacher_feedback' => trim((string) ($grading['teacher_feedback'] ?? '')) ?: null,
                    'graded_by' => $teacher->id,
                    'graded_at' => now(),
                ]);
            }

            $attempt->refresh()->loadMissing('answers.question');
            $scoreObjective = 0.0;
            $scoreEssay = 0.0;
            $hasUngradedEssay = false;

            foreach ($attempt->answers as $answer) {
                $type = $answer->question?->type;
                if (in_array($type, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_SHORT_ANSWER], true)) {
                    $scoreObjective += (float) $answer->score;
                } elseif ($type === Question::TYPE_ESSAY) {
                    $scoreEssay += (float) $answer->score;
                    if ($answer->graded_at === null) {
                        $hasUngradedEssay = true;
                    }
                }
            }

            $attempt->update([
                'score_objective' => $scoreObjective,
                'score_essay' => $scoreEssay,
                'final_score' => $scoreObjective + $scoreEssay,
                'status' => $hasUngradedEssay ? $attempt->status : ExamAttempt::STATUS_GRADED,
            ]);

            return $attempt->fresh();
        });
    }

    public function publishResults(Exam $exam, User $publisher, ?string $note = null): void
    {
        DB::transaction(function () use ($exam, $publisher, $note): void {
            $affected = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED, ExamAttempt::STATUS_GRADED])
                ->update(['is_published' => true]);

            ExamPublicationLog::create([
                'exam_id' => $exam->id,
                'published_by' => $publisher->id,
                'published_at' => now(),
                'note' => $note,
            ]);

            if ($affected > 0) {
                $notification = SystemNotification::create([
                    'type' => 'exam_result_published',
                    'title' => 'Hasil ujian tersedia',
                    'body' => "Hasil ujian {$exam->title} telah dipublikasikan.",
                    'title_en' => 'Exam result available',
                    'body_en' => "Results for exam {$exam->title} have been published.",
                    'related_type' => 'exam',
                    'related_id' => $exam->id,
                    'created_by' => $publisher->id,
                ]);

                $studentIds = ExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->pluck('student_id')
                    ->unique()
                    ->all();

                foreach ($studentIds as $studentId) {
                    UserNotification::firstOrCreate([
                        'notification_id' => $notification->id,
                        'user_id' => $studentId,
                    ]);
                }
            }
        });
    }

    public function notifyExamAvailable(Exam $exam, User $actor): void
    {
        $studentIds = CourseStudent::query()
            ->where('course_id', $exam->course_id)
            ->pluck('student_id')
            ->unique()
            ->all();

        if (empty($studentIds)) {
            return;
        }

        $notification = SystemNotification::create([
            'type' => 'exam_available',
            'title' => 'Ujian tersedia',
            'body' => "Ujian {$exam->title} sudah tersedia.",
            'title_en' => 'Exam available',
            'body_en' => "Exam {$exam->title} is now available.",
            'related_type' => 'exam',
            'related_id' => $exam->id,
            'created_by' => $actor->id,
        ]);

        foreach ($studentIds as $studentId) {
            UserNotification::firstOrCreate([
                'notification_id' => $notification->id,
                'user_id' => $studentId,
            ]);
        }
    }

    private function normalizeText(string $text): ?string
    {
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
