<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassStudent;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamPublicationLog;
use App\Models\ExamQuestion;
use App\Models\ExamSessionLog;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\SuspiciousActivityLog;
use App\Models\SystemNotification;
use App\Models\TabSwitchLog;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealisticPopulationSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->firstOrFail();
        $semester = Semester::where('academic_year_id', $year->id)->where('code', 'odd')->firstOrFail();
        $superAdmin = User::whereHas('role', fn ($q) => $q->where('code', Role::SUPER_ADMIN))->firstOrFail();

        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('academic_year_id', $year->id)->where('is_active', true)->orderBy('name')->get();
        $subjectTeacherMap = SubjectTeacher::where('academic_year_id', $year->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('subject_id');
        $classStudentMap = ClassStudent::where('academic_year_id', $year->id)
            ->where('status', 'active')
            ->get()
            ->groupBy('class_id');

        $courses = $this->seedCourses($subjects, $classes, $semester, $year, $superAdmin, $subjectTeacherMap, $classStudentMap);
        $subjectQuestionPool = $this->seedQuestionBanksAndQuestions($subjects, $subjectTeacherMap, $superAdmin);
        $exams = $this->seedExams($courses, $subjectQuestionPool, $superAdmin);
        $this->seedExamAttemptsAndMonitoring($exams);
        $this->seedResultPublicationNotifications($exams, $superAdmin);
    }

    private function seedCourses(
        Collection $subjects,
        Collection $classes,
        Semester $semester,
        AcademicYear $year,
        User $superAdmin,
        Collection $subjectTeacherMap,
        Collection $classStudentMap
    ): Collection {
        $courses = collect();

        foreach ($subjects as $subject) {
            foreach ($classes as $class) {
                $title = "{$subject->name_id} - {$class->name} - {$semester->name} {$year->name}";

                $course = Course::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'class_id' => $class->id,
                        'academic_year_id' => $year->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'title' => $title,
                        'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
                        'description' => "Kelas {$class->name} untuk mata pelajaran {$subject->name_id} semester {$semester->name}.",
                        'is_published' => true,
                        'created_by' => $superAdmin->id,
                    ]
                );

                $teacherIds = $subjectTeacherMap->get($subject->id, collect())->pluck('teacher_id')->values()->all();
                shuffle($teacherIds);
                $selectedTeacherIds = array_slice($teacherIds, 0, min(count($teacherIds), rand(1, 2)));
                if (empty($selectedTeacherIds) && ! empty($teacherIds)) {
                    $selectedTeacherIds = [$teacherIds[0]];
                }

                $teacherPayload = [];
                foreach ($selectedTeacherIds as $idx => $teacherId) {
                    $teacherPayload[$teacherId] = ['is_main_teacher' => $idx === 0];
                }
                $course->teachers()->sync($teacherPayload);

                $studentPayload = [];
                $classStudents = $classStudentMap->get($class->id, collect());
                foreach ($classStudents as $classStudent) {
                    $studentPayload[$classStudent->student_id] = [
                        'enrolled_at' => now()->subDays(rand(20, 80)),
                    ];
                }
                $course->students()->sync($studentPayload);

                $courses->push($course);
            }
        }

        return $courses;
    }

    private function seedQuestionBanksAndQuestions(Collection $subjects, Collection $subjectTeacherMap, User $superAdmin): array
    {
        $subjectQuestionPool = [];

        foreach ($subjects as $subject) {
            $teacherIds = $subjectTeacherMap->get($subject->id, collect())->pluck('teacher_id')->values()->all();
            $creatorId = ! empty($teacherIds) ? $teacherIds[array_rand($teacherIds)] : $superAdmin->id;

            $questionIds = [];
            for ($bankIndex = 1; $bankIndex <= 2; $bankIndex++) {
                $bank = QuestionBank::create([
                    'subject_id' => $subject->id,
                    'title' => "Bank Soal {$subject->name_id} Paket {$bankIndex}",
                    'description' => "Kumpulan soal {$subject->name_id} untuk evaluasi SMP Teramia (paket {$bankIndex}).",
                    'visibility' => QuestionBank::VISIBILITY_SUBJECT_SHARED,
                    'created_by' => $creatorId,
                ]);

                $questionSet = $this->buildSubjectQuestionSet($subject->code, $subject->name_id, $bankIndex);
                foreach ($questionSet as $item) {
                    $question = Question::create([
                        'question_bank_id' => $bank->id,
                        'subject_id' => $subject->id,
                        'created_by' => $creatorId,
                        'type' => $item['type'],
                        'question_text' => $item['question_text'],
                        'question_text_en' => $item['question_text_en'] ?? null,
                        'explanation' => $item['explanation'] ?? null,
                        'explanation_en' => $item['explanation_en'] ?? null,
                        'points' => $item['points'],
                        'difficulty' => $item['difficulty'],
                        'import_source' => 'manual',
                        'short_answer_key' => $item['short_answer_key'] ?? null,
                        'is_active' => true,
                    ]);

                    if ($item['type'] === Question::TYPE_MULTIPLE_CHOICE) {
                        foreach ($item['options'] as $key => $text) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => $key,
                                'option_text' => $text,
                                'is_correct' => $key === $item['correct_option'],
                            ]);
                        }
                    }

                    $questionIds[] = $question->id;
                }
            }

            $subjectQuestionPool[$subject->id] = $questionIds;
        }

        return $subjectQuestionPool;
    }

    private function seedExams(Collection $courses, array $subjectQuestionPool, User $superAdmin): Collection
    {
        $exams = collect();

        foreach ($courses as $course) {
            $questionIds = $subjectQuestionPool[$course->subject_id] ?? [];
            shuffle($questionIds);
            $selectedQuestionIds = array_slice($questionIds, 0, min(15, count($questionIds)));

            if (empty($selectedQuestionIds)) {
                continue;
            }

            $startAt = now()->subDays(rand(0, 2))->setHour(rand(7, 10))->setMinute(0);
            $endAt = now()->addDays(rand(3, 10))->setHour(rand(11, 15))->setMinute(0);

            $exam = Exam::create([
                'course_id' => $course->id,
                'title' => 'Ulangan '.$course->subject->name_id.' '.$course->schoolClass->name,
                'description' => 'Simulasi ujian rutin untuk '.$course->subject->name_id.' kelas '.$course->schoolClass->name.'.',
                'created_by' => $course->created_by ?: $superAdmin->id,
                'exam_type' => 'mixed',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration_minutes' => [30, 45, 60][array_rand([30, 45, 60])],
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'auto_submit' => true,
                'show_result_after_submit' => false,
                'show_answer_key' => false,
                'show_explanation' => false,
                'max_attempts' => 1,
                'status' => Exam::STATUS_ACTIVE,
                'is_published' => true,
            ]);

            foreach ($selectedQuestionIds as $idx => $questionId) {
                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'question_order' => $idx + 1,
                    'points' => $question->points,
                ]);
            }

            $notification = SystemNotification::create([
                'type' => 'exam_available',
                'title' => 'Ujian tersedia',
                'body' => "Ujian {$exam->title} sudah tersedia untuk diikuti.",
                'title_en' => 'Exam available',
                'body_en' => "Exam {$exam->title} is now available.",
                'related_type' => 'exam',
                'related_id' => $exam->id,
                'created_by' => $exam->created_by,
            ]);

            $studentIds = CourseStudent::where('course_id', $course->id)->pluck('student_id')->all();
            foreach ($studentIds as $studentId) {
                UserNotification::firstOrCreate([
                    'notification_id' => $notification->id,
                    'user_id' => $studentId,
                ]);
            }

            $exams->push($exam);
        }

        return $exams;
    }

    private function seedExamAttemptsAndMonitoring(Collection $exams): void
    {
        foreach ($exams as $exam) {
            $exam->loadMissing(['examQuestions.question.options', 'course.teachers']);
            $courseStudents = CourseStudent::where('course_id', $exam->course_id)->pluck('student_id')->all();
            if (empty($courseStudents)) {
                continue;
            }

            shuffle($courseStudents);
            $participants = array_slice($courseStudents, 0, rand(5, 10));

            foreach ($participants as $studentId) {
                $statusRoll = rand(1, 100);
                $status = $statusRoll <= 55
                    ? ExamAttempt::STATUS_SUBMITTED
                    : ($statusRoll <= 80 ? ExamAttempt::STATUS_AUTO_SUBMITTED : ExamAttempt::STATUS_IN_PROGRESS);

                $startedAt = now()->subHours(rand(2, 72))->subMinutes(rand(1, 59));
                $submittedAt = null;
                $autoSubmittedAt = null;
                if ($status === ExamAttempt::STATUS_SUBMITTED) {
                    $submittedAt = (clone $startedAt)->addMinutes(rand(20, max(25, $exam->duration_minutes - 3)));
                } elseif ($status === ExamAttempt::STATUS_AUTO_SUBMITTED) {
                    $autoSubmittedAt = (clone $startedAt)->addMinutes($exam->duration_minutes);
                }

                $attempt = ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'started_at' => $startedAt,
                    'submitted_at' => $submittedAt,
                    'auto_submitted_at' => $autoSubmittedAt,
                    'status' => $status,
                    'score_objective' => 0,
                    'score_essay' => 0,
                    'final_score' => 0,
                    'is_published' => false,
                    'tab_switch_count' => 0,
                    'focus_loss_count' => 0,
                    'refresh_count' => 0,
                    'suspicious_flag' => false,
                ]);

                $objectiveScore = 0.0;
                $essayScore = 0.0;
                $hasUngradedEssay = false;
                $graderId = $exam->course->teachers->first()?->id;

                foreach ($exam->examQuestions as $examQuestion) {
                    $question = $examQuestion->question;
                    if (! $question) {
                        continue;
                    }

                    $isAnswered = $status !== ExamAttempt::STATUS_IN_PROGRESS || rand(1, 100) <= 60;

                    $answerPayload = [
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => null,
                        'answer_text' => null,
                        'is_correct' => null,
                        'score' => 0,
                        'teacher_feedback' => null,
                        'graded_by' => null,
                        'graded_at' => null,
                    ];

                    if ($isAnswered) {
                        if ($question->type === Question::TYPE_MULTIPLE_CHOICE) {
                            $options = $question->options;
                            $correctOption = $options->firstWhere('is_correct', true);
                            $selected = rand(1, 100) <= 65
                                ? $correctOption
                                : $options->where('id', '!=', $correctOption?->id)->shuffle()->first();

                            $isCorrect = (bool) ($selected?->is_correct);
                            $score = $isCorrect ? (float) $examQuestion->points : 0;

                            $answerPayload['selected_option_id'] = $selected?->id;
                            $answerPayload['is_correct'] = $isCorrect;
                            $answerPayload['score'] = $score;
                            $objectiveScore += $score;
                        } elseif ($question->type === Question::TYPE_SHORT_ANSWER) {
                            $isCorrect = rand(1, 100) <= 58;
                            $score = $isCorrect ? (float) $examQuestion->points : 0;
                            $answerPayload['answer_text'] = $isCorrect
                                ? (string) $question->short_answer_key
                                : 'jawaban kurang tepat';
                            $answerPayload['is_correct'] = $isCorrect;
                            $answerPayload['score'] = $score;
                            $objectiveScore += $score;
                        } else {
                            $answerPayload['answer_text'] = 'Penjelasan siswa terkait topik '.$question->question_text;

                            if ($status !== ExamAttempt::STATUS_IN_PROGRESS && rand(1, 100) <= 72) {
                                $score = round(((float) $examQuestion->points) * (rand(50, 95) / 100), 2);
                                $answerPayload['score'] = $score;
                                $answerPayload['graded_by'] = $graderId;
                                $answerPayload['graded_at'] = now()->subHours(rand(1, 24));
                                $answerPayload['teacher_feedback'] = 'Jawaban cukup baik, lanjutkan latihan agar lebih terstruktur.';
                                $essayScore += $score;
                            } elseif ($status !== ExamAttempt::STATUS_IN_PROGRESS) {
                                $hasUngradedEssay = true;
                            }
                        }
                    }

                    ExamAttemptAnswer::create($answerPayload);
                }

                if ($status !== ExamAttempt::STATUS_IN_PROGRESS && ! $hasUngradedEssay) {
                    $status = ExamAttempt::STATUS_GRADED;
                }

                $attempt->update([
                    'status' => $status,
                    'score_objective' => round($objectiveScore, 2),
                    'score_essay' => round($essayScore, 2),
                    'final_score' => round($objectiveScore + $essayScore, 2),
                ]);

                $this->seedAttemptMonitoringLogs($attempt, $status);
            }
        }
    }

    private function seedAttemptMonitoringLogs(ExamAttempt $attempt, string $status): void
    {
        $events = [
            ['type' => 'exam_start', 'time' => $attempt->started_at ?: now()],
        ];

        $blurCount = rand(0, 4);
        $refreshCount = rand(0, 2);
        for ($i = 0; $i < $blurCount; $i++) {
            $events[] = ['type' => 'window_blur', 'time' => now()->subMinutes(rand(10, 120))];
            $events[] = ['type' => 'window_focus', 'time' => now()->subMinutes(rand(5, 110))];
        }

        for ($i = 0; $i < $refreshCount; $i++) {
            $events[] = ['type' => 'refresh', 'time' => now()->subMinutes(rand(5, 100))];
        }

        if (rand(1, 100) <= 8) {
            $events[] = ['type' => 'duplicate_session', 'time' => now()->subMinutes(rand(3, 80))];
        }

        if (rand(1, 100) <= 25) {
            $events[] = ['type' => 'reconnect', 'time' => now()->subMinutes(rand(2, 60))];
        }

        if ($status === ExamAttempt::STATUS_AUTO_SUBMITTED) {
            $events[] = ['type' => 'auto_submit', 'time' => $attempt->auto_submitted_at ?: now()];
        } elseif (in_array($status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED], true)) {
            $events[] = ['type' => 'exam_submit', 'time' => $attempt->submitted_at ?: now()];
        }

        foreach ($events as $event) {
            ExamSessionLog::create([
                'exam_attempt_id' => $attempt->id,
                'user_id' => $attempt->student_id,
                'event_type' => $event['type'],
                'event_time' => $event['time'],
                'metadata_json' => ['seeded' => true],
            ]);

            if (in_array($event['type'], ['window_blur', 'window_focus', 'visibility_hidden', 'visibility_visible'], true)) {
                TabSwitchLog::create([
                    'exam_attempt_id' => $attempt->id,
                    'user_id' => $attempt->student_id,
                    'event_type' => $event['type'],
                    'event_time' => $event['time'],
                ]);
            }
        }

        $hasDuplicateSession = collect($events)->contains(fn ($e) => $e['type'] === 'duplicate_session');
        $suspicious = $hasDuplicateSession || rand(1, 100) <= 24;
        if ($suspicious) {
            $focusLossCount = max($blurCount, rand(2, 5));
            $refreshUsed = max($refreshCount, rand(0, 2));
            $attempt->update([
                'suspicious_flag' => true,
                'focus_loss_count' => $focusLossCount,
                'refresh_count' => $refreshUsed,
                'tab_switch_count' => $focusLossCount,
            ]);

            SuspiciousActivityLog::create([
                'user_id' => $attempt->student_id,
                'exam_attempt_id' => $attempt->id,
                'activity_type' => $hasDuplicateSession ? 'duplicate_session' : 'tab_switch_excessive',
                'severity' => $hasDuplicateSession ? 'high' : 'medium',
                'note' => 'Simulasi aktivitas mencurigakan saat pengerjaan ujian.',
            ]);
        }
    }

    private function seedResultPublicationNotifications(Collection $exams, User $superAdmin): void
    {
        $publishedExamIds = $exams->shuffle()->take((int) floor($exams->count() * 0.35))->pluck('id')->all();
        if (empty($publishedExamIds)) {
            return;
        }

        foreach ($publishedExamIds as $examId) {
            $exam = Exam::find($examId);
            if (! $exam) {
                continue;
            }

            $attempts = ExamAttempt::where('exam_id', $exam->id)
                ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_AUTO_SUBMITTED, ExamAttempt::STATUS_GRADED])
                ->get();

            if ($attempts->isEmpty()) {
                continue;
            }

            $attemptIds = $attempts->pluck('id')->all();
            ExamAttempt::whereIn('id', $attemptIds)->update(['is_published' => true]);

            ExamPublicationLog::create([
                'exam_id' => $exam->id,
                'published_by' => $superAdmin->id,
                'published_at' => now()->subDays(rand(0, 2)),
                'note' => 'Publikasi hasil ujian oleh sistem seeding realistis.',
            ]);

            $notification = SystemNotification::create([
                'type' => 'exam_result_published',
                'title' => 'Hasil ujian dipublikasikan',
                'body' => "Hasil {$exam->title} sudah dipublikasikan.",
                'title_en' => 'Exam result published',
                'body_en' => "Result for {$exam->title} has been published.",
                'related_type' => 'exam',
                'related_id' => $exam->id,
                'created_by' => $superAdmin->id,
            ]);

            foreach ($attempts as $attempt) {
                UserNotification::firstOrCreate([
                    'notification_id' => $notification->id,
                    'user_id' => $attempt->student_id,
                ]);
            }
        }
    }

    private function buildSubjectQuestionSet(string $subjectCode, string $subjectName, int $bankIndex): array
    {
        $topicsBySubject = [
            'MTK' => ['persamaan linear', 'teorema pythagoras', 'himpunan', 'perbandingan', 'bangun datar', 'statistika'],
            'PPKN' => ['Pancasila', 'UUD 1945', 'hak dan kewajiban', 'Bhinneka Tunggal Ika', 'gotong royong', 'musyawarah'],
            'BIN' => ['teks narasi', 'teks deskripsi', 'teks eksposisi', 'kalimat efektif', 'ide pokok', 'ejaan'],
            'AGR' => ['kasih', 'iman', 'pengampunan', 'pelayanan', 'doa', 'kejujuran'],
            'BIG' => ['simple present', 'simple past', 'vocabulary', 'greeting', 'descriptive text', 'daily activity'],
            'IPA' => ['sistem pernapasan', 'zat dan perubahan', 'gaya', 'energi', 'ekosistem', 'organ tubuh'],
            'IPS' => ['interaksi sosial', 'kegiatan ekonomi', 'peta', 'letak geografis', 'permintaan dan penawaran', 'sejarah nasional'],
            'PJOK' => ['kebugaran jasmani', 'pemanasan', 'bola besar', 'lari jarak pendek', 'pola hidup sehat', 'koordinasi gerak'],
            'INF' => ['algoritma', 'data digital', 'internet sehat', 'keamanan akun', 'perangkat keras', 'pemrograman dasar'],
            'SBK' => ['unsur seni rupa', 'ritme musik', 'tari tradisional', 'teater', 'warna', 'apresiasi karya'],
            'PRK' => ['kewirausahaan', 'kerajinan bahan keras', 'kerajinan bahan lunak', 'perencanaan produk', 'pengemasan', 'nilai jual'],
        ];

        $topics = $topicsBySubject[$subjectCode] ?? ["konsep dasar {$subjectName}", "materi {$subjectName}"];
        $set = [];

        for ($i = 0; $i < 8; $i++) {
            $topic = $topics[($i + $bankIndex) % count($topics)];
            $set[] = [
                'type' => Question::TYPE_MULTIPLE_CHOICE,
                'question_text' => "Pada materi {$topic}, pernyataan yang paling tepat adalah ...",
                'points' => 1,
                'difficulty' => $i < 3 ? 'easy' : ($i < 6 ? 'medium' : 'hard'),
                'options' => [
                    'A' => "Penjelasan yang tidak sesuai dengan konsep {$topic}.",
                    'B' => "Konsep inti {$topic} yang benar sesuai pembelajaran SMP.",
                    'C' => "Pernyataan yang bertentangan dengan materi {$topic}.",
                    'D' => "Contoh yang tidak berkaitan langsung dengan {$topic}.",
                ],
                'correct_option' => 'B',
                'explanation' => "Jawaban benar menekankan konsep inti dari {$topic}.",
            ];
        }

        $shortTopicA = $topics[0];
        $shortTopicB = $topics[1] ?? $topics[0];
        $set[] = [
            'type' => Question::TYPE_SHORT_ANSWER,
            'question_text' => "Tuliskan istilah kunci yang berkaitan dengan {$shortTopicA}.",
            'points' => 2,
            'difficulty' => 'medium',
            'short_answer_key' => $this->normalizeText($shortTopicA),
            'explanation' => "Gunakan istilah sesuai topik materi {$shortTopicA}.",
        ];
        $set[] = [
            'type' => Question::TYPE_SHORT_ANSWER,
            'question_text' => "Sebutkan satu konsep utama dari {$shortTopicB}.",
            'points' => 2,
            'difficulty' => 'medium',
            'short_answer_key' => $this->normalizeText($shortTopicB),
            'explanation' => "Jawaban harus memuat konsep utama {$shortTopicB}.",
        ];

        $essayTopicA = $topics[2] ?? $topics[0];
        $essayTopicB = $topics[3] ?? $topics[1] ?? $topics[0];
        $set[] = [
            'type' => Question::TYPE_ESSAY,
            'question_text' => "Jelaskan penerapan {$essayTopicA} dalam kehidupan sehari-hari siswa SMP.",
            'points' => 5,
            'difficulty' => 'medium',
            'explanation' => "Uraikan contoh nyata dan alasan yang logis.",
        ];
        $set[] = [
            'type' => Question::TYPE_ESSAY,
            'question_text' => "Berikan analisis singkat mengenai pentingnya {$essayTopicB} pada mata pelajaran {$subjectName}.",
            'points' => 5,
            'difficulty' => 'hard',
            'explanation' => "Jawaban dinilai dari ketepatan konsep, argumentasi, dan contoh.",
        ];

        return $set;
    }

    private function normalizeText(string $text): string
    {
        return strtolower(trim((string) preg_replace('/\s+/u', ' ', $text)));
    }
}
