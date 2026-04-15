<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspiciousActivityLog extends Model
{
    protected $fillable = ['user_id', 'exam_attempt_id', 'activity_type', 'severity', 'note', 'event_count', 'last_detected_at', 'context_json'];

    protected function casts(): array
    {
        return [
            'last_detected_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }
}
