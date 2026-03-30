<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_SHORT_ANSWER = 'short_answer';
    public const TYPE_ESSAY = 'essay';

    protected $fillable = [
        'question_bank_id', 'subject_id', 'created_by', 'type', 'question_text', 'question_text_en',
        'explanation', 'explanation_en', 'points', 'difficulty', 'import_source', 'short_answer_key', 'is_active',
    ];

    protected function casts(): array
    {
        return ['points' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function bank(): BelongsTo { return $this->belongsTo(QuestionBank::class, 'question_bank_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function options(): HasMany { return $this->hasMany(QuestionOption::class); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }

    public function isMultipleChoice(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE;
    }
}
