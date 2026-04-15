<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id', 'question_id', 'selected_option_id', 'selected_option_ids_json', 'answer_text',
        'is_correct', 'score', 'teacher_feedback', 'graded_by', 'graded_at',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'graded_at' => 'datetime', 'selected_option_ids_json' => 'array'];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
