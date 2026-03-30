<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use SoftDeletes;

    public const VISIBILITY_SUBJECT_SHARED = 'subject_shared';
    public const VISIBILITY_PRIVATE = 'private';

    protected $fillable = ['subject_id', 'title', 'description', 'visibility', 'created_by'];

    public function isShared(): bool
    {
        return $this->visibility === self::VISIBILITY_SUBJECT_SHARED;
    }

    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
}
