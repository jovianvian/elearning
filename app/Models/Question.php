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
    public const TYPE_MULTIPLE_RESPONSE = 'multiple_response';
    public const TYPE_SHORT_ANSWER = 'short_answer';
    public const TYPE_ESSAY = 'essay';

    protected $fillable = [
        'question_bank_id', 'subject_id', 'created_by', 'type', 'question_text', 'question_text_en',
        'question_image_path', 'explanation', 'explanation_en', 'points', 'difficulty', 'import_source', 'short_answer_key', 'is_active',
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

    public function isMultipleResponse(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_RESPONSE;
    }

    public function isObjectiveType(): bool
    {
        return in_array($this->type, [self::TYPE_MULTIPLE_CHOICE, self::TYPE_MULTIPLE_RESPONSE, self::TYPE_SHORT_ANSWER], true);
    }

    public function getImageUrlAttribute(): ?string
    {
        $value = trim((string) ($this->question_image_path ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(https?:)?\/\//i', $value) || str_starts_with($value, 'data:')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return asset($value);
        }

        if (str_starts_with($value, 'public/')) {
            return asset(str_replace('public/', 'storage/', $value));
        }

        if (file_exists(public_path($value))) {
            return asset($value);
        }

        if (file_exists(public_path('storage/'.$value))) {
            return asset('storage/'.$value);
        }

        return asset($value);
    }
}
