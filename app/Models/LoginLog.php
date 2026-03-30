<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    protected $fillable = ['user_id', 'login_at', 'logout_at', 'ip_address', 'user_agent', 'device_type', 'browser', 'platform', 'is_success', 'failure_reason', 'session_id'];

    protected function casts(): array
    {
        return ['login_at' => 'datetime', 'logout_at' => 'datetime', 'is_success' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
