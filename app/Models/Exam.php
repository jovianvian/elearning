<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_OBJECTIVE_SINGLE_CHOICE = 'objective_single_choice';
    public const TYPE_OBJECTIVE_MULTI_RESPONSE = 'objective_multi_response';
    public const TYPE_OBJECTIVE_SHORT_ANSWER = 'objective_short_answer';
    public const TYPE_ESSAY = 'essay';
    public const TYPE_MIXED = 'mixed';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_GRADED = 'graded';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'course_id', 'title', 'description', 'created_by', 'exam_type', 'start_at', 'end_at', 'duration_minutes',
        'shuffle_questions', 'shuffle_options', 'auto_submit', 'show_result_after_submit', 'show_answer_key',
        'show_explanation', 'max_attempts', 'required_paid_month', 'target_score', 'objective_weight_percent', 'essay_weight_percent',
        'status', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'auto_submit' => 'boolean',
            'show_result_after_submit' => 'boolean',
            'show_answer_key' => 'boolean',
            'show_explanation' => 'boolean',
            'required_paid_month' => 'integer',
            'is_published' => 'boolean',
            'target_score' => 'decimal:2',
            'objective_weight_percent' => 'decimal:2',
            'essay_weight_percent' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
    public function attempts(): HasMany { return $this->hasMany(ExamAttempt::class); }

    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === self::STATUS_ARCHIVED) {
            return self::STATUS_ARCHIVED;
        }

        $now = now();

        if ($this->end_at && $now->greaterThan($this->end_at)) {
            return self::STATUS_CLOSED;
        }

        if ($this->start_at && $this->end_at && $now->between($this->start_at, $this->end_at)) {
            return self::STATUS_ACTIVE;
        }

        if ($this->start_at && $now->lessThan($this->start_at)) {
            return self::STATUS_SCHEDULED;
        }

        return $this->status ?: self::STATUS_DRAFT;
    }
}
