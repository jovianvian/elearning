<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestoreLog extends Model
{
    protected $fillable = ['restored_by', 'entity_type', 'entity_id', 'restored_at', 'note'];

    protected function casts(): array
    {
        return ['restored_at' => 'datetime'];
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
