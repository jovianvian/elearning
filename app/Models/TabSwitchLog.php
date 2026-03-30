<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabSwitchLog extends Model
{
    protected $fillable = ['exam_attempt_id', 'user_id', 'event_type', 'event_time'];

    protected function casts(): array
    {
        return ['event_time' => 'datetime'];
    }
}
