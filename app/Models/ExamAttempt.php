<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_AUTO_SUBMITTED = 'auto_submitted';
    public const STATUS_GRADED = 'graded';

    protected $fillable = [
        'exam_id', 'student_id', 'started_at', 'submitted_at', 'auto_submitted_at', 'status',
        'score_objective', 'score_essay', 'final_score', 'is_published', 'tab_switch_count',
        'focus_loss_count', 'refresh_count', 'suspicious_flag',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'auto_submitted_at' => 'datetime',
            'is_published' => 'boolean',
            'suspicious_flag' => 'boolean',
        ];
    }

    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function answers(): HasMany { return $this->hasMany(ExamAttemptAnswer::class); }
}
