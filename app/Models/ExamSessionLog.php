<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionLog extends Model
{
    protected $fillable = ['exam_attempt_id', 'user_id', 'event_type', 'event_time', 'metadata_json'];

    protected function casts(): array
    {
        return ['event_time' => 'datetime', 'metadata_json' => 'array'];
    }
}
